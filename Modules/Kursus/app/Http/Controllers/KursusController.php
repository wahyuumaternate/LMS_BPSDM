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
                $file->storeAs('kursus/thumbnail', $filename);

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
                    Storage::disk('local')->delete('kursus/thumbnail/' . $kursus->thumbnail);
                }

                $file = $request->file('thumbnail');
                $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('kursus/thumbnail', $filename);

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

    public function store_modul(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required',
            'nama_modul' => 'required',
            'urutan' => 'required',
            'deskripsi' => 'nullable',
            'is_published' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        try {
            Modul::create($data);

            return redirect()->route('course.modul', $request->kursus_id)
                ->with('success', 'Modul berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error membuat modul: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update_modul(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_modul' => 'required',
            'urutan' => 'required',
            'deskripsi' => 'nullable',
            'is_published' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        try {
            Modul::findOrFail($id)->update($data);

            return redirect()->route('course.modul', $request->kursus_id)
                ->with('success', 'Perubahan modul berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error menyimpan perubahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy_modul($id)
    {
        try {
            $modul = Modul::findOrFail($id);
            $modul->delete();
            session()->flash('success', 'Modul berhasil dihapus.');

            return response()->json([
                'redirect' => route('course.modul', $modul->kursus_id), // atau page kamu
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error menghapus modul: ' . $e->getMessage())
                ->withInput();
        }
    }

    //  MATERI
    public function store_materi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modul_id' => 'required|exists:moduls,id',
            'judul_materi' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:1',
            'tipe_konten' => 'required|in:pdf,doc,video,audio,gambar,link,scorm',
            'file' => 'nullable|file|max:102400', // 100MB max
            'path_link' => 'nullable|url',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'is_wajib' => 'boolean',
        ]);

        if ($validator->fails()) {
            // dd($validator);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error_modal', 'create_materi');
        }

        $data = $request->except(['file', 'path_link']);

        try {
            // Handle file upload or external link
            if ($request->tipe_konten === 'link') {
                $data['file_path'] = $request->path_link;
            } else if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug($request->judul_materi) . '-' . time() . '.' . $extension;

                $folder = 'materi/files/' . $request->tipe_konten;
                $file->storeAs($folder, $filename, 'public');

                $data['file_path'] = $filename;
                $data['ukuran_file'] = round($file->getSize() / 1024); // convert to KB
            }

            if ($request->is_published) {
                $data['published_at'] = now();
            }

            Materi::create($data);
            return redirect()->route('course.modul', $request->kursus_id)
                ->with('success', 'Materi berhasil ditambahkan');
        } catch (\Exception $e) {
            // dd($e);
            return redirect()->back()
                ->with('error', 'Error membuat materi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update_materi(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'judul_materi' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:1',
            'tipe_konten' => 'required|in:pdf,doc,video,audio,gambar,link,scorm',
            'file' => 'nullable|file|max:102400', // 100MB max
            'path_link' => 'nullable|url',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'is_wajib' => 'boolean',
        ]);

        if ($validator->fails()) {
            // dd($validator);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error_modal', 'create_materi');
        }

        $data = $request->except(['file', 'path_link']);

        try {
            $materi = Materi::findOrFail($id);

            // Handle file upload or external link
            if ($request->tipe_konten === 'link') {
                $data['file_path'] = $request->path_link;
            } else if ($request->hasFile('file')) {
                // Delete old file if not a link
                if ($materi->tipe_konten !== 'link' && $materi->file_path) {
                    Storage::disk('public')->delete('materi/files/' . $materi->tipe_konten . '/' . $materi->file_path);
                }

                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug($request->judul_materi) . '-' . time() . '.' . $extension;

                $folder = 'materi/files/' . $request->tipe_konten;
                $file->storeAs($folder, $filename, 'public');

                $data['file_path'] = $filename;
                $data['ukuran_file'] = round($file->getSize() / 1024); // convert to KB
            }

            if ($request->is_published) {
                if (!$materi->published_at)
                    $data['published_at'] = now();
            } else {
                $data['published_at'] = null;
            }
            $materi->update($data);
            return redirect()->route('course.modul', $request->kursus_id)
                ->with('success', 'Perubahan materi berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error menyimpan perubahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy_materi($id)
    {
        try {
            $materi = Materi::with('modul')->findOrFail($id);

            // Delete file if not a link
            if ($materi->tipe_konten !== 'link' && $materi->file_path) {
                Storage::disk('public')->delete('materi/files/' . $materi->tipe_konten . '/' . $materi->file_path);
            }

            $materi->delete();
            session()->flash('success', 'Materi berhasil dihapus.');

            return response()->json([
                'redirect' => route('course.modul', $materi->modul->kursus_id), // atau page kamu
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error menghapus materi: ' . $e->getMessage())
                ->withInput();
        }
    }
}
