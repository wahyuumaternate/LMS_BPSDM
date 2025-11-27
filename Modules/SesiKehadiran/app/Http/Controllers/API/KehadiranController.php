<?php

namespace Modules\SesiKehadiran\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SesiKehadiran\Entities\Kehadiran;
use Modules\SesiKehadiran\Entities\SesiKehadiran;
use Modules\SesiKehadiran\Transformers\KehadiranResource;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Kehadiran Peserta",
 *     description="API Endpoints untuk peserta mencatat dan melihat data kehadiran mereka"
 * )
 */
class KehadiranController extends Controller
{
    /**
     * Constructor - Pastikan hanya peserta yang bisa akses
     */
    public function __construct()
    {
        $this->middleware('auth:peserta');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/kehadiran",
     *     summary="Mendapatkan daftar kehadiran peserta yang login",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Filter berdasarkan ID Kursus"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"hadir", "terlambat", "izin", "sakit", "tidak_hadir"}),
     *         description="Filter berdasarkan status kehadiran"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kehadiran berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="sesi_id", type="integer", example=2),
     *                     @OA\Property(property="pertemuan_ke", type="integer", example=1),
     *                     @OA\Property(property="tanggal", type="string", format="date", example="2025-01-15"),
     *                     @OA\Property(property="waktu_mulai", type="string", example="08:00"),
     *                     @OA\Property(property="waktu_selesai", type="string", example="10:00"),
     *                     @OA\Property(property="waktu_checkin", type="string", format="date-time", example="2025-01-15T08:00:00Z", nullable=true),
     *                     @OA\Property(property="waktu_checkout", type="string", format="date-time", example="2025-01-15T10:00:00Z", nullable=true),
     *                     @OA\Property(property="status", type="string", example="hadir"),
     *                     @OA\Property(property="durasi_menit", type="integer", example=120, nullable=true),
     *                     @OA\Property(property="keterangan", type="string", example="Hadir tepat waktu", nullable=true),
     *                     @OA\Property(property="kursus", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama", type="string", example="Kursus Laravel")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $user = auth('peserta')->user();

        $query = Kehadiran::with(['sesi.kursus'])
            ->where('peserta_id', $user->id);

        // Filter berdasarkan kursus
        if ($request->has('kursus_id')) {
            $query->whereHas('sesi', function ($q) use ($request) {
                $q->where('kursus_id', $request->kursus_id);
            });
        }

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $kehadiranList = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $kehadiranList->getCollection()->map(function ($kehadiran) {
                return $this->formatKehadiran($kehadiran);
            }),
            'meta' => [
                'current_page' => $kehadiranList->currentPage(),
                'last_page' => $kehadiranList->lastPage(),
                'per_page' => $kehadiranList->perPage(),
                'total' => $kehadiranList->total(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/kehadiran/{id}",
     *     summary="Mendapatkan detail kehadiran",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Kehadiran"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail kehadiran berhasil diambil"
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Tidak dapat mengakses data kehadiran peserta lain"),
     *     @OA\Response(response=404, description="Kehadiran tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show($id)
    {
        $user = auth('peserta')->user();

        $kehadiran = Kehadiran::with(['sesi.kursus'])->findOrFail($id);

        // Pastikan peserta hanya bisa melihat kehadiran miliknya sendiri
        if ($kehadiran->peserta_id !== $user->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat data kehadiran ini'
            ], 403);
        }

        return response()->json([
            'data' => $this->formatKehadiran($kehadiran)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/student/kehadiran/checkin",
     *     summary="Peserta melakukan check-in kehadiran",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sesi_id"},
     *             @OA\Property(property="sesi_id", type="integer", example=1, description="ID Sesi Kehadiran"),
     *             @OA\Property(property="lokasi_checkin", type="string", example="Ruang 101", description="Lokasi check-in (opsional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check-in berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Check-in berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="sesi_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="hadir"),
     *                 @OA\Property(property="waktu_checkin", type="string", format="date-time", example="2025-01-15T08:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Sesi tidak aktif atau di luar waktu yang ditentukan"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error atau sudah check-in"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function checkin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sesi_id' => 'required|exists:sesi_kehadiran,id',
            'lokasi_checkin' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth('peserta')->user();
        $sesi = SesiKehadiran::findOrFail($request->sesi_id);
        $now = $now = Carbon::now('Asia/Jayapura');

        // Validasi waktu sesi
        $validation = $this->validateSesiTime($sesi, $now);
        if (!$validation['valid']) {
            return response()->json([
                'message' => $validation['message']
            ], 400);
        }

        // Cek apakah sesi aktif (ongoing)
        if ($sesi->status !== 'ongoing') {
            return response()->json([
                'message' => 'Sesi kehadiran belum dimulai atau sudah selesai'
            ], 400);
        }

        // Cek apakah sudah ada data kehadiran
        $kehadiran = Kehadiran::where('sesi_id', $request->sesi_id)
            ->where('peserta_id', $user->id)
            ->first();

        // Tentukan status berdasarkan waktu check-in
        $status = $this->determineStatus($sesi, $now);

        if ($kehadiran) {
            // Jika sudah ada dan sudah check-in
            if ($kehadiran->waktu_checkin) {
                return response()->json([
                    'message' => 'Anda sudah melakukan check-in untuk sesi ini',
                    'data' => $this->formatKehadiran($kehadiran->load('sesi.kursus'))
                ], 422);
            }

            // Update data kehadiran yang sudah ada
            $kehadiran->update([
                'waktu_checkin' => $now,
                'lokasi_checkin' => $request->lokasi_checkin,
                'status' => $status,
            ]);

            $message = 'Check-in berhasil';
        } else {
            // Buat data kehadiran baru
            $kehadiran = Kehadiran::create([
                'sesi_id' => $request->sesi_id,
                'peserta_id' => $user->id,
                'waktu_checkin' => $now,
                'lokasi_checkin' => $request->lokasi_checkin,
                'status' => $status,
            ]);

            $message = 'Check-in berhasil';
        }

        // Tambahkan info status
        if ($status === 'terlambat') {
            $message .= ' (Terlambat)';
        }

        return response()->json([
            'message' => $message,
            'data' => $this->formatKehadiran($kehadiran->load('sesi.kursus'))
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/student/kehadiran/izin",
     *     summary="Peserta mengajukan izin atau sakit",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sesi_id", "status", "keterangan"},
     *             @OA\Property(property="sesi_id", type="integer", example=1, description="ID Sesi Kehadiran"),
     *             @OA\Property(property="status", type="string", enum={"izin", "sakit"}, example="izin", description="Status kehadiran"),
     *             @OA\Property(property="keterangan", type="string", example="Ada keperluan keluarga", description="Alasan izin/sakit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Izin/sakit berhasil diajukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Izin berhasil diajukan"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="sesi_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="izin"),
     *                 @OA\Property(property="keterangan", type="string", example="Ada keperluan keluarga")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error atau sudah ada kehadiran"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function submitIzin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sesi_id' => 'required|exists:sesi_kehadiran,id',
            'status' => 'required|in:izin,sakit',
            'keterangan' => 'required|string|max:500',
        ], [
            'sesi_id.required' => 'Sesi kehadiran wajib dipilih',
            'sesi_id.exists' => 'Sesi kehadiran tidak ditemukan',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status harus izin atau sakit',
            'keterangan.required' => 'Keterangan wajib diisi',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth('peserta')->user();
        $sesi = SesiKehadiran::findOrFail($request->sesi_id);

        // Cek apakah sudah ada data kehadiran
        $kehadiran = Kehadiran::where('sesi_id', $request->sesi_id)
            ->where('peserta_id', $user->id)
            ->first();

        if ($kehadiran) {
            // Jika sudah check-in, tidak bisa ajukan izin/sakit
            if ($kehadiran->waktu_checkin) {
                return response()->json([
                    'message' => 'Anda sudah melakukan check-in, tidak dapat mengajukan izin/sakit',
                    'data' => $this->formatKehadiran($kehadiran->load('sesi.kursus'))
                ], 422);
            }

            // Jika sudah ada status izin/sakit, update saja
            if (in_array($kehadiran->status, ['izin', 'sakit'])) {
                $kehadiran->update([
                    'status' => $request->status,
                    'keterangan' => $request->keterangan,
                ]);

                $message = ucfirst($request->status) . ' berhasil diperbarui';
            } else {
                // Update data kehadiran yang sudah ada
                $kehadiran->update([
                    'status' => $request->status,
                    'keterangan' => $request->keterangan,
                ]);

                $message = ucfirst($request->status) . ' berhasil diajukan';
            }
        } else {
            // Buat data kehadiran baru
            $kehadiran = Kehadiran::create([
                'sesi_id' => $request->sesi_id,
                'peserta_id' => $user->id,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            $message = ucfirst($request->status) . ' berhasil diajukan';
        }

        return response()->json([
            'message' => $message,
            'data' => $this->formatKehadiran($kehadiran->load('sesi.kursus'))
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/student/kehadiran/checkout",
     *     summary="Peserta melakukan check-out kehadiran",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sesi_id"},
     *             @OA\Property(property="sesi_id", type="integer", example=1, description="ID Sesi Kehadiran"),
     *             @OA\Property(property="lokasi_checkout", type="string", example="Ruang 101", description="Lokasi check-out (opsional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check-out berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Check-out berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="sesi_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="hadir"),
     *                 @OA\Property(property="waktu_checkin", type="string", format="date-time", example="2025-01-15T08:00:00Z"),
     *                 @OA\Property(property="waktu_checkout", type="string", format="date-time", example="2025-01-15T10:00:00Z"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=120)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Belum check-in atau sesi tidak aktif"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error atau sudah check-out"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
   public function checkout(Request $request)
{
   
    $validator = Validator::make($request->all(), [
        'sesi_id' => 'required|exists:sesi_kehadiran,id',
        'lokasi_checkout' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = auth('peserta')->user();
    $sesi = SesiKehadiran::findOrFail($request->sesi_id);
    $now = Carbon::now('Asia/Jayapura');

// Ambil sesi dari database
$sesi = SesiKehadiran::findOrFail($request->sesi_id);

// Parsing datetime langsung, tanpa gabungan string
$mulai = Carbon::parse($sesi->waktu_mulai)->timezone('Asia/Jayapura');
$selesai = Carbon::parse($sesi->waktu_selesai)->timezone('Asia/Jayapura')
              ->addMinutes($sesi->durasi_berlaku_menit ?? 0);

// Cek status sesi
if ($now->lt($mulai)) {
    return response()->json(['message' => 'Sesi kehadiran belum dibuka. Silakan coba lagi nanti.'], 400);
}

if ($now->gt($selesai)) {
    return response()->json(['message' => 'Sesi kehadiran sudah ditutup.'], 400);
}

// Ambil kehadiran peserta
$kehadiran = Kehadiran::where('sesi_id', $sesi->id)
    ->where('peserta_id', $user->id)
    ->first();

if (!$kehadiran || !$kehadiran->waktu_checkin) {
    return response()->json(['message' => 'Anda belum melakukan check-in untuk sesi ini'], 400);
}

if ($kehadiran->waktu_checkout) {
    return response()->json([
        'message' => 'Anda sudah melakukan check-out untuk sesi ini',
        'data' => $this->formatKehadiran($kehadiran->load('sesi.kursus'))
    ], 422);
}

// Hitung durasi
$checkinTime = Carbon::parse($kehadiran->waktu_checkin, 'Asia/Jayapura');
$durasiMenit = $now->diffInMinutes($checkinTime);

// Update checkout
$kehadiran->update([
    'waktu_checkout' => $now,
    'lokasi_checkout' => $request->lokasi_checkout,
    'durasi_menit' => $durasiMenit,
]);

return response()->json([
    'message' => 'Check-out berhasil',
    'data' => $this->formatKehadiran($kehadiran->load('sesi.kursus'))
]);

}




    /**
     * @OA\Get(
     *     path="/api/v1/student/kehadiran/sesi/{sesi_id}/status",
     *     summary="Cek status kehadiran peserta untuk sesi tertentu",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="sesi_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Sesi Kehadiran"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status kehadiran berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="sesi", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="pertemuan_ke", type="integer", example=1),
     *                 @OA\Property(property="tanggal", type="string", format="date", example="2025-01-15"),
     *                 @OA\Property(property="waktu_mulai", type="string", example="08:00"),
     *                 @OA\Property(property="waktu_selesai", type="string", example="10:00"),
     *                 @OA\Property(property="status", type="string", example="ongoing")
     *             ),
     *             @OA\Property(property="kehadiran", type="object", nullable=true,
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="hadir"),
     *                 @OA\Property(property="waktu_checkin", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="waktu_checkout", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="durasi_menit", type="integer", nullable=true)
     *             ),
     *             @OA\Property(property="can_checkin", type="boolean", example=true),
     *             @OA\Property(property="can_checkout", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sesi tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function checkStatus($sesiId)
    {
     
        $user = auth('peserta')->user();
        $sesi = SesiKehadiran::with('kursus')->findOrFail($sesiId);
        $now = $now = Carbon::now('Asia/Jayapura');

        // Cek kehadiran peserta
        $kehadiran = Kehadiran::where('sesi_id', $sesiId)
            ->where('peserta_id', $user->id)
            ->first();

        // Validasi waktu sesi
        $validation = $this->validateSesiTime($sesi, $now);

        // Tentukan apakah bisa check-in/check-out
        $canCheckin = $validation['valid'] && $sesi->status === 'ongoing' && 
                      (!$kehadiran || !$kehadiran->waktu_checkin);
        $canCheckout = $kehadiran && $kehadiran->waktu_checkin && !$kehadiran->waktu_checkout;

        return response()->json([
            'sesi' => [
                'id' => $sesi->id,
                'kursus_id' => $sesi->kursus_id,
                'kursus_nama' => $sesi->kursus->nama ?? null,
                'pertemuan_ke' => $sesi->pertemuan_ke,
                'tanggal' => Carbon::parse($sesi->tanggal)->setTimezone('Asia/Jayapura')->format('Y-m-d'),
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'status' => $sesi->status,
                'durasi_berlaku_menit' => $sesi->durasi_berlaku_menit,
            ],
            'kehadiran' => $kehadiran ? [
                'id' => $kehadiran->id,
                'status' => $kehadiran->status,
               'waktu_checkin' => Carbon::parse($kehadiran->waktu_checkin)->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s'),
                'waktu_checkout' => Carbon::parse($kehadiran->waktu_checkout)->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s'),
                'lokasi_checkin' => $kehadiran->lokasi_checkin,
                'lokasi_checkout' => $kehadiran->lokasi_checkout,
                'durasi_menit' => $kehadiran->durasi_menit,
                'keterangan' => $kehadiran->keterangan,
            ] : null,
            'can_checkin' => $canCheckin,
            'can_checkout' => $canCheckout,
            'validation_message' => !$validation['valid'] ? $validation['message'] : null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/kehadiran/kursus/{kursus_id}",
     *     summary="Mendapatkan daftar kehadiran peserta berdasarkan kursus",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Kursus"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kehadiran berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="kursus", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="Kursus Laravel")
     *             ),
     *             @OA\Property(property="summary", type="object",
     *                 @OA\Property(property="total_sesi", type="integer", example=10),
     *                 @OA\Property(property="total_hadir", type="integer", example=8),
     *                 @OA\Property(property="total_terlambat", type="integer", example=1),
     *                 @OA\Property(property="total_izin", type="integer", example=0),
     *                 @OA\Property(property="total_sakit", type="integer", example=1),
     *                 @OA\Property(property="total_tidak_hadir", type="integer", example=0),
     *                 @OA\Property(property="persentase_kehadiran", type="number", format="float", example=90.00)
     *             ),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Kursus tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getByKursus($kursusId)
    {
        $user = auth('peserta')->user();
        $kursus = Kursus::findOrFail($kursusId);

        // Ambil semua sesi untuk kursus
        $sesiIds = SesiKehadiran::where('kursus_id', $kursusId)->pluck('id');
        $totalSesi = $sesiIds->count();

        $kehadiranList = Kehadiran::with(['sesi'])
            ->whereIn('sesi_id', $sesiIds)
            ->where('peserta_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung statistik
        $summary = $this->calculateSummary($kehadiranList, $totalSesi);

        return response()->json([
            'kursus' => [
                'id' => $kursus->id,
                'nama' => $kursus->nama,
            ],
            'summary' => $summary,
            'data' => $kehadiranList->map(function ($kehadiran) {
                return $this->formatKehadiran($kehadiran);
            })
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/kehadiran/report",
     *     summary="Mendapatkan laporan/statistik kehadiran peserta",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Filter berdasarkan ID Kursus"
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *         description="Tanggal awal (format: YYYY-MM-DD)"
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *         description="Tanggal akhir (format: YYYY-MM-DD)"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laporan kehadiran berhasil diambil"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function report(Request $request)
    {
        $user = auth('peserta')->user();

        // Ambil semua sesi berdasarkan filter
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
        $totalSesi = count($sesiList);

        // Query kehadiran peserta
        $kehadiranList = Kehadiran::whereIn('sesi_id', $sesiList)
            ->where('peserta_id', $user->id)
            ->with(['sesi.kursus'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung statistik
        $summary = $this->calculateSummary($kehadiranList, $totalSesi);

        return response()->json([
            'summary' => $summary,
            'data' => $kehadiranList->map(function ($kehadiran) {
                return $this->formatKehadiran($kehadiran);
            })
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/kehadiran/available-sessions",
     *     summary="Mendapatkan daftar sesi yang tersedia untuk check-in",
     *     tags={"Kehadiran Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Filter berdasarkan ID Kursus"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar sesi yang tersedia",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kursus_nama", type="string", example="Kursus Laravel"),
     *                     @OA\Property(property="pertemuan_ke", type="integer", example=1),
     *                     @OA\Property(property="tanggal", type="string", format="date", example="2025-01-15"),
     *                     @OA\Property(property="waktu_mulai", type="string", example="08:00"),
     *                     @OA\Property(property="waktu_selesai", type="string", example="10:00"),
     *                     @OA\Property(property="status", type="string", example="ongoing"),
     *                     @OA\Property(property="sudah_checkin", type="boolean", example=false),
     *                     @OA\Property(property="sudah_checkout", type="boolean", example=false)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function availableSessions(Request $request)
    {
       
        $user = auth('peserta')->user();

        $query = SesiKehadiran::with('kursus')
            ->where('status', 'ongoing');

        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        $sessions = $query->orderBy('tanggal', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        return response()->json([
            'data' => $sessions->map(function ($sesi) use ($user) {
                $kehadiran = Kehadiran::where('sesi_id', $sesi->id)
                    ->where('peserta_id', $user->id)
                    ->first();

                return [
                    'id' => $sesi->id,
                    'kursus_id' => $sesi->kursus_id,
                    'kursus_nama' => $sesi->kursus->nama ?? null,
                    'pertemuan_ke' => $sesi->pertemuan_ke,

                    // FORMAT TANGGAL: 2025-10-25
                    'tanggal' => Carbon::parse($sesi->tanggal)->format('Y-m-d'),

                    // FORMAT WAKTU: 06:08:19
                    'waktu_mulai' => Carbon::parse($sesi->waktu_mulai)->format('H:i:s'),
                    'waktu_selesai' => Carbon::parse($sesi->waktu_selesai)->format('H:i:s'),

                    'durasi_berlaku_menit' => $sesi->durasi_berlaku_menit,
                    'status' => $sesi->status,
                    'sudah_checkin' => $kehadiran && $kehadiran->waktu_checkin ? true : false,
                    'sudah_checkout' => $kehadiran && $kehadiran->waktu_checkout ? true : false,
                ];

            })
        ]);
    }

    /**
     * Validasi waktu sesi
     */
    private function validateSesiTime(SesiKehadiran $sesi, Carbon $now): array
    {
        $tanggal = Carbon::parse($sesi->tanggal);
        $waktuMulai = Carbon::parse($sesi->waktu_mulai);
        $waktuSelesai = Carbon::parse($sesi->waktu_selesai);

      $timezone = 'Asia/Jayapura'; // WITA, ganti sesuai kebutuhan
$now = Carbon::now($timezone);
$sesiStart = Carbon::create($tanggal->year, $tanggal->month, $tanggal->day, $waktuMulai->hour, $waktuMulai->minute, 0, $timezone);


        $sesiEnd = Carbon::create(
            $tanggal->year,
            $tanggal->month,
            $tanggal->day,
            $waktuSelesai->hour,
            $waktuSelesai->minute,
            0
        )->addMinutes($sesi->durasi_berlaku_menit ?? 0);

        if ($now->lt($sesiStart)) {
            return [
                'valid' => false,
                'message' => 'Sesi kehadiran belum dibuka. Silakan coba lagi nanti.'
            ];
        }

        if ($now->gt($sesiEnd)) {
            return [
                'valid' => false,
                'message' => 'Sesi kehadiran sudah ditutup.'
            ];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Tentukan status berdasarkan waktu check-in
     */
    private function determineStatus(SesiKehadiran $sesi, Carbon $now): string
    {
        
        $tanggal = Carbon::parse($sesi->tanggal);
        $waktuMulai = Carbon::parse($sesi->waktu_mulai);

        $waktuTenggatHadir = Carbon::create(
            $tanggal->year,
            $tanggal->month,
            $tanggal->day,
            $waktuMulai->hour,
            $waktuMulai->minute,
            0
        )->addMinutes(15); // Terlambat jika >15 menit dari waktu mulai

        return $now->gt($waktuTenggatHadir) ? 'terlambat' : 'hadir';
    }

    /**
     * Format data kehadiran untuk response
     */
   private function formatKehadiran(Kehadiran $kehadiran): array
{
    return [
        'id' => $kehadiran->id,
        'sesi_id' => $kehadiran->sesi_id,
        'pertemuan_ke' => $kehadiran->sesi->pertemuan_ke ?? null,

        // FORMAT TANGGAL (Y-m-d)
        'tanggal' => $kehadiran->sesi->tanggal
            ? Carbon::parse($kehadiran->sesi->tanggal)->format('Y-m-d')
            : null,

        // FORMAT JAM (H:i:s)
        'waktu_mulai' => $kehadiran->sesi->waktu_mulai
            ? Carbon::parse($kehadiran->sesi->waktu_mulai)->format('H:i:s')
            : null,

        'waktu_selesai' => $kehadiran->sesi->waktu_selesai
            ? Carbon::parse($kehadiran->sesi->waktu_selesai)->format('H:i:s')
            : null,

        // Waktu Check-in & Checkout (H:i:s)
        'waktu_checkin' => $kehadiran->waktu_checkin
            ? Carbon::parse($kehadiran->waktu_checkin)->format('H:i:s')
            : null,

        'waktu_checkout' => $kehadiran->waktu_checkout
            ? Carbon::parse($kehadiran->waktu_checkout)->format('H:i:s')
            : null,

        'lokasi_checkin' => $kehadiran->lokasi_checkin,
        'lokasi_checkout' => $kehadiran->lokasi_checkout,

        'status' => $kehadiran->status,
        'durasi_menit' => $kehadiran->durasi_menit,
        'keterangan' => $kehadiran->keterangan,

        'kursus' => $kehadiran->sesi->kursus ? [
            'id' => $kehadiran->sesi->kursus->id,
            'nama' => $kehadiran->sesi->kursus->nama,
        ] : null,
    ];
}


    /**
     * Hitung summary kehadiran
     */
    private function calculateSummary($kehadiranList, int $totalSesi): array
    {
        $totalHadir = $kehadiranList->where('status', 'hadir')->count();
        $totalTerlambat = $kehadiranList->where('status', 'terlambat')->count();
        $totalIzin = $kehadiranList->where('status', 'izin')->count();
        $totalSakit = $kehadiranList->where('status', 'sakit')->count();
        $totalTidakHadir = $kehadiranList->where('status', 'tidak_hadir')->count();

        // Hitung persentase kehadiran (hadir + terlambat)
        $persentaseKehadiran = $totalSesi > 0 
            ? (($totalHadir + $totalTerlambat) / $totalSesi) * 100 
            : 0;

        return [
            'total_sesi' => $totalSesi,
            'total_hadir' => $totalHadir,
            'total_terlambat' => $totalTerlambat,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_tidak_hadir' => $totalTidakHadir,
            'persentase_kehadiran' => round($persentaseKehadiran, 2),
        ];
    }
}