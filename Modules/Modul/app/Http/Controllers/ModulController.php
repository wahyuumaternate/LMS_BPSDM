<?php

namespace Modules\Modul\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Modul\Entities\Modul;

class ModulController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kursus_id' => 'required|exists:kursus,id',
                'nama_modul' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'urutan' => 'nullable|integer|min:1',
                'is_published' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except(['_token']);
            $data['is_published'] = $request->has('is_published');

            // If urutan not provided, set it to the last position
            if (!$request->filled('urutan')) {
                $lastUrutan = Modul::where('kursus_id', $request->kursus_id)
                    ->max('urutan');
                $data['urutan'] = ($lastUrutan ?? 0) + 1;
            }

            Modul::create($data);

            return redirect()->route('course.modul', $request->kursus_id)
                ->with('success', 'Modul berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error membuat modul: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $modul = Modul::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'kursus_id' => 'required|exists:kursus,id',
                'nama_modul' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'urutan' => 'nullable|integer|min:1',
                'is_published' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except(['_token', '_method']);
            $data['is_published'] = $request->has('is_published');

            $modul->update($data);

            return redirect()->route('course.modul', $modul->kursus_id)
                ->with('success', 'Modul berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error mengupdate modul: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $modul = Modul::with('kursus')->findOrFail($id);
            $kursusId = $modul->kursus_id;

            $modul->delete();

            return response()->json([
                'success' => true,
                'message' => 'Modul berhasil dihapus',
                'redirect' => route('course.modul', $kursusId)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error menghapus modul: ' . $e->getMessage()
            ], 500);
        }
    }
}
