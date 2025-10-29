<?php

namespace Modules\SesiKehadiran\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SesiKehadiran\Entities\Kehadiran;
use Modules\SesiKehadiran\Entities\SesiKehadiran;
use Modules\SesiKehadiran\Transformers\KehadiranResource;
use Modules\SesiKehadiran\Services\QRCodeService;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Kehadiran",
 *     description="API Endpoints untuk manajemen Kehadiran Peserta"
 * )
 */
class KehadiranController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/kehadiran",
     *     summary="Mendapatkan daftar kehadiran",
     *     tags={"Kehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kehadiran berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="sesi_id", type="integer", example=2),
     *                     @OA\Property(property="peserta_id", type="integer", example=5),
     *                     @OA\Property(property="status", type="string", example="hadir")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Kehadiran::with(['sesi', 'peserta']);

        if ($request->has('sesi_id')) {
            $query->where('sesi_id', $request->sesi_id);
        }

        if ($request->has('peserta_id')) {
            $query->where('peserta_id', $request->peserta_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $kehadiranList = $query->orderBy('created_at', 'desc')->paginate(10);

        return KehadiranResource::collection($kehadiranList);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/kehadiran",
     *     summary="Mencatat kehadiran peserta",
     *     tags={"Kehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sesi_id", "peserta_id", "status"},
     *             @OA\Property(property="sesi_id", type="integer", example=1),
     *             @OA\Property(property="peserta_id", type="integer", example=3),
     *             @OA\Property(property="status", type="string", example="hadir")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kehadiran berhasil dicatat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kehadiran created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="status", type="string", example="hadir")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sesi_id' => 'required|exists:sesi_kehadiran,id',
            'peserta_id' => 'required|exists:peserta,id',
            'status' => 'required|in:hadir,terlambat,izin,sakit,tidak_hadir',
            'waktu_checkin' => 'nullable|date',
            'waktu_checkout' => 'nullable|date|after_or_equal:waktu_checkin',
            'lokasi_checkin' => 'nullable|string|max:255',
            'lokasi_checkout' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cek apakah sudah ada data kehadiran untuk peserta pada sesi ini
        $existing = Kehadiran::where('sesi_id', $request->sesi_id)
            ->where('peserta_id', $request->peserta_id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Kehadiran sudah tercatat untuk peserta ini pada sesi yang sama',
                'data' => new KehadiranResource($existing)
            ], 422);
        }

        // Hitung durasi jika ada checkin dan checkout
        $durasi = null;
        if ($request->waktu_checkin && $request->waktu_checkout) {
            $checkin = Carbon::parse($request->waktu_checkin);
            $checkout = Carbon::parse($request->waktu_checkout);
            $durasi = $checkout->diffInMinutes($checkin);
        }

        // Buat data kehadiran
        $kehadiran = Kehadiran::create(array_merge(
            $request->all(),
            ['durasi_menit' => $durasi]
        ));

        return response()->json([
            'message' => 'Kehadiran created successfully',
            'data' => new KehadiranResource($kehadiran)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/kehadiran/{id}",
     *     summary="Mendapatkan detail kehadiran",
     *     tags={"Kehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Kehadiran",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail kehadiran berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="sesi_id", type="integer", example=2),
     *                 @OA\Property(property="peserta_id", type="integer", example=5),
     *                 @OA\Property(property="status", type="string", example="hadir"),
     *                 @OA\Property(property="waktu_checkin", type="string", example="2025-10-30 09:00:00"),
     *                 @OA\Property(property="waktu_checkout", type="string", example="2025-10-30 11:00:00"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=120)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kehadiran tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $kehadiran = Kehadiran::with(['sesi.kursus', 'peserta'])->findOrFail($id);
        return new KehadiranResource($kehadiran);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/kehadiran/{id}",
     *     summary="Mengupdate data kehadiran",
     *     tags={"Kehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Kehadiran",
     *         required=true
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="terlambat", enum={"hadir", "terlambat", "izin", "sakit", "tidak_hadir"}),
     *             @OA\Property(property="waktu_checkin", type="string", format="date-time", example="2025-10-30 09:15:00"),
     *             @OA\Property(property="waktu_checkout", type="string", format="date-time", example="2025-10-30 11:00:00"),
     *             @OA\Property(property="lokasi_checkin", type="string", example="Ruang 101"),
     *             @OA\Property(property="lokasi_checkout", type="string", example="Ruang 101"),
     *             @OA\Property(property="keterangan", type="string", example="Peserta datang terlambat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kehadiran berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kehadiran updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="terlambat")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kehadiran tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $kehadiran = Kehadiran::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:hadir,terlambat,izin,sakit,tidak_hadir',
            'waktu_checkin' => 'nullable|date',
            'waktu_checkout' => 'nullable|date|after_or_equal:waktu_checkin',
            'lokasi_checkin' => 'nullable|string|max:255',
            'lokasi_checkout' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Hitung durasi jika ada checkin dan checkout
        $data = $request->all();
        if (
            $request->has('waktu_checkin') && $request->has('waktu_checkout') &&
            $request->waktu_checkin && $request->waktu_checkout
        ) {
            $checkin = Carbon::parse($request->waktu_checkin);
            $checkout = Carbon::parse($request->waktu_checkout);
            $data['durasi_menit'] = $checkout->diffInMinutes($checkin);
        }

        $kehadiran->update($data);

        return response()->json([
            'message' => 'Kehadiran updated successfully',
            'data' => new KehadiranResource($kehadiran)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/kehadiran/{id}",
     *     summary="Menghapus data kehadiran",
     *     tags={"Kehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Kehadiran",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kehadiran berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kehadiran deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kehadiran tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $kehadiran = Kehadiran::findOrFail($id);
        $kehadiran->delete();

        return response()->json([
            'message' => 'Kehadiran deleted successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/kehadiran/scan/{token}",
     *     summary="Proses scan QR Code untuk kehadiran",
     *     tags={"Kehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         description="Token dari QR Code",
     *         required=true
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"peserta_id", "type"},
     *             @OA\Property(property="peserta_id", type="integer", example=1),
     *             @OA\Property(property="type", type="string", enum={"checkin", "checkout"}, example="checkin"),
     *             @OA\Property(property="lokasi", type="string", example="Ruang 101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR Code berhasil discan",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Check-in berhasil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="hadir")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="QR Code tidak valid atau expired"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function scanQrCode(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'peserta_id' => 'required|exists:peserta,id',
            'type' => 'required|in:checkin,checkout',
            'lokasi' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verifikasi token QR code
        $verification = $this->qrCodeService->verifyToken($token, $request->type);

        if (!$verification || !$verification['valid']) {
            return response()->json([
                'message' => 'QR Code tidak valid atau expired'
            ], 400);
        }

        $sesiId = $verification['sesi_id'];
        $sesi = SesiKehadiran::findOrFail($sesiId);

        // Cek apakah sesi masih aktif
        $now = Carbon::now();
        $tanggal = Carbon::parse($sesi->tanggal);
        $waktuMulai = Carbon::parse($sesi->waktu_mulai);
        $waktuSelesai = Carbon::parse($sesi->waktu_selesai);

        $sesiStart = Carbon::create(
            $tanggal->year,
            $tanggal->month,
            $tanggal->day,
            $waktuMulai->hour,
            $waktuMulai->minute,
            0
        )->subMinutes($sesi->durasi_berlaku_menit);

        $sesiEnd = Carbon::create(
            $tanggal->year,
            $tanggal->month,
            $tanggal->day,
            $waktuSelesai->hour,
            $waktuSelesai->minute,
            0
        )->addMinutes($sesi->durasi_berlaku_menit);

        if ($now->lt($sesiStart) || $now->gt($sesiEnd)) {
            return response()->json([
                'message' => 'QR Code sudah tidak berlaku. Sesi kehadiran di luar waktu yang ditentukan.'
            ], 400);
        }

        // Cek apakah sudah ada data kehadiran
        $kehadiran = Kehadiran::where('sesi_id', $sesiId)
            ->where('peserta_id', $request->peserta_id)
            ->first();

        // Tentukan status kehadiran berdasarkan waktu scan
        $status = 'hadir';
        $waktuTenggatHadir = Carbon::create(
            $tanggal->year,
            $tanggal->month,
            $tanggal->day,
            $waktuMulai->hour,
            $waktuMulai->minute,
            0
        )->addMinutes(15); // Terlambat jika >15 menit dari waktu mulai

        if ($request->type == 'checkin' && $now->gt($waktuTenggatHadir)) {
            $status = 'terlambat';
        }

        if (!$kehadiran) {
            // Buat data kehadiran baru
            $data = [
                'sesi_id' => $sesiId,
                'peserta_id' => $request->peserta_id,
                'status' => $status
            ];

            if ($request->type == 'checkin') {
                $data['waktu_checkin'] = $now;
                $data['lokasi_checkin'] = $request->lokasi;
            } else {
                $data['waktu_checkout'] = $now;
                $data['lokasi_checkout'] = $request->lokasi;
            }

            $kehadiran = Kehadiran::create($data);
            $message = $request->type == 'checkin' ? 'Check-in berhasil' : 'Check-out berhasil';
        } else {
            // Update data kehadiran yang sudah ada
            $data = [];

            if ($request->type == 'checkin') {
                $data['waktu_checkin'] = $now;
                $data['lokasi_checkin'] = $request->lokasi;
                if (!$kehadiran->status || $kehadiran->status == 'tidak_hadir') {
                    $data['status'] = $status;
                }
            } else {
                $data['waktu_checkout'] = $now;
                $data['lokasi_checkout'] = $request->lokasi;
            }

            // Hitung durasi jika ada checkout
            if ($request->type == 'checkout' && $kehadiran->waktu_checkin) {
                $checkin = Carbon::parse($kehadiran->waktu_checkin);
                $data['durasi_menit'] = $now->diffInMinutes($checkin);
            }

            $kehadiran->update($data);
            $message = $request->type == 'checkin' ? 'Check-in berhasil diperbarui' : 'Check-out berhasil';
        }

        return response()->json([
            'message' => $message,
            'data' => new KehadiranResource($kehadiran)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/kehadiran/report",
     *     summary="Mendapatkan laporan kehadiran",
     *     tags={"Kehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter berdasarkan ID Kursus",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="peserta_id",
     *         in="query",
     *         description="Filter berdasarkan ID Peserta",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Tanggal awal (format: YYYY-MM-DD)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Tanggal akhir (format: YYYY-MM-DD)",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laporan kehadiran berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="summary",
     *                 type="object",
     *                 @OA\Property(property="total_sesi", type="integer", example=10),
     *                 @OA\Property(property="total_hadir", type="integer", example=8),
     *                 @OA\Property(property="total_terlambat", type="integer", example=1),
     *                 @OA\Property(property="total_izin", type="integer", example=0),
     *                 @OA\Property(property="total_sakit", type="integer", example=1),
     *                 @OA\Property(property="total_tidak_hadir", type="integer", example=0),
     *                 @OA\Property(property="persentase_kehadiran", type="number", format="float", example=90)
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", example="hadir")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function report(Request $request)
    {
        // Ambil semua sesi untuk kursus yang dipilih
        $sesiQuery = SesiKehadiran::query();
        if ($request->has('kursus_id')) {
            $sesiQuery->where('kursus_id', $request->kursus_id);
        }

        // Filter berdasarkan tanggal
        if ($request->has('start_date')) {
            $sesiQuery->where('tanggal', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $sesiQuery->where('tanggal', '<=', $request->end_date);
        }

        $sesiList = $sesiQuery->pluck('id')->toArray();

        // Query kehadiran
        $kehadiranQuery = Kehadiran::whereIn('sesi_id', $sesiList);

        if ($request->has('peserta_id')) {
            $kehadiranQuery->where('peserta_id', $request->peserta_id);
        }

        $kehadiranList = $kehadiranQuery->with(['sesi.kursus', 'peserta'])->get();

        // Hitung statistik
        $totalSesi = count($sesiList);
        $totalHadir = $kehadiranList->where('status', 'hadir')->count();
        $totalTerlambat = $kehadiranList->where('status', 'terlambat')->count();
        $totalIzin = $kehadiranList->where('status', 'izin')->count();
        $totalSakit = $kehadiranList->where('status', 'sakit')->count();
        $totalTidakHadir = $kehadiranList->where('status', 'tidak_hadir')->count();

        // Hitung persentase kehadiran
        $persentaseKehadiran = 0;
        if ($totalSesi > 0) {
            $persentaseKehadiran = (($totalHadir + $totalTerlambat) / $totalSesi) * 100;
        }

        return response()->json([
            'summary' => [
                'total_sesi' => $totalSesi,
                'total_hadir' => $totalHadir,
                'total_terlambat' => $totalTerlambat,
                'total_izin' => $totalIzin,
                'total_sakit' => $totalSakit,
                'total_tidak_hadir' => $totalTidakHadir,
                'persentase_kehadiran' => round($persentaseKehadiran, 2)
            ],
            'data' => KehadiranResource::collection($kehadiranList)
        ]);
    }
}
