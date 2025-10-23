<?php

namespace Modules\Kursus\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kursus\Entities\PendaftaranKursus;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Transformers\PendaftaranKursusResource;
use Illuminate\Support\Facades\Validator;

class PendaftaranKursusController extends Controller
{
    public function index(Request $request)
    {
        $query = PendaftaranKursus::with(['kursus', 'peserta']);

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['pending', 'disetujui', 'ditolak', 'aktif', 'selesai', 'batal'])) {
            $query->where('status', $request->status);
        }

        // Filter by peserta_id
        if ($request->has('peserta_id')) {
            $query->where('peserta_id', $request->peserta_id);
        }

        // Filter by kursus_id
        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        $pendaftaran = $query->paginate(15);

        return PendaftaranKursusResource::collection($pendaftaran);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peserta_id' => 'required|exists:pesertas,id',
            'kursus_id' => 'required|exists:kursus,id',
            'tanggal_daftar' => 'nullable|date',
            'status' => 'nullable|in:pending,disetujui,ditolak,aktif,selesai,batal',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if already registered
        $existingPendaftaran = PendaftaranKursus::where('peserta_id', $request->peserta_id)
            ->where('kursus_id', $request->kursus_id)
            ->first();
        if ($existingPendaftaran) {
            return response()->json([
                'message' => 'Peserta already registered for this course.'
            ], 422);
        }

        // Check if registration is open
        $kursus = Kursus::findOrFail($request->kursus_id);
        if (!$kursus->isPendaftaranOpen() && $request->user()->role !== 'super_admin') {
            return response()->json([
                'message' => 'Registration is not open for this course.'
            ], 422);
        }

        // Check if quota is full
        $enrolledCount = PendaftaranKursus::where('kursus_id', $request->kursus_id)
            ->whereIn('status', ['pending', 'disetujui', 'aktif'])
            ->count();
        if ($enrolledCount >= $kursus->kuota_peserta && $kursus->kuota_peserta > 0) {
            return response()->json([
                'message' => 'Course quota is full.'
            ], 422);
        }

        // Set default values
        $data = $request->all();
        if (!isset($data['tanggal_daftar'])) {
            $data['tanggal_daftar'] = now()->format('Y-m-d');
        }
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        $pendaftaran = PendaftaranKursus::create($data);

        return response()->json([
            'message' => 'Pendaftaran created successfully',
            'data' => new PendaftaranKursusResource($pendaftaran)
        ], 201);
    }

    public function show($id)
    {
        $pendaftaran = PendaftaranKursus::with(['kursus', 'peserta'])->findOrFail($id);
        return new PendaftaranKursusResource($pendaftaran);
    }

    public function update(Request $request, $id)
    {
        $pendaftaran = PendaftaranKursus::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'peserta_id' => 'sometimes|required|exists:pesertas,id',
            'kursus_id' => 'sometimes|required|exists:kursus,id',
            'tanggal_daftar' => 'nullable|date',
            'status' => 'nullable|in:pending,disetujui,ditolak,aktif,selesai,batal',
            'alasan_ditolak' => 'nullable|string|required_if:status,ditolak',
            'nilai_akhir' => 'nullable|numeric|min:0|max:100',
            'predikat' => 'nullable|in:sangat_baik,baik,cukup,kurang',
            'tanggal_disetujui' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Set tanggal_disetujui if status is changed to disetujui
        if ($request->has('status') && $request->status === 'disetujui' && $pendaftaran->status !== 'disetujui') {
            $data['tanggal_disetujui'] = now();
        }

        // Set tanggal_selesai if status is changed to selesai
        if ($request->has('status') && $request->status === 'selesai' && $pendaftaran->status !== 'selesai') {
            $data['tanggal_selesai'] = now();
        }

        $pendaftaran->update($data);

        return response()->json([
            'message' => 'Pendaftaran updated successfully',
            'data' => new PendaftaranKursusResource($pendaftaran)
        ]);
    }

    public function destroy($id)
    {
        $pendaftaran = PendaftaranKursus::findOrFail($id);
        $pendaftaran->delete();

        return response()->json([
            'message' => 'Pendaftaran deleted successfully'
        ]);
    }
}
