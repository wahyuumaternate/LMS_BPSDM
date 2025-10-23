<?php

namespace Modules\Kursus\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Transformers\KursusResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KursusController extends Controller
{
    public function index(Request $request)
    {
        $query = Kursus::with(['kategori', 'adminInstruktur']);

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['draft', 'aktif', 'nonaktif', 'selesai'])) {
            $query->where('status', $request->status);
        }

        // Filter by kategori
        if ($request->has('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter by level
        if ($request->has('level') && in_array($request->level, ['dasar', 'menengah', 'lanjut'])) {
            $query->where('level', $request->level);
        }

        // Filter by tipe
        if ($request->has('tipe') && in_array($request->tipe, ['daring', 'luring', 'hybrid'])) {
            $query->where('tipe', $request->tipe);
        }

        // Filter by instruktur
        if ($request->has('admin_instruktur_id')) {
            $query->where('admin_instruktur_id', $request->admin_instruktur_id);
        }

        $kursus = $query->paginate(15);

        return KursusResource::collection($kursus);
    }
    public function store(Request $request)
    {
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
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('thumbnail');

        // Upload thumbnail jika ada
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/kursus/thumbnail', $filename);
            $data['thumbnail'] = $filename;
        }

        $kursus = Kursus::create($data);

        return response()->json([
            'message' => 'Kursus created successfully',
            'data' => new KursusResource($kursus)
        ], 201);
    }

    public function show($id)
    {
        $kursus = Kursus::with(['kategori', 'adminInstruktur', 'prasyarats.kursusPrasyarat'])->findOrFail($id);
        return new KursusResource($kursus);
    }

    public function update(Request $request, $id)
    {
        $kursus = Kursus::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'admin_instruktur_id' => 'sometimes|required|exists:admin_instrukturs,id',
            'kategori_id' => 'sometimes|required|exists:kategori_kursus,id',
            'kode_kursus' => 'sometimes|required|string|max:50|unique:kursus,kode_kursus,' . $id,
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
            'tujuan_pembelajaran' => 'nullable|string',
            'sasaran_peserta' => 'nullable|string',
            'durasi_jam' => 'nullable|integer|min:0',
            'tanggal_buka_pendaftaran' => 'nullable|date',
            'tanggal_tutup_pendaftaran' => 'nullable|date|after_or_equal:tanggal_buka_pendaftaran',
            'tanggal_mulai_kursus' => 'nullable|date|after_or_equal:tanggal_tutup_pendaftaran',
            'tanggal_selesai_kursus' => 'nullable|date|after_or_equal:tanggal_mulai_kursus',
            'kuota_peserta' => 'nullable|integer|min:0',
            'level' => 'sometimes|required|in:dasar,menengah,lanjut',
            'tipe' => 'sometimes|required|in:daring,luring,hybrid',
            'status' => 'sometimes|required|in:draft,aktif,nonaktif,selesai',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('thumbnail');

        // Upload thumbnail jika ada
        if ($request->hasFile('thumbnail')) {
            // Hapus thumbnail lama jika ada
            if ($kursus->thumbnail) {
                Storage::delete('public/kursus/thumbnail/' . $kursus->thumbnail);
            }

            $file = $request->file('thumbnail');
            $filename = Str::slug($request->judul ?? $kursus->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/kursus/thumbnail', $filename);
            $data['thumbnail'] = $filename;
        }

        $kursus->update($data);

        return response()->json([
            'message' => 'Kursus updated successfully',
            'data' => new KursusResource($kursus)
        ]);
    }

    public function destroy($id)
    {
        $kursus = Kursus::findOrFail($id);

        // Check if kursus has related pendaftaran
        if ($kursus->pendaftaran()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete kursus. It has related pendaftaran.'
            ], 422);
        }

        // Hapus thumbnail jika ada
        if ($kursus->thumbnail) {
            Storage::delete('public/kursus/thumbnail/' . $kursus->thumbnail);
        }

        $kursus->delete();

        return response()->json([
            'message' => 'Kursus deleted successfully'
        ]);
    }
}
