<?php

namespace Modules\SesiKehadiran\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SesiKehadiran\Entities\SesiKehadiran;
use Modules\Kursus\Entities\Kursus;

class SesiKehadiranController extends Controller
{
    /**
     * Tampilkan halaman sesi kehadiran untuk kursus tertentu
     */
    public function index($kursusId)
    {
        $kursus = Kursus::with(['sesiKehadiran.kehadirans'])->findOrFail($kursusId);
        return view('kursus::kehadiran.sesi-kehadiran', compact('kursus'));
    }

    /**
     * Store sesi kehadiran baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kursus_id' => 'required|exists:kursus,id',
            'pertemuan_ke' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'durasi_berlaku_menit' => 'required|integer|min:1',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ], [
            'pertemuan_ke.required' => 'Pertemuan ke- wajib diisi',
            'tanggal.required' => 'Tanggal wajib diisi',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai',
            'durasi_berlaku_menit.required' => 'Durasi check-in wajib diisi',
            'status.required' => 'Status wajib dipilih',
        ]);

        SesiKehadiran::create($validated);

        return redirect()
            ->route('sesi-kehadiran.index', $request->kursus_id)
            ->with('success', 'Sesi kehadiran berhasil ditambahkan');
    }

    /**
     * Update sesi kehadiran
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kursus_id' => 'required|exists:kursus,id',
            'pertemuan_ke' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'durasi_berlaku_menit' => 'required|integer|min:1',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        $sesi = SesiKehadiran::findOrFail($id);
        $sesi->update($validated);

        return redirect()
            ->route('sesi-kehadiran.index', $request->kursus_id)
            ->with('success', 'Sesi kehadiran berhasil diperbarui');
    }

    /**
     * Hapus sesi kehadiran
     */
    public function destroy($id)
    {
        $sesi = SesiKehadiran::findOrFail($id);
        $kursusId = $sesi->kursus_id;
        $sesi->delete();

        return redirect()
            ->route('sesi-kehadiran.index', $kursusId)
            ->with('success', 'Sesi kehadiran berhasil dihapus');
    }


    /**
     * Detail sesi kehadiran dengan daftar peserta
     */
    public function detail($id)
    {
        $sesi = SesiKehadiran::with(['kursus', 'kehadirans.peserta'])->findOrFail($id);
        $kursus = $sesi->kursus; // Tambahkan variable kursus
        return view('kursus::kehadiran.detail', compact('sesi', 'kursus'));
    }

    /**
     * Update status kehadiran peserta
     */
    public function updateStatusKehadiran(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:hadir,terlambat,izin,sakit,tidak_hadir',
            'keterangan' => 'nullable|string',
        ]);

        $kehadiran = \Modules\SesiKehadiran\Entities\Kehadiran::findOrFail($id);
        $kehadiran->update($validated);

        return redirect()
            ->route('sesi-kehadiran.detail', $kehadiran->sesi_id)
            ->with('success', 'Status kehadiran berhasil diperbarui');
    }
}
