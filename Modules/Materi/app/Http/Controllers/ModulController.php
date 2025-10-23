<?php

namespace Modules\Materi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Materi\Entities\Modul;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ModulController extends Controller
{
    public function index($kursusId)
    {
        $kursus = Kursus::findOrFail($kursusId);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $modul = Modul::where('kursus_id', $kursusId)
            ->with('materi')
            ->orderBy('urutan')
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'kursus' => [
                    'id' => $kursus->id,
                    'judul' => $kursus->judul
                ],
                'modul' => $modul
            ]
        ]);
    }
    public function store(Request $request, $kursusId)
    {
        $kursus = Kursus::findOrFail($kursusId);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'nama_modul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        // If urutan not provided, get last urutan + 1
        if (!$request->has('urutan')) {
            $lastUrutan = Modul::where('kursus_id', $kursusId)->max('urutan') ?? 0;
            $urutan = $lastUrutan + 1;
        } else {
            $urutan = $request->urutan;
            // Reorder existing modules if necessary
            $existingModul = Modul::where('kursus_id', $kursusId)
                ->where('urutan', '>=', $urutan)
                ->orderBy('urutan')
                ->get();
            foreach ($existingModul as $modul) {
                $modul->urutan += 1;
                $modul->save();
            }
        }
        $modul = new Modul([
            'nama_modul' => $request->nama_modul,
            'deskripsi' => $request->deskripsi,
            'urutan' => $urutan,
            'is_published' => $request->is_published ?? false
        ]);
        $modul->kursus()->associate($kursus);
        $modul->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Modul berhasil dibuat',
            'data' => $modul
        ], 201);
    }
    public function show($id)
    {
        $modul = Modul::with(['kursus:id,judul,admin_instruktur_id', 'materi' => function ($query) {
            $query->orderBy('urutan');
        }])->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'data' => $modul
        ]);
    }
    public function update(Request $request, $id)
    {
        $modul = Modul::with('kursus:id,admin_instruktur_id')->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'nama_modul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        // Handle reordering if urutan changed
        if ($request->has('urutan') && $request->urutan != $modul->urutan) {
            $oldUrutan = $modul->urutan;
            $newUrutan = $request->urutan;
            if ($newUrutan > $oldUrutan) {
                // Moving down, shift up modules in between
                Modul::where('kursus_id', $modul->kursus_id)
                    ->where('urutan', '>', $oldUrutan)
                    ->where('urutan', '<=', $newUrutan)
                    ->decrement('urutan');
            } else {
                // Moving up, shift down modules in between
                Modul::where('kursus_id', $modul->kursus_id)
                    ->where('urutan', '>=', $newUrutan)
                    ->where('urutan', '<', $oldUrutan)
                    ->increment('urutan');
            }
        }
        $modul->fill($request->all());
        $modul->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Modul berhasil diperbarui',
            'data' => $modul
        ]);
    }
    public function destroy($id)
    {
        $modul = Modul::with(['kursus:id,admin_instruktur_id', 'materi'])->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Check if modul has quizzes
        $quizCount = $modul->quiz()->count();
        if ($quizCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Modul tidak dapat dihapus karena memiliki ' . $quizCount . ' quiz'
            ], 400);
        }
        // Check if modul has materi
        $materiCount = $modul->materi()->count();
        if ($materiCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Modul tidak dapat dihapus karena memiliki ' . $materiCount . ' materi'
            ], 400);
        }
        // Reorder remaining modules
        Modul::where('kursus_id', $modul->kursus_id)
            ->where('urutan', '>', $modul->urutan)
            ->decrement('urutan');
        $modul->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Modul berhasil dihapus'
        ]);
    }
}
