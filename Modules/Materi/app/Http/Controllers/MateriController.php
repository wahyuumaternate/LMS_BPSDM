<?php

namespace Modules\Materi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Materi\Entities\Materi;
use Modules\Materi\Entities\Modul;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    public function index($modulId)
    {
        $modul = Modul::with('kursus:id,judul,admin_instruktur_id')->findOrFail($modulId);
        $user = Auth::guard('sanctum')->user();
        // If peserta, check if they're enrolled in the course
        if ($user->tokenCan('peserta')) {
            $isEnrolled = $user->pendaftaranKursus()
                ->where('kursus_id', $modul->kursus_id)
                ->whereIn('status', ['disetujui', 'aktif'])
                ->exists();
            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda belum terdaftar dalam kursus ini'
                ], 403);
            }
            // If module is not published, deny access
            if (!$modul->is_published) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Modul belum dipublikasikan'
                ], 403);
            }
        }
        // If admin/instruktur, check if they have access to the course
        else if (!($user->tokenCan('super_admin') || $modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $materi = Materi::where('modul_id', $modulId)
            ->orderBy('urutan')
            ->get();
        if ($user->tokenCan('peserta')) {
            $materi->each(function ($item) use ($user) {
                $progress = $user->progresMateri()
                    ->where('materi_id', $item->id)
                    ->first();
                $item->progress = $progress ? [
                    'is_selesai' => $progress->is_selesai,
                    'progress_persen' => $progress->progress_persen,
                    'tanggal_mulai' => $progress->tanggal_mulai,
                    'tanggal_selesai' => $progress->tanggal_selesai,
                    'durasi_belajar_menit' => $progress->durasi_belajar_menit
                ] : null;
            });
        }
        return response()->json([
            'status' => 'success',
            'data' => [
                'modul' => [
                    'id' => $modul->id,
                    'nama_modul' => $modul->nama_modul,
                    'deskripsi' => $modul->deskripsi,
                    'is_published' => $modul->is_published,
                    'kursus' => [
                        'id' => $modul->kursus->id,
                        'judul' => $modul->kursus->judul
                    ]
                ],
                'materi' => $materi
            ]
        ]);
    }
    public function store(Request $request, $modulId)
    {
        $modul = Modul::with('kursus:id,admin_instruktur_id')->findOrFail($modulId);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'judul_materi' => 'required|string|max:255',
            'tipe_konten' => 'required|in:pdf,doc,video,audio,gambar,link,scorm',
            'file' => 'required_unless:tipe_konten,link|file|max:102400',
            'link' => 'required_if:tipe_konten,link|url',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'nullable|integer|min:1',
            'is_wajib' => 'nullable|boolean',
            'urutan' => 'nullable|integer|min:1'
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
            $lastUrutan = Materi::where('modul_id', $modulId)->max('urutan') ?? 0;
            $urutan = $lastUrutan + 1;
        } else {
            $urutan = $request->urutan;
            // Reorder existing materi if necessary
            $existingMateri = Materi::where('modul_id', $modulId)
                ->where('urutan', '>=', $urutan)
                ->orderBy('urutan')
                ->get();
            foreach ($existingMateri as $materi) {
                $materi->urutan += 1;
                $materi->save();
            }
        }
        $materi = new Materi([
            'judul_materi' => $request->judul_materi,
            'tipe_konten' => $request->tipe_konten,
            'deskripsi' => $request->deskripsi,
            'durasi_menit' => $request->durasi_menit ?? 0,
            'is_wajib' => $request->is_wajib ?? true,
            'urutan' => $urutan,
            'published_at' => $request->has('published') && $request->published ? now() : null
        ]);
        $materi->modul()->associate($modul);
        // Handle file upload
        if ($request->tipe_konten !== 'link' && $request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store file based on content type
            $path = 'materi/' . $request->tipe_konten . '/' . $filename;
            Storage::disk('public')->put($path, file_get_contents($file));
            $materi->file_path = $path;
            $materi->ukuran_file = $file->getSize();
        } else if ($request->tipe_konten === 'link') {
            $materi->file_path = $request->link;
        }
        $materi->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Materi berhasil ditambahkan',
            'data' => $materi
        ], 201);
    }
    public function show($id)
    {
        $materi = Materi::with('modul.kursus:id,judul,admin_instruktur_id')->findOrFail($id);
        $modul = $materi->modul;
        $user = Auth::guard('sanctum')->user();
        // If peserta, check if they're enrolled in the course
        if ($user->tokenCan('peserta')) {
            $isEnrolled = $user->pendaftaranKursus()
                ->where('kursus_id', $modul->kursus_id)
                ->whereIn('status', ['disetujui', 'aktif'])
                ->exists();
            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda belum terdaftar dalam kursus ini'
                ], 403);
            }
            // If module or materi is not published, deny access
            if (!$modul->is_published || !$materi->published_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Materi belum dipublikasikan'
                ], 403);
            }
            // Record that student has started this materi
            $progresMateri = $user->progresMateri()
                ->where('materi_id', $materi->id)
                ->first();
            if (!$progresMateri) {
                $user->progresMateri()->create([
                    'materi_id' => $materi->id,
                    'is_selesai' => false,
                    'progress_persen' => 0,
                    'tanggal_mulai' => now(),
                    'durasi_belajar_menit' => 0
                ]);
            }
            // Get progress data
            $progress = $user->progresMateri()
                ->where('materi_id', $materi->id)
                ->first();
            $materi->progress = $progress ? [
                'is_selesai' => $progress->is_selesai,
                'progress_persen' => $progress->progress_persen,
                'tanggal_mulai' => $progress->tanggal_mulai,
                'tanggal_selesai' => $progress->tanggal_selesai,
                'durasi_belajar_menit' => $progress->durasi_belajar_menit
            ] : null;
        }
        // If admin/instruktur, check if they have access to the course
        else if (!($user->tokenCan('super_admin') || $modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Generate file URL if applicable
        if ($materi->tipe_konten !== 'link' && $materi->file_path) {
            $materi->file_url = url('storage/' . $materi->file_path);
        }
        return response()->json([
            'status' => 'success',
            'data' => $materi
        ]);
    }
    public function update(Request $request, $id)
    {
        $materi = Materi::with('modul.kursus:id,admin_instruktur_id')->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $materi->modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'judul_materi' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'nullable|integer|min:1',
            'is_wajib' => 'nullable|boolean',
            'urutan' => 'nullable|integer|min:1',
            'published' => 'nullable|boolean',
            'file' => 'nullable|file|max:102400',
            'link' => 'nullable|url'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        // Handle reordering if urutan changed
        if ($request->has('urutan') && $request->urutan != $materi->urutan) {
            $oldUrutan = $materi->urutan;
            $newUrutan = $request->urutan;
            if ($newUrutan > $oldUrutan) {
                // Moving down, shift up materi in between
                Materi::where('modul_id', $materi->modul_id)
                    ->where('urutan', '>', $oldUrutan)
                    ->where('urutan', '<=', $newUrutan)
                    ->decrement('urutan');
            } else {
                // Moving up, shift down materi in between
                Materi::where('modul_id', $materi->modul_id)
                    ->where('urutan', '>=', $newUrutan)
                    ->where('urutan', '<', $oldUrutan)
                    ->increment('urutan');
            }
        }
        // Handle file replacement if provided
        if ($request->hasFile('file')) {
            // Delete old file
            if ($materi->file_path && $materi->tipe_konten !== 'link') {
                Storage::disk('public')->delete($materi->file_path);
            }
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store file based on content type
            $path = 'materi/' . $materi->tipe_konten . '/' . $filename;
            Storage::disk('public')->put($path, file_get_contents($file));
            $materi->file_path = $path;
            $materi->ukuran_file = $file->getSize();
        } else if ($materi->tipe_konten === 'link' && $request->has('link')) {
            $materi->file_path = $request->link;
        }
        // Update publication status
        if ($request->has('published')) {
            if ($request->published && !$materi->published_at) {
                $materi->published_at = now();
            } else if (!$request->published && $materi->published_at) {
                $materi->published_at = null;
            }
        }
        $materi->fill($request->except(['file', 'link', 'published']));
        $materi->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Materi berhasil diperbarui',
            'data' => $materi
        ]);
    }
    public function destroy($id)
    {
        $materi = Materi::with('modul.kursus:id,admin_instruktur_id')->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $materi->modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Delete file if exists
        if ($materi->file_path && $materi->tipe_konten !== 'link') {
            Storage::disk('public')->delete($materi->file_path);
        }
        // Reorder remaining materi
        Materi::where('modul_id', $materi->modul_id)
            ->where('urutan', '>', $materi->urutan)
            ->decrement('urutan');
        $materi->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Materi berhasil dihapus'
        ]);
    }
    // Mark materi as completed
    public function markComplete(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        if (!$user->tokenCan('peserta')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya peserta yang dapat menandai materi sebagai selesai'
            ], 403);
        }
        // Check if user is enrolled in the course
        $isEnrolled = $user->pendaftaranKursus()
            ->where('kursus_id', $materi->modul->kursus_id)
            ->whereIn('status', ['disetujui', 'aktif'])
            ->exists();
        if (!$isEnrolled) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum terdaftar dalam kursus ini'
            ], 403);
        }
        // Update or create progress
        $progress = $user->progresMateri()
            ->where('materi_id', $materi->id)
            ->first();
        if ($progress) {
            $progress->update([
                'is_selesai' => true,
                'progress_persen' => 100,
                'tanggal_selesai' => now(),
                'durasi_belajar_menit' => $request->durasi_belajar_menit ?? $progress->durasi_belajar_menit
            ]);
        } else {
            $progress = $user->progresMateri()->create([
                'materi_id' => $materi->id,
                'is_selesai' => true,
                'progress_persen' => 100,
                'tanggal_mulai' => now(),
                'tanggal_selesai' => now(),
                'durasi_belajar_menit' => $request->durasi_belajar_menit ?? 0
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Materi berhasil ditandai sebagai selesai',
            'data' => $progress
        ]);
    }
    // Update progress without completing
    public function updateProgress(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        if (!$user->tokenCan('peserta')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya peserta yang dapat memperbarui progres materi'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'progress_persen' => 'required|integer|min:0|max:100',
            'durasi_belajar_menit' => 'nullable|integer|min:0'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        // Check if user is enrolled in the course
        $isEnrolled = $user->pendaftaranKursus()
            ->where('kursus_id', $materi->modul->kursus_id)
            ->whereIn('status', ['disetujui', 'aktif'])
            ->exists();
        if (!$isEnrolled) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum terdaftar dalam kursus ini'
            ], 403);
        }
        // Update or create progress
        $progress = $user->progresMateri()
            ->where('materi_id', $materi->id)
            ->first();
        if ($progress) {
            $progress->update([
                'progress_persen' => $request->progress_persen,
                'is_selesai' => $request->progress_persen == 100,
                'tanggal_selesai' => $request->progress_persen == 100 ? now() : null,
                'durasi_belajar_menit' => $request->durasi_belajar_menit ?? $progress->durasi_belajar_menit
            ]);
        } else {
            $progress = $user->progresMateri()->create([
                'materi_id' => $materi->id,
                'is_selesai' => $request->progress_persen == 100,
                'progress_persen' => $request->progress_persen,
                'tanggal_mulai' => now(),
                'tanggal_selesai' => $request->progress_persen == 100 ? now() : null,
                'durasi_belajar_menit' => $request->durasi_belajar_menit ?? 0
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Progres materi berhasil diperbarui',
            'data' => $progress
        ]);
    }
}
