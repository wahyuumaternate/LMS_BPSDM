<?php

namespace Modules\SesiKehadiran\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SesiKehadiran\Entities\SesiKehadiran;
use Modules\SesiKehadiran\Transformers\SesiKehadiranResource;
use Modules\SesiKehadiran\Services\QRCodeService;
use Illuminate\Support\Facades\Validator;
use Modules\Kursus\Entities\Kursus;

/**
 * @OA\Tag(
 *     name="SesiKehadiran",
 *     description="API Endpoints untuk manajemen Sesi Kehadiran"
 * )
 */

class SesiKehadiranController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sesi-kehadiran",
     *     summary="Mendapatkan daftar sesi kehadiran",
     *     tags={"SesiKehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar sesi kehadiran berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kursus_id", type="integer", example=3),
     *                     @OA\Property(property="pertemuan_ke", type="integer", example=2),
     *                     @OA\Property(property="tanggal", type="string", example="2025-10-30"),
     *                     @OA\Property(property="status", type="string", example="scheduled")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = SesiKehadiran::with('kursus');

        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sesiList = $query->orderBy('tanggal', 'desc')
            ->orderBy('waktu_mulai', 'asc')
            ->paginate(10);

        return SesiKehadiranResource::collection($sesiList);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sesi-kehadiran",
     *     summary="Membuat sesi kehadiran baru",
     *     tags={"SesiKehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kursus_id","pertemuan_ke","tanggal","waktu_mulai","waktu_selesai"},
     *             @OA\Property(property="kursus_id", type="integer", example=1),
     *             @OA\Property(property="pertemuan_ke", type="integer", example=1),
     *             @OA\Property(property="tanggal", type="string", format="date", example="2025-10-30"),
     *             @OA\Property(property="waktu_mulai", type="string", format="time", example="09:00"),
     *             @OA\Property(property="waktu_selesai", type="string", format="time", example="11:00"),
     *             @OA\Property(property="durasi_berlaku_menit", type="integer", example=30),
     *             @OA\Property(property="status", type="string", example="scheduled", enum={"scheduled", "ongoing", "completed", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sesi kehadiran berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesi kehadiran created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="pertemuan_ke", type="integer", example=1),
     *                 @OA\Property(property="tanggal", type="string", example="2025-10-30"),
     *                 @OA\Property(property="status", type="string", example="scheduled"),
     *                 @OA\Property(property="qr_code_checkin", type="string", example="sesi-1-checkin-abc123.png"),
     *                 @OA\Property(property="qr_code_checkout", type="string", example="sesi-1-checkout-xyz789.png")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'pertemuan_ke' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'durasi_berlaku_menit' => 'nullable|integer|min:5|max:120',
            'status' => 'nullable|in:scheduled,ongoing,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Simpan data sesi kehadiran tanpa QR code dulu
        $sesi = SesiKehadiran::create($request->all());

        // Generate QR code untuk check-in
        $checkinFilename = $this->qrCodeService->generateForSesi($sesi->id, 'checkin');

        // Generate QR code untuk check-out
        $checkoutFilename = $this->qrCodeService->generateForSesi($sesi->id, 'checkout');

        // Update sesi dengan nama file QR code
        $sesi->update([
            'qr_code_checkin' => $checkinFilename,
            'qr_code_checkout' => $checkoutFilename
        ]);

        return response()->json([
            'message' => 'Sesi kehadiran created successfully',
            'data' => new SesiKehadiranResource($sesi)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sesi-kehadiran/{id}",
     *     summary="Mendapatkan detail sesi kehadiran",
     *     tags={"SesiKehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Sesi Kehadiran",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail sesi kehadiran berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=3),
     *                 @OA\Property(property="pertemuan_ke", type="integer", example=2),
     *                 @OA\Property(property="tanggal", type="string", example="2025-10-30"),
     *                 @OA\Property(property="waktu_mulai", type="string", example="09:00"),
     *                 @OA\Property(property="waktu_selesai", type="string", example="11:00"),
     *                 @OA\Property(property="durasi_berlaku_menit", type="integer", example=30),
     *                 @OA\Property(property="status", type="string", example="scheduled"),
     *                 @OA\Property(property="qr_code_checkin", type="string", example="sesi-1-checkin-abc123.png"),
     *                 @OA\Property(property="qr_code_checkout", type="string", example="sesi-1-checkout-xyz789.png")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sesi kehadiran tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $sesi = SesiKehadiran::with(['kursus', 'kehadiran.peserta'])->findOrFail($id);
        return new SesiKehadiranResource($sesi);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/sesi-kehadiran/{id}",
     *     summary="Mengupdate sesi kehadiran",
     *     tags={"SesiKehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Sesi Kehadiran",
     *         required=true
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="kursus_id", type="integer", example=1),
     *             @OA\Property(property="pertemuan_ke", type="integer", example=1),
     *             @OA\Property(property="tanggal", type="string", format="date", example="2025-10-30"),
     *             @OA\Property(property="waktu_mulai", type="string", format="time", example="09:00"),
     *             @OA\Property(property="waktu_selesai", type="string", format="time", example="11:00"),
     *             @OA\Property(property="durasi_berlaku_menit", type="integer", example=30),
     *             @OA\Property(property="status", type="string", example="ongoing", enum={"scheduled", "ongoing", "completed", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sesi kehadiran berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesi kehadiran updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="ongoing")
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
     *         description="Sesi kehadiran tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $sesi = SesiKehadiran::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kursus_id' => 'sometimes|required|exists:kursus,id',
            'pertemuan_ke' => 'sometimes|required|integer|min:1',
            'tanggal' => 'sometimes|required|date',
            'waktu_mulai' => 'sometimes|required|date_format:H:i',
            'waktu_selesai' => 'sometimes|required|date_format:H:i|after:waktu_mulai',
            'durasi_berlaku_menit' => 'nullable|integer|min:5|max:120',
            'status' => 'nullable|in:scheduled,ongoing,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sesi->update($request->all());

        return response()->json([
            'message' => 'Sesi kehadiran updated successfully',
            'data' => new SesiKehadiranResource($sesi)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/sesi-kehadiran/{id}",
     *     summary="Menghapus sesi kehadiran",
     *     tags={"SesiKehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Sesi Kehadiran",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sesi kehadiran berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesi kehadiran deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sesi kehadiran tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $sesi = SesiKehadiran::findOrFail($id);
        $sesi->delete();

        return response()->json([
            'message' => 'Sesi kehadiran deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sesi-kehadiran/{id}/qrcode",
     *     summary="Mendapatkan URL QR code sesi kehadiran",
     *     tags={"SesiKehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Sesi Kehadiran",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code URL berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="checkin_url", type="string", example="http://localhost/storage/qrcodes/sesi-1-checkin-xyz.png"),
     *             @OA\Property(property="checkout_url", type="string", example="http://localhost/storage/qrcodes/sesi-1-checkout-xyz.png")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sesi kehadiran tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getQrCode($id)
    {
        $sesi = SesiKehadiran::findOrFail($id);

        return response()->json([
            'checkin_url' => $this->qrCodeService->getUrl($sesi->qr_code_checkin),
            'checkout_url' => $this->qrCodeService->getUrl($sesi->qr_code_checkout)
        ]);
    }


    /**
     * @OA\Get(
     *      path="/api/v1/kursus/{kursus_id}/sesi",
     *     summary="Mendapatkan daftar sesi kehadiran berdasarkan kursus",
     *     tags={"SesiKehadiran"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="path",
     *         description="ID Kursus",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"scheduled", "ongoing", "completed", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar sesi kehadiran untuk kursus tertentu berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data", 
     *                 type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="kursus",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah"),
     *                 @OA\Property(property="total_sesi", type="integer", example=12),
     *                 @OA\Property(property="sesi_completed", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kursus tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

    public function getByKursus($kursusId, Request $request)
    {

        // Verifikasi kursus
        $kursus = Kursus::findOrFail($kursusId);

        // Query sesi kehadiran
        $query = SesiKehadiran::where('kursus_id', $kursusId);

        // Filter berdasarkan status jika ada
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan rentang tanggal jika ada
        if ($request->has('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai')) {
            $query->where('tanggal', '<=', $request->tanggal_selesai);
        }

        // Urutkan berdasarkan pertemuan_ke (default) atau parameter sort
        $sortField = $request->input('sort_by', 'pertemuan_ke');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        // Ambil data dengan pagination
        $perPage = $request->input('per_page', 10);
        $sesiList = $query->paginate($perPage);

        // Hitung statistik
        $totalSesi = SesiKehadiran::where('kursus_id', $kursusId)->count();
        $completedSesi = SesiKehadiran::where('kursus_id', $kursusId)
            ->where('status', 'completed')
            ->count();

        // Siapkan response
        $response = [
            'data' => SesiKehadiranResource::collection($sesiList),
            'kursus' => [
                'id' => $kursus->id,
                'judul' => $kursus->judul,
                'total_sesi' => $totalSesi,
                'sesi_completed' => $completedSesi,
            ]
        ];

        return response()->json($response);
    }
}
