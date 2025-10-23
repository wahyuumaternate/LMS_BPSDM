<?php

namespace Modules\Kategori\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kategori\Entities\KategoriKursus;
use Modules\Kategori\Transformers\KategoriKursusResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KategoriKursusController extends Controller
{
    public function index()
    {
        $kategori = KategoriKursus::orderBy('urutan')->get();
        return KategoriKursusResource::collection($kategori);
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:kategori_kursus',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Generate slug jika tidak disediakan
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nama_kategori']);
        }

        $kategori = KategoriKursus::create($data);

        return response()->json([
            'message' => 'Kategori kursus created successfully',
            'data' => new KategoriKursusResource($kategori)
        ], 201);
    }

    public function show($id)
    {
        $kategori = KategoriKursus::findOrFail($id);
        return new KategoriKursusResource($kategori);
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriKursus::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:kategori_kursus,slug,' . $id,
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kategori->update($request->all());

        return response()->json([
            'message' => 'Kategori kursus updated successfully',
            'data' => new KategoriKursusResource($kategori)
        ]);
    }

    public function destroy($id)
    {
        $kategori = KategoriKursus::findOrFail($id);

        // Check if kategori has related kursus
        if ($kategori->kursus()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete kategori. It has related kursus.'
            ], 422);
        }

        $kategori->delete();

        return response()->json([
            'message' => 'Kategori kursus deleted successfully'
        ]);
    }
}
