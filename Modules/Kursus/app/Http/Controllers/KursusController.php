<?php

namespace Modules\Kursus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\Kategori\Entities\KategoriKursus;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Entities\Prasyarat;
use Modules\Materi\Entities\Materi;
use Modules\Modul\Entities\Modul;

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

        if (Auth::user()->role === 'instruktur') {
            $query->where('admin_instruktur_id', Auth::user()->id);
        }

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
        $instruktur = AdminInstruktur::role('instruktur')->get();
        return view('kursus::create', compact(['kategori', 'instruktur']));
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

            if (isset($data['thumbnail']) && !$request->hasFile('thumbnail')) {
                unset($data['thumbnail']);
            }

            // Upload thumbnail jika ada
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();

                // simpan ke storage/public/kursus/thumbnail
                $file->storeAs('kursus/thumbnail', $filename, 'public');

                $data['thumbnail'] = $filename; // set ke data
            }

            Kursus::create($data);

            return redirect()->route('course.index')
                ->with('success', 'Kursus berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error membuat kursus: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $kursus = Kursus::with(['adminInstruktur', 'kategori'])->findOrFail($id);
        if (Auth::user()->role == 'instruktur')
            if ($kursus->admin_instruktur_id !== Auth::user()->id)
                abort(403);
        return view('kursus::partial.detail', compact('kursus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kategori = KategoriKursus::get();
        $instruktur = AdminInstruktur::role('instruktur')->get();
        $kursus = Kursus::with(['adminInstruktur', 'kategori'])->findOrFail($id);

        if (Auth::user()->role == 'instruktur')
            if ($kursus->admin_instruktur_id !== Auth::user()->id)
                abort(403);

        return view('kursus::edit', compact(['kategori', 'instruktur', 'kursus']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $kursus = Kursus::findOrFail($id);

        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'admin_instruktur_id' => 'required|exists:admin_instrukturs,id',
                'kategori_id' => 'required|exists:kategori_kursus,id',
                'kode_kursus' => 'required|string|max:50|unique:kursus,kode_kursus,' . $id,
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

            if (isset($data['thumbnail']) && !$request->hasFile('thumbnail')) {
                unset($data['thumbnail']);
            }

            // Upload thumbnail jika ada
            if ($request->hasFile('thumbnail')) {
                // Hapus thumbnail lama jika ada
                if ($kursus->thumbnail) {
                    Storage::disk('public')->delete('kursus/thumbnail/' . $kursus->thumbnail);
                }

                $file = $request->file('thumbnail');
                $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('kursus/thumbnail', $filename, 'public');

                $data['thumbnail'] = $filename; // set ke data
            }

            $kursus->update($data);
            $kursus->save();

            return redirect()->route('course.index')
                ->with('success', 'Perubahan kursus berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error menyimpan perubahan: ' . $e->getMessage())
                ->withInput();
        }
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

    // PRASYARAT KURSUS
    public function prasyarat($id)
    {
        $kursus = Kursus::with(['adminInstruktur', 'kategori', 'prasyarats'])->findOrFail($id);

        if (Auth::user()->role == 'instruktur')
            if ($kursus->admin_instruktur_id !== Auth::user()->id)
                abort(403);

        return view('kursus::partial.prasyarat', compact('kursus'));
    }

    public function store_prasyarat(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'deskripsi' => 'required',
            'is_wajib' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        try {
            Prasyarat::create($data);

            return redirect()->route('course.prasyarat', $request->kursus_id)
                ->with('success', 'Prasyarat berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error membuat prasyarat: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update_prasyarat(Request $request, $id)
    {
        $prasyarat = Prasyarat::findOrFail($id);
        // Validasi input
        $validator = Validator::make($request->all(), [
            'deskripsi' => 'required',
            'is_wajib' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        try {
            $prasyarat->update($data);
            return redirect()->route('course.prasyarat', $prasyarat->kursus_id)
                ->with('success', 'Perubahan prasyarat berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error menyimpan perubahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function delete_prasyarat($id)
    {
        try {
            $prasyarat = Prasyarat::findOrFail($id);
            $prasyarat->delete();
            session()->flash('success', 'Prasyarat berhasil dihapus.');

            return response()->json([
                'redirect' => route('course.prasyarat', $prasyarat->kursus_id), // atau page kamu
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error menghapus prasyarat: ' . $e->getMessage())
                ->withInput();
        }
    }

    // MODUL KURSUS
    public function modul($id)
    {
        $kursus = Kursus::with([
            'adminInstruktur',
            'modul' => function ($query) {
                $query->orderBy('urutan', 'asc')
                    ->orderBy('created_at', 'desc')->with([
                        'materis' => function ($q) {
                            $q->orderBy('urutan', 'asc')
                                ->orderBy('created_at', 'desc');
                        }
                    ]);
            }
        ])->findOrFail($id);

        if (Auth::user()->role == 'instruktur')
            if ($kursus->admin_instruktur_id !== Auth::user()->id)
                abort(403);

        return view('kursus::partial.modul', compact('kursus'));
    }

    public function materi($id)
    {
        $kursus = Kursus::with([
            'adminInstruktur',
            'kategori',
            'modul.materis' // eager load modul beserta materinya
        ])->findOrFail($id);

        return view('kursus::materi.index', compact('kursus'));
    }

    public function tugas($id)
    {
        $kursus = Kursus::with([
            'adminInstruktur',
            'kategori',
            'modul' => function ($query) {
                $query->orderBy('urutan');
            },
            'modul.tugas'
        ])->findOrFail($id);

        return view('kursus::tugas.index', compact('kursus'));
    }

    public function ujian($id)
    {
        $kursus = Kursus::with(['adminInstruktur', 'kategori'])->findOrFail($id);
        return view('kursus::partial.ujian', compact('kursus'));
    }

    public function forum($id)
    {
        $kursus = Kursus::with(['adminInstruktur', 'kategori'])->findOrFail($id);
        return view('kursus::partial.forum', compact('kursus'));
    }

    public function kuis($id)
    {
        $kursus = Kursus::with(['adminInstruktur', 'kategori'])->findOrFail($id);
        return view('kursus::partial.kuis', compact('kursus'));
    }

    public function peserta($id)
    {
        $kursus = Kursus::with(['adminInstruktur', 'kategori'])->findOrFail($id);
        return view('kursus::partial.peserta', compact('kursus'));
    }
}
