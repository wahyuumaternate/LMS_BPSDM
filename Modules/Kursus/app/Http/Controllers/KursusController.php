<?php

namespace Modules\Kursus\Http\Controllers;

use App\Exports\PesertaKursusExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\Kategori\Entities\KategoriKursus;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Entities\PendaftaranKursus;
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
        $kursus = Kursus::with(['adminInstruktur', 'kategori', 'ujians.ujianResults'])
            ->findOrFail($id);

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

    /**
     * Display list of course participants
     */
    public function peserta($id)
    {
        try {
            $kursus = Kursus::with([
                'adminInstruktur',
                'kategori',
                'peserta' => function ($query) {
                    $query->with('opd')
                        ->orderBy('pendaftaran_kursus.tanggal_daftar', 'desc');
                }
            ])->findOrFail($id);

            return view('kursus::partial.peserta', compact('kursus'));
        } catch (\Exception $e) {
            Log::error('Error loading peserta: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat data peserta.');
        }
    }



    /**
     * Update participant status
     */
    public function updateStatus(Request $request, $kursusId, $pesertaId)
    {
        $request->validate([
            'status' => 'required|in:pending,disetujui,ditolak,aktif,selesai,batal',
            'alasan_ditolak' => 'required_if:status,ditolak|nullable|string|max:1000',
        ], [
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status tidak valid.',
            'alasan_ditolak.required_if' => 'Alasan ditolak harus diisi jika status ditolak.',
            'alasan_ditolak.max' => 'Alasan ditolak maksimal 1000 karakter.',
        ]);

        try {
            DB::beginTransaction();

            $pendaftaran = PendaftaranKursus::where('kursus_id', $kursusId)
                ->where('peserta_id', $pesertaId)
                ->firstOrFail();

            $oldStatus = $pendaftaran->status;
            $newStatus = $request->status;

            // Prepare update data
            $updateData = [
                'status' => $newStatus,
                'updated_at' => now(),
            ];

            // Set timestamps based on status
            if ($newStatus === 'disetujui' && $oldStatus !== 'disetujui') {
                $updateData['tanggal_disetujui'] = now();
            }

            if ($newStatus === 'selesai' && $oldStatus !== 'selesai') {
                $updateData['tanggal_selesai'] = now();
            }

            // Handle rejection reason
            if ($newStatus === 'ditolak') {
                $updateData['alasan_ditolak'] = $request->alasan_ditolak;
            } else {
                $updateData['alasan_ditolak'] = null;
            }

            // Reset nilai if status changed from selesai to other status
            if ($oldStatus === 'selesai' && $newStatus !== 'selesai') {
                $updateData['nilai_akhir'] = null;
                $updateData['predikat'] = null;
            }

            $pendaftaran->update($updateData);

            DB::commit();

            // Log the status change
            Log::info("Status changed for peserta {$pesertaId} in kursus {$kursusId}: {$oldStatus} -> {$newStatus}");

            return back()->with('success', 'Status peserta berhasil diperbarui dari ' . ucfirst($oldStatus) . ' menjadi ' . ucfirst($newStatus) . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating status: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui status peserta. Silakan coba lagi.');
        }
    }

    /**
     * Update participant final grade
     */
    public function updateNilai(Request $request, $kursusId, $pesertaId)
    {
        $request->validate([
            'nilai_akhir' => 'required|numeric|min:0|max:100',
            'predikat' => 'required|in:sangat_baik,baik,cukup,kurang',
        ], [
            'nilai_akhir.required' => 'Nilai akhir harus diisi.',
            'nilai_akhir.numeric' => 'Nilai akhir harus berupa angka.',
            'nilai_akhir.min' => 'Nilai akhir minimal 0.',
            'nilai_akhir.max' => 'Nilai akhir maksimal 100.',
            'predikat.required' => 'Predikat harus dipilih.',
            'predikat.in' => 'Predikat tidak valid.',
        ]);

        try {
            DB::beginTransaction();

            $pendaftaran = PendaftaranKursus::where('kursus_id', $kursusId)
                ->where('peserta_id', $pesertaId)
                ->firstOrFail();

            // Verify participant has completed the course
            if ($pendaftaran->status !== 'selesai') {
                return back()->with('error', 'Nilai hanya dapat diinput untuk peserta dengan status Selesai.');
            }

            // Validate predikat matches nilai
            $nilai = $request->nilai_akhir;
            $predikat = $request->predikat;

            $expectedPredikat = $this->calculatePredikat($nilai);
            if ($predikat !== $expectedPredikat) {
                return back()->with('error', 'Predikat tidak sesuai dengan nilai yang diinput.');
            }

            $pendaftaran->update([
                'nilai_akhir' => $nilai,
                'predikat' => $predikat,
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info("Grade updated for peserta {$pesertaId} in kursus {$kursusId}: {$nilai} ({$predikat})");

            return back()->with('success', 'Nilai akhir berhasil disimpan: ' . number_format($nilai, 2) . ' (' . ucwords(str_replace('_', ' ', $predikat)) . ')');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating nilai: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan nilai. Silakan coba lagi.');
        }
    }

    /**
     * Calculate predikat based on nilai
     */
    private function calculatePredikat($nilai)
    {
        if ($nilai >= 80) return 'sangat_baik';
        if ($nilai >= 70) return 'baik';
        if ($nilai >= 60) return 'cukup';
        return 'kurang';
    }

    public function exportPeserta($id)
    {
        try {
            $kursus = Kursus::findOrFail($id);

            $fileName = 'Peserta_' . str_replace(' ', '_', $kursus->judul) . '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new PesertaKursusExport($id),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Error exporting peserta: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data peserta.');
        }
    }

    /**
     * Bulk update status - FIXED VERSION
     */
    public function bulkUpdateStatus(Request $request, $kursusId)
    {
        // Validasi dengan string instead of json
        $request->validate([
            'peserta_ids' => 'required|string',
            'status' => 'required|in:pending,disetujui,ditolak,aktif,selesai,batal',
            'alasan_ditolak' => 'required_if:status,ditolak|nullable|string|max:1000',
        ], [
            'peserta_ids.required' => 'Pilih minimal 1 peserta.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status tidak valid.',
            'alasan_ditolak.required_if' => 'Alasan ditolak harus diisi jika status ditolak.',
        ]);

        try {
            // Decode JSON peserta_ids
            $pesertaIds = json_decode($request->peserta_ids, true);

            // Validate decoded data
            if (!is_array($pesertaIds) || empty($pesertaIds)) {
                return back()->with('error', 'Data peserta tidak valid. Pilih minimal 1 peserta.');
            }

            // Convert to integers and validate
            $pesertaIds = array_map('intval', $pesertaIds);

            // Log untuk debugging
            Log::info('Bulk update attempt', [
                'kursus_id' => $kursusId,
                'peserta_ids' => $pesertaIds,
                'status' => $request->status,
                'count' => count($pesertaIds)
            ]);

            DB::beginTransaction();

            $newStatus = $request->status;
            $count = 0;

            // Update berdasarkan status
            if ($newStatus === 'disetujui' || $newStatus === 'selesai') {
                // Individual updates untuk check old status
                $pendaftaranList = PendaftaranKursus::where('kursus_id', $kursusId)
                    ->whereIn('peserta_id', $pesertaIds)
                    ->get();

                if ($pendaftaranList->isEmpty()) {
                    DB::rollBack();
                    return back()->with('error', 'Tidak ada peserta yang ditemukan untuk diupdate.');
                }

                foreach ($pendaftaranList as $pendaftaran) {
                    $oldStatus = $pendaftaran->status;
                    $updateData = [
                        'status' => $newStatus,
                        'updated_at' => now()
                    ];

                    // Set tanggal_disetujui hanya jika entering status
                    if ($newStatus === 'disetujui' && $oldStatus !== 'disetujui') {
                        $updateData['tanggal_disetujui'] = now();
                    }

                    // Set tanggal_selesai hanya jika entering status
                    if ($newStatus === 'selesai' && $oldStatus !== 'selesai') {
                        $updateData['tanggal_selesai'] = now();
                    }

                    $pendaftaran->update($updateData);
                    $count++;
                }
            } else {
                // Bulk update untuk status lainnya
                $updateData = [
                    'status' => $newStatus,
                    'updated_at' => now(),
                ];

                // Handle alasan_ditolak
                if ($newStatus === 'ditolak') {
                    $updateData['alasan_ditolak'] = $request->alasan_ditolak;
                }

                $count = PendaftaranKursus::where('kursus_id', $kursusId)
                    ->whereIn('peserta_id', $pesertaIds)
                    ->update($updateData);

                if ($count === 0) {
                    DB::rollBack();
                    return back()->with('error', 'Tidak ada peserta yang berhasil diupdate. Periksa data peserta.');
                }
            }

            DB::commit();

            Log::info("Bulk status update success: {$count} peserta updated to {$newStatus}");

            $statusLabel = ucfirst($newStatus);
            return back()->with('success', "{$count} peserta berhasil diperbarui statusnya menjadi {$statusLabel}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk updating status', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
}
