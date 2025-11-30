<?php

namespace Modules\Blog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Blog\Entities\Berita;
use Modules\Blog\Entities\KategoriBerita;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BeritaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Berita::with(['kategori', 'penulis'])
                      ->orderBy('created_at', 'desc');

        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->where('kategori_berita_id', $request->kategori);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $berita = $query->paginate(10);
        $kategoris = KategoriBerita::active()->ordered()->get();

        return view('blog::berita.index', compact('berita', 'kategoris'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = KategoriBerita::active()->ordered()->get();
        return view('blog::berita.create', compact('kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_berita_id' => 'required|exists:kategori_berita,id',
            'judul' => 'required|string|max:255',
            'ringkasan' => 'nullable|string',
            'konten' => 'required|string',
            'gambar_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sumber_gambar' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        // Handle image upload
        if ($request->hasFile('gambar_utama')) {
            $image = $request->file('gambar_utama');
            $imageName = time() . '_' . Str::slug($request->judul) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('berita', $imageName);
            $validated['gambar_utama'] = $imageName;
        }

        // Set author
        $validated['admin_instruktur_id'] = Auth::guard('admin_instruktur')->id();

        // Auto-generate slug
        $validated['slug'] = Str::slug($request->judul);

        // Set published_at if status is published
        if ($validated['status'] === 'published' && !$request->filled('published_at')) {
            $validated['published_at'] = now();
        }

        // Create berita
        $berita = Berita::create($validated);

        return redirect()->route('berita.index')
                        ->with('success', 'Berita berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $berita = Berita::with(['kategori', 'penulis'])->findOrFail($id);
        
        // Get related berita
        $related = $berita->getRelated(3);

        return view('blog::berita.show', compact('berita', 'related'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $berita = Berita::findOrFail($id);
        $kategoris = KategoriBerita::active()->ordered()->get();
        
        return view('blog::berita.edit', compact('berita', 'kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $berita = Berita::findOrFail($id);

        $validated = $request->validate([
            'kategori_berita_id' => 'required|exists:kategori_berita,id',
            'judul' => 'required|string|max:255',
            'ringkasan' => 'nullable|string',
            'konten' => 'required|string',
            'gambar_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sumber_gambar' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        // Handle image upload
        if ($request->hasFile('gambar_utama')) {
            // Delete old image
            if ($berita->gambar_utama) {
                Storage::delete('berita/' . $berita->gambar_utama);
            }

            $image = $request->file('gambar_utama');
            $imageName = time() . '_' . Str::slug($request->judul) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('berita', $imageName);
            $validated['gambar_utama'] = $imageName;
        }

        // Update slug if judul changed
        if ($berita->judul !== $request->judul) {
            $validated['slug'] = Str::slug($request->judul);
        }

        // Set published_at if status changed to published
        if ($validated['status'] === 'published' && !$berita->published_at && !$request->filled('published_at')) {
            $validated['published_at'] = now();
        }

        $berita->update($validated);

        return redirect()->route('berita.index')
                        ->with('success', 'Berita berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $berita = Berita::findOrFail($id);

        // Delete image
        if ($berita->gambar_utama) {
            Storage::delete('berita/' . $berita->gambar_utama);
        }

        $berita->delete();

        return redirect()->route('berita.index')
                        ->with('success', 'Berita berhasil dihapus!');
    }

    /**
     * Publish berita
     */
    public function publish($id)
    {
        $berita = Berita::findOrFail($id);
        $berita->publish();

        return redirect()->back()
                        ->with('success', 'Berita berhasil dipublish!');
    }

    /**
     * Archive berita
     */
    public function archive($id)
    {
        $berita = Berita::findOrFail($id);
        $berita->archive();

        return redirect()->back()
                        ->with('success', 'Berita berhasil diarsipkan!');
    }
}