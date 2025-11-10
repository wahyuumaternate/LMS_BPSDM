<?php

namespace Modules\Kursus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\Kategori\Entities\KategoriKursus;
use Modules\Kursus\Entities\Kursus;

class KursusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function table(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $perPage = max(5, min(100, (int) $perPage));

        $query = Kursus::with(['kategori', 'adminInstruktur']);

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['draft', 'aktif', 'nonaktif', 'selesai'])) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->search) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        $kursus = $query->paginate($perPage);

        return view('kursus::partial.table', compact('kursus'));
    }

    public function index()
    {
        return view('kursus::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = KategoriKursus::get();
        return view('kursus::create', compact('kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'admin_instruktur_id' => 'required|exists:admin_instrukturs,id',
                'kategori_id' => 'required|exists:kategori_kursus,id',
                'kode_kursus' => 'required|string|max:50|unique:kursus',
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'tujuan_pembelajaran' => 'nullable|string',
                'sasaran_peserta' => 'nullable|string',
                'durasi_jam' => 'nullable|integer|min:0',
                'tanggal_buka_pendaftaran' => 'nullable|date',
                'tanggal_tutup_pendaftaran' => 'nullable|date|after_or_equal:tanggal_buka_pendaftaran',
                'tanggal_mulai_kursus' => 'nullable|date|after_or_equal:tanggal_tutup_pendaftaran',
                'tanggal_selesai_kursus' => 'nullable|date|after_or_equal:tanggal_mulai_kursus',
                'kuota_peserta' => 'nullable|integer|min:0',
                'level' => 'required|in:dasar,menengah,lanjut',
                'tipe' => 'required|in:daring,luring,hybrid',
                'status' => 'required|in:draft,aktif,nonaktif,selesai',
                'thumbnail' => 'nullable|mimes:jpeg,png,jpg|max:2048',
                'passing_grade' => 'nullable|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except('thumbnail'); // ambil semua data kecuali thumbnail

            // Upload thumbnail jika ada
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();

                // simpan ke storage/public/kursus/thumbnail
                $file->storeAs('public/kursus/thumbnail', $filename);

                $data['thumbnail'] = $filename; // set ke data
            }

            Kursus::create($data);

            return redirect()->route('course.index')
                ->with('success', 'Kursus berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error membuat kategori: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('kursus::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('kursus::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kursus = Kursus::findOrFail($id);
        $kursus->delete();
    }

    public function search_instruktur(Request $request)
    {
        $data = AdminInstruktur::where('role', 'instruktur')->where('nama_lengkap', 'LIKE', '%' . $request->q . '%')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->nama_gelar = $item->nama_lengkap_dengan_gelar;
                return $item;
            });

        return response()->json($data);
    }
}
