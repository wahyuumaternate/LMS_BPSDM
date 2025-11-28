<?php

namespace Modules\Kategori\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Kategori\Entities\JenisKursus;
use Modules\Kategori\Entities\KategoriKursus;
use Illuminate\Support\Str;

class JenisKursusController extends Controller
{
    public function index()
    {
        $jenisKursus = JenisKursus::with('kategoriKursus')
            ->orderBy('urutan')
            ->get();
        
        $kategoriKursus = KategoriKursus::orderBy('urutan')->get();
        
        return view('kategori::jenis-kursus.index', compact('jenisKursus', 'kategoriKursus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_kursus_id' => 'required|exists:kategori_kursus,id',
            'kode_jenis' => 'required|string|max:20|unique:jenis_kursus,kode_jenis',
            'nama_jenis' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:jenis_kursus,slug',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer|min:1',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['nama_jenis']);
        }

        // Set default values
        $validated['is_active'] = true;
        $validated['urutan'] = $validated['urutan'] ?? 1;

        JenisKursus::create($validated);

        return redirect()->route('kategori.jenis-kursus.index')
            ->with('success', 'Jenis kursus berhasil ditambahkan!');
    }

    public function show($id)
    {
        $jenisKursus = JenisKursus::with('kategoriKursus')->findOrFail($id);
        
        return view('kategori::jenis-kursus.show', compact('jenisKursus'));
    }

    public function update(Request $request, $id)
    {
        $jenisKursus = JenisKursus::findOrFail($id);

        $validated = $request->validate([
            'kategori_kursus_id' => 'required|exists:kategori_kursus,id',
            'kode_jenis' => 'required|string|max:20|unique:jenis_kursus,kode_jenis,' . $id,
            'nama_jenis' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:jenis_kursus,slug,' . $id,
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
            'urutan' => 'nullable|integer|min:1',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['nama_jenis']);
        }

        $jenisKursus->update($validated);

        return redirect()->route('kategori.jenis-kursus.index')
            ->with('success', 'Jenis kursus berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $jenisKursus = JenisKursus::findOrFail($id);
        $jenisKursus->delete();

        return redirect()->route('kategori.jenis-kursus.index')
            ->with('success', 'Jenis kursus berhasil dihapus!');
    }

    public function updateOrder(Request $request)
    {
        $orders = $request->input('orders');

        foreach ($orders as $id => $urutan) {
            JenisKursus::where('id', $id)->update(['urutan' => $urutan]);
        }

        return response()->json(['success' => 'Urutan jenis kursus berhasil diperbarui!']);
    }
}