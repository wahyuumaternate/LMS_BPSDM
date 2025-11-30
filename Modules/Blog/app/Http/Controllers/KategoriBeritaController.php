<?php

namespace Modules\Blog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Blog\Entities\KategoriBerita;
use Illuminate\Support\Str;

class KategoriBeritaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategoris = KategoriBerita::withCount('berita')
                                  ->ordered()
                                  ->get();

        return view('blog::kategori.index', compact('kategoris'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('blog::kategori.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|in:primary,secondary,success,danger,warning,info,dark',
            'is_active' => 'boolean',
            'urutan' => 'nullable|integer|min:0',
        ]);

        // Auto-generate slug
        $validated['slug'] = Str::slug($request->nama_kategori);

        KategoriBerita::create($validated);

        return redirect()->route('kategori-berita.index')
                        ->with('success', 'Kategori berhasil dibuat!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kategori = KategoriBerita::findOrFail($id);
        return view('blog::kategori.edit', compact('kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $kategori = KategoriBerita::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|in:primary,secondary,success,danger,warning,info,dark',
            'is_active' => 'boolean',
            'urutan' => 'nullable|integer|min:0',
        ]);

        // Update slug if nama_kategori changed
        if ($kategori->nama_kategori !== $request->nama_kategori) {
            $validated['slug'] = Str::slug($request->nama_kategori);
        }

        $kategori->update($validated);

        return redirect()->route('kategori-berita.index')
                        ->with('success', 'Kategori berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kategori = KategoriBerita::findOrFail($id);

        // Check if kategori has berita
        if ($kategori->berita()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'Kategori tidak bisa dihapus karena masih memiliki berita!');
        }

        $kategori->delete();

        return redirect()->route('kategori-berita.index')
                        ->with('success', 'Kategori berhasil dihapus!');
    }
}