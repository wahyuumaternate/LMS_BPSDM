<?php

namespace Modules\JadwalKegiatan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\JadwalKegiatan\Entities\JadwalKegiatan as EntitiesJadwalKegiatan;
use App\Http\Controllers\Controller;

class JadwalKegiatanController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'nama_kegiatan' => 'required|string|max:255',
            'waktu_mulai_kegiatan' => 'required|date',
            'waktu_selesai_kegiatan' => 'required|date|after:waktu_mulai_kegiatan',
            'tipe' => 'required|in:online,offline,hybrid',
            'lokasi' => 'nullable|string|max:255',
            'link_meeting' => 'nullable|url',
            'keterangan' => 'nullable|string',
        ], [
            'kursus_id.required' => 'Kursus harus dipilih',
            'kursus_id.exists' => 'Kursus tidak ditemukan',
            'nama_kegiatan.required' => 'Nama kegiatan harus diisi',
            'waktu_mulai_kegiatan.required' => 'Waktu mulai harus diisi',
            'waktu_mulai_kegiatan.date' => 'Format waktu mulai tidak valid',
            'waktu_selesai_kegiatan.required' => 'Waktu selesai harus diisi',
            'waktu_selesai_kegiatan.date' => 'Format waktu selesai tidak valid',
            'waktu_selesai_kegiatan.after' => 'Waktu selesai harus setelah waktu mulai',
            'tipe.required' => 'Tipe kegiatan harus dipilih',
            'tipe.in' => 'Tipe kegiatan tidak valid',
            'link_meeting.url' => 'Format link meeting tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            EntitiesJadwalKegiatan::create($request->all());

            return redirect()->route('course.jadwal', $request->kursus_id)
                ->with('success', 'Jadwal kegiatan berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan jadwal kegiatan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'nama_kegiatan' => 'required|string|max:255',
            'waktu_mulai_kegiatan' => 'required|date',
            'waktu_selesai_kegiatan' => 'required|date|after:waktu_mulai_kegiatan',
            'tipe' => 'required|in:online,offline,hybrid',
            'lokasi' => 'nullable|string|max:255',
            'link_meeting' => 'nullable|url',
            'keterangan' => 'nullable|string',
        ], [
            'kursus_id.required' => 'Kursus harus dipilih',
            'kursus_id.exists' => 'Kursus tidak ditemukan',
            'nama_kegiatan.required' => 'Nama kegiatan harus diisi',
            'waktu_mulai_kegiatan.required' => 'Waktu mulai harus diisi',
            'waktu_mulai_kegiatan.date' => 'Format waktu mulai tidak valid',
            'waktu_selesai_kegiatan.required' => 'Waktu selesai harus diisi',
            'waktu_selesai_kegiatan.date' => 'Format waktu selesai tidak valid',
            'waktu_selesai_kegiatan.after' => 'Waktu selesai harus setelah waktu mulai',
            'tipe.required' => 'Tipe kegiatan harus dipilih',
            'tipe.in' => 'Tipe kegiatan tidak valid',
            'link_meeting.url' => 'Format link meeting tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $jadwal = EntitiesJadwalKegiatan::findOrFail($id);
            $jadwal->update($request->all());

            return redirect()->route('course.jadwal', $request->kursus_id)
                ->with('success', 'Jadwal kegiatan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui jadwal kegiatan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $jadwal = EntitiesJadwalKegiatan::findOrFail($id);
            $kursusId = $jadwal->kursus_id;
            $jadwal->delete();

            return redirect()->route('course.jadwal', $kursusId)
                ->with('success', 'Jadwal kegiatan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus jadwal kegiatan: ' . $e->getMessage());
        }
    }
}
