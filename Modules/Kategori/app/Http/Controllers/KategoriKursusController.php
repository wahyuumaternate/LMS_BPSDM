<?php

namespace Modules\Kategori\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kategori\Entities\KategoriKursus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Kategori\Entities\JenisKursus;

class KategoriKursusController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        try {
            $kategori = KategoriKursus::orderBy('urutan')->get();
            return view('kategori::index', compact('kategori'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error mengambil data kategori: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_kategori' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:kategori_kursus',
                'deskripsi' => 'nullable|string',
                'icon' => 'nullable|string|max:255',
                'urutan' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'create');
            }

            $data = $request->all();

            // Generate slug jika tidak disediakan
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['nama_kategori']);
            }

            KategoriKursus::create($data);

            return redirect()->route('kategori.kategori-kursus.index')
                ->with('success', 'Kategori kursus berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error membuat kategori: ' . $e->getMessage())
                ->withInput()
                ->with('error_modal', 'create');
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        try {
            $kategori = KategoriKursus::findOrFail($id);
           
            // dd($jenisKursus);
            return view('kategori::show', compact('kategori'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('kategori.kategori-kursus.index')
                ->with('error', 'Kategori tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('kategori.kategori-kursus.index')
                ->with('error', 'Error menampilkan kategori: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $kategori = KategoriKursus::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_kategori' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:kategori_kursus,slug,' . $id,
                'deskripsi' => 'nullable|string',
                'icon' => 'nullable|string|max:255',
                'urutan' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'edit')
                    ->with('edit_id', $id);
            }

            $data = $request->all();

            // Update slug if nama_kategori is updated but slug isn't provided
            if (isset($data['nama_kategori']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['nama_kategori']);
            }

            $kategori->update($data);

            return redirect()->route('kategori.kategori-kursus.index')
                ->with('success', 'Kategori kursus berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('kategori.kategori-kursus.index')
                ->with('error', 'Kategori tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error memperbarui kategori: ' . $e->getMessage())
                ->withInput()
                ->with('error_modal', 'edit')
                ->with('edit_id', $id);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $kategori = KategoriKursus::findOrFail($id);

            // Check if kategori has related kursus
            if ($kategori->kursus()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus kategori. Kategori memiliki kursus terkait.');
            }

            $kategori->delete();

            return redirect()->route('kategori.kategori-kursus.index')
                ->with('success', 'Kategori kursus berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('kategori.kategori-kursus.index')
                ->with('error', 'Kategori tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('kategori.kategori-kursus.index')
                ->with('error', 'Error menghapus kategori: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan kursus berdasarkan kategori.
     * 
     * @param  string  $slug
     * @return \Illuminate\Contracts\View\View
     */
    public function showBySlug($slug)
    {
        try {
            $kategori = KategoriKursus::where('slug', $slug)->firstOrFail();
            $kursus = $kategori->kursus()->paginate(12);

            return view('kategori::show_kursus', compact('kategori', 'kursus'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('kategori.kategori-kursus.index')
                ->with('error', 'Kategori tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('kategori.kategori-kursus.index')
                ->with('error', 'Error menampilkan kursus berdasarkan kategori: ' . $e->getMessage());
        }
    }

    /**
     * Ubah urutan kategori kursus.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        try {
            $orders = $request->input('orders', []);

            foreach ($orders as $id => $urutan) {
                $kategori = KategoriKursus::find($id);
                if ($kategori) {
                    $kategori->update(['urutan' => $urutan]);
                }
            }

            return response()->json(['success' => 'Urutan kategori berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error memperbarui urutan kategori: ' . $e->getMessage()], 500);
        }
    }
}
