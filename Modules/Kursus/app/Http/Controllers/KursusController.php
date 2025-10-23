<?php

namespace Modules\Kursus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KursusController extends Controller
{
    public function index(Request $request)
    {
        $query = Kursus::with(['kategori:id,nama_kategori', 'instruktur:id,nama_lengkap,gelar_depan,gelar_belakang']);
        // Filter by kategori
        if ($request->has('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }
        // Filter by tipe
        if ($request->has('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        // Filter by search term (judul atau deskripsi)
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $request->search . '%');
            });
        }
        // Filter by instructor (admin only)
        if (Auth::guard('sanctum')->user()->tokenCan('super_admin') && $request->has('instruktur_id')) {
            $query->where('admin_instruktur_id', $request->instruktur_id);
        }
        // Filter kursus by instruktur (if not super admin)
        if (Auth::guard('sanctum')->user()->tokenCan('instruktur')) {
            $query->where('admin_instruktur_id', Auth::id());
        }
        // Default ordering
        $orderBy = $request->order_by ?? 'created_at';
        $orderDir = $request->order_dir ?? 'desc';
        $kursus = $query->orderBy($orderBy, $orderDir)->paginate($request->per_page ?? 15);
        return response()->json([
            'status' => 'success',
            'data' => $kursus
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'required|exists:kategori_kursus,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tujuan_pembelajaran' => 'nullable|string',
            'sasaran_peserta' => 'nullable|string',
            'durasi_jam' => 'nullable|integer|min:1',
            'tanggal_buka_pendaftaran' => 'nullable|date',
            'tanggal_tutup_pendaftaran' => 'nullable|date|after_or_equal:tanggal_buka_pendaftaran',
            'tanggal_mulai_kursus' => 'nullable|date|after_or_equal:tanggal_tutup_pendaftaran',
            'tanggal_selesai_kursus' => 'nullable|date|after_or_equal:tanggal_mulai_kursus',
            'kuota_peserta' => 'nullable|integer|min:1',
            'level' => 'required|in:dasar,menengah,lanjut',
            'tipe' => 'required|in:daring,luring,hybrid',
            'status' => 'nullable|in:draft,aktif,nonaktif,selesai',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $request->all();
        $data['admin_instruktur_id'] = Auth::id();
        $data['kode_kursus'] = 'K' . date('Ym') . Str::random(5);
        $data['status'] = $request->status ?? 'draft';
        // Upload thumbnail
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('kursus/thumbnail', $filename, 'public');
            $data['thumbnail'] = $filename;
        }
        $kursus = Kursus::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kursus berhasil dibuat',
            'data' => $kursus
        ], 201);
    }
    public function show($id)
    {
        $kursus = Kursus::with([
            'kategori:id,nama_kategori',
            'instruktur:id,nama_lengkap,gelar_depan,gelar_belakang',
            'modul:id,kursus_id,nama_modul,urutan',
            'prasyarat:id,kursus_id,kursus_prasyarat_id,deskripsi,is_wajib',
            'prasyarat.kursusPrasyarat:id,judul'
        ])->find($id);
        if (!$kursus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kursus tidak ditemukan'
            ], 404);
        }
        // Check authorization (only super admin or course instructor can see draft courses)
        $user = Auth::guard('sanctum')->user();
        if ($kursus->status === 'draft' && !($user->tokenCan('super_admin') || $kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'data' => $kursus
        ]);
    }
    public function update(Request $request, $id)
    {
        $kursus = Kursus::find($id);
        if (!$kursus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kursus tidak ditemukan'
            ], 404);
        }
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'sometimes|required|exists:kategori_kursus,id',
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
            'tujuan_pembelajaran' => 'nullable|string',
            'sasaran_peserta' => 'nullable|string',
            'durasi_jam' => 'nullable|integer|min:1',
            'tanggal_buka_pendaftaran' => 'nullable|date',
            'tanggal_tutup_pendaftaran' => 'nullable|date|after_or_equal:tanggal_buka_pendaftaran',
            'tanggal_mulai_kursus' => 'nullable|date|after_or_equal:tanggal_tutup_pendaftaran',
            'tanggal_selesai_kursus' => 'nullable|date|after_or_equal:tanggal_mulai_kursus',
            'kuota_peserta' => 'nullable|integer|min:1',
            'level' => 'sometimes|required|in:dasar,menengah,lanjut',
            'tipe' => 'sometimes|required|in:daring,luring,hybrid',
            'status' => 'nullable|in:draft,aktif,nonaktif,selesai',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $request->all();
        // Upload thumbnail
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($kursus->thumbnail) {
                Storage::disk('public')->delete('kursus/thumbnail/' . $kursus->thumbnail);
            }
            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('kursus/thumbnail', $filename, 'public');
            $data['thumbnail'] = $filename;
        }
        $kursus->update($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Kursus berhasil diperbarui',
            'data' => $kursus
        ]);
    }
    public function destroy($id)
    {
        $kursus = Kursus::find($id);
        if (!$kursus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kursus tidak ditemukan'
            ], 404);
        }
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Check if kursus has pendaftaran
        $pendaftaranCount = $kursus->pendaftaran()->count();
        if ($pendaftaranCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kursus tidak dapat dihapus karena memiliki ' . $pendaftaranCount . ' pendaftaran'
            ], 400);
        }
        // Delete thumbnail
        if ($kursus->thumbnail) {
            Storage::disk('public')->delete('kursus/thumbnail/' . $kursus->thumbnail);
        }
        $kursus->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Kursus berhasil dihapus'
        ]);
    }
}
