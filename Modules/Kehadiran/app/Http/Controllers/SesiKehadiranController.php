<?php

namespace Modules\Kehadiran\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kehadiran\Entities\SesiKehadiran;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SesiKehadiranController extends Controller
{
    public function index($kursusId)
    {
        $kursus = Kursus::findOrFail($kursusId);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        // If peserta, check if they're enrolled in the course
        if ($user->tokenCan('peserta')) {
            $isEnrolled = $user->pendaftaranKursus()
                ->where('kursus_id', $kursusId)
                ->whereIn('status', ['disetujui', 'aktif'])
                ->exists();
            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda belum terdaftar dalam kursus ini'
                ], 403);
            }
            $sesi = SesiKehadiran::where('kursus_id', $kursusId)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->get();
            // Get kehadiran data for this peserta
            foreach ($sesi as $s) {
                $kehadiran = $user->kehadiran()
                    ->where('sesi_id', $s->id)
                    ->first();
                $s->kehadiran = $kehadiran;
            }
        }
        // If admin/instruktur, check if they have access to the course
        else if ($user->tokenCan('super_admin') || $kursus->admin_instruktur_id === $user->id) {
            $sesi = SesiKehadiran::where('kursus_id', $kursusId)
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->get();
            // Get all kehadiran for each sesi
            foreach ($sesi as $s) {
                $kehadiran = $s->kehadiran()
                    ->with('peserta:id,nama_lengkap,nip')
                    ->get();
                $s->kehadiran = $kehadiran;
                $s->hadir_count = $kehadiran->whereIn('status', ['hadir', 'terlambat'])->count();
                $s->tidak_hadir_count = $kehadiran->where('status', 'tidak_hadir')->count();
                $s->izin_count = $kehadiran->whereIn('status', ['izin', 'sakit'])->count();
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'data' => [
                'kursus' => [
                    'id' => $kursus->id,
                    'judul' => $kursus->judul
                ],
                'sesi' => $sesi
            ]
        ]);
    }
    public function store(Request $request, $kursusId)
    {
        $kursus = Kursus::findOrFail($kursusId);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'pertemuan_ke' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'durasi_berlaku_menit' => 'nullable|integer|min:1',
            'status' => 'nullable|in:scheduled,ongoing,completed,cancelled'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $sesi = new SesiKehadiran($request->all());
        $sesi->kursus_id = $kursusId;
        $sesi->status = $request->status ?? 'scheduled';
        $sesi->durasi_berlaku_menit = $request->durasi_berlaku_menit ?? 30;
        // Generate QR codes
        $sesi->qr_code_checkin = Str::random(32);
        $sesi->qr_code_checkout = Str::random(32);
        $sesi->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Sesi kehadiran berhasil ditambahkan',
            'data' => $sesi
        ], 201);
    }
    public function show($id)
    {
        $sesi = SesiKehadiran::with(['kursus:id,judul,admin_instruktur_id'])->findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        // If peserta, check if they're enrolled in the course
        if ($user->tokenCan('peserta')) {
            $isEnrolled = $user->pendaftaranKursus()
                ->where('kursus_id', $sesi->kursus_id)
                ->whereIn('status', ['disetujui', 'aktif'])
                ->exists();
            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda belum terdaftar dalam kursus ini'
                ], 403);
            }
            // Get kehadiran data for this peserta
            $kehadiran = $user->kehadiran()
                ->where('sesi_id', $sesi->id)
                ->first();
            $sesi->kehadiran = $kehadiran;
            // Only return QR code if the session is ongoing and within allowed time
            if ($sesi->status === 'ongoing' && now()->between(
                now()->parse($sesi->tanggal . ' ' . $sesi->waktu_mulai)->subMinutes(15),
                now()->parse($sesi->tanggal . ' ' . $sesi->waktu_selesai)->addMinutes(15)
            )) {
                $sesi->qr_code_checkin_url = route('kehadiran.scan-checkin', ['token' => $sesi->qr_code_checkin]);
                $sesi->qr_code_checkout_url = route('kehadiran.scan-checkout', ['token' => $sesi->qr_code_checkout]);
            } else {
                $sesi->makeHidden(['qr_code_checkin', 'qr_code_checkout']);
            }
        }
        // If admin/instruktur, check if they have access to the course
        else if ($user->tokenCan('super_admin') || $sesi->kursus->admin_instruktur_id === $user->id) {
            // Get all kehadiran for this sesi
            $kehadiran = $sesi->kehadiran()
                ->with('peserta:id,nama_lengkap,nip')
                ->get();
            $sesi->kehadiran = $kehadiran;
            $sesi->hadir_count = $kehadiran->whereIn('status', ['hadir', 'terlambat'])->count();
            $sesi->tidak_hadir_count = $kehadiran->where('status', 'tidak_hadir')->count();
            $sesi->izin_count = $kehadiran->whereIn('status', ['izin', 'sakit'])->count();
            // Generate QR code URLs
            $sesi->qr_code_checkin_url = route('kehadiran.scan-checkin', ['token' => $sesi->qr_code_checkin]);
            $sesi->qr_code_checkout_url = route('kehadiran.scan-checkout', ['token' => $sesi->qr_code_checkout]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'data' => $sesi
        ]);
    }
    public function update(Request $request, $id)
    {
        $sesi = SesiKehadiran::with(['kursus:id,admin_instruktur_id'])->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $sesi->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'pertemuan_ke' => 'sometimes|required|integer|min:1',
            'tanggal' => 'sometimes|required|date',
            'waktu_mulai' => 'sometimes|required|date_format:H:i',
            'waktu_selesai' => 'sometimes|required|date_format:H:i|after:waktu_mulai',
            'durasi_berlaku_menit' => 'nullable|integer|min:1',
            'status' => 'nullable|in:scheduled,ongoing,completed,cancelled',
            'regenerate_qr' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        // Regenerate QR codes if requested
        if ($request->regenerate_qr) {
            $sesi->qr_code_checkin = Str::random(32);
            $sesi->qr_code_checkout = Str::random(32);
        }
        $sesi->update($request->except('regenerate_qr'));
        return response()->json([
            'status' => 'success',
            'message' => 'Sesi kehadiran berhasil diperbarui',
            'data' => $sesi
        ]);
    }
    public function destroy($id)
    {
        $sesi = SesiKehadiran::with(['kursus:id,admin_instruktur_id'])->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $sesi->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Check if sesi has kehadiran records
        $kehadiranCount = $sesi->kehadiran()->count();
        if ($kehadiranCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi tidak dapat dihapus karena memiliki ' . $kehadiranCount . ' rekaman kehadiran'
            ], 400);
        }
        $sesi->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Sesi kehadiran berhasil dihapus'
        ]);
    }
}
