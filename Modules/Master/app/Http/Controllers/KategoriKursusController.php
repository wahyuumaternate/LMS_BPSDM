<?php

namespace Modules\Master\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Master\Entities\KategoriKursus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KategoriKursusController extends Controller
{
    public function index()
    {
        $kategori = KategoriKursus::orderBy('urutan', 'asc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $kategori
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100',
            'slug' => 'nullable|string|unique:kategori_kursus,slug',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $request->all();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nama_kategori']);
        }
        $kategori = KategoriKursus::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kategori kursus berhasil ditambahkan',
            'data' => $kategori
        ], 201);
    }
    public function show($id)
    {
        $kategori = KategoriKursus::find($id);
        if (!$kategori) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori kursus tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $kategori
        ]);
    }
    public function update(Request $request, $id)
    {
        $kategori = KategoriKursus::find($id);
        if (!$kategori) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori kursus tidak ditemukan'
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100',
            'slug' => 'nullable|string|unique:kategori_kursus,slug,' . $id,
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $request->all();
        if (empty($data['slug']) && $request->has('nama_kategori')) {
            $data['slug'] = Str::slug($data['nama_kategori']);
        }
        $kategori->update($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kategori kursus berhasil diperbarui',
            'data' => $kategori
        ]);
    }
    public function destroy($id)
    {
        $kategori = KategoriKursus::find($id);
        if (!$kategori) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori kursus tidak ditemukan'
            ], 404);
        }
        // Check if kategori has kursus
        $kursusCount = $kategori->kursus()->count();
        if ($kursusCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki ' . $kursusCount . ' kursus'
            ], 400);
        }
        $kategori->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Kategori kursus berhasil dihapus'
        ]);
    }
}
