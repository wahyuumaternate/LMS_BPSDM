<?php

namespace Modules\JadwalKegiatan\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\JadwalKegiatan\Entities\JadwalKegiatan;
use Modules\JadwalKegiatan\Transformers\JadwalKegiatanResource;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="JadwalKegiatan",
 *     description="API Endpoints untuk manajemen Jadwal Kegiatan"
 * )
 */
class JadwalKegiatanController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/jadwal-kegiatan",
     *     summary="Mendapatkan daftar jadwal kegiatan",
     *     tags={"JadwalKegiatan"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter berdasarkan ID Kursus",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tipe",
     *         in="query",
     *         description="Filter berdasarkan tipe kegiatan",
     *         required=false,
     *         @OA\Schema(type="string", enum={"online", "offline", "hybrid"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status kegiatan",
     *         required=false,
     *         @OA\Schema(type="string", enum={"upcoming", "ongoing", "past"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Pencarian berdasarkan nama kegiatan",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar jadwal kegiatan berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="nama_kegiatan", type="string", example="Workshop Pengelolaan Keuangan"),
     *                     @OA\Property(property="waktu_mulai_kegiatan", type="string", format="date-time", example="2025-10-30T09:00:00.000000Z"),
     *                     @OA\Property(property="waktu_selesai_kegiatan", type="string", format="date-time", example="2025-10-30T11:00:00.000000Z"),
     *                     @OA\Property(property="lokasi", type="string", example="Ruang 101"),
     *                     @OA\Property(property="tipe", type="string", example="offline"),
     *                     @OA\Property(property="tipe_text", type="string", example="Offline"),
     *                     @OA\Property(property="link_meeting", type="string", example=null),
     *                     @OA\Property(property="keterangan", type="string", example="Bawa laptop masing-masing"),
     *                     @OA\Property(property="durasi_menit", type="integer", example=120)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object"
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = JadwalKegiatan::with('kursus');

        // Filter berdasarkan kursus_id
        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        // Filter berdasarkan tipe
        if ($request->has('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter berdasarkan status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'ongoing':
                    $query->ongoing();
                    break;
                case 'past':
                    $query->past();
                    break;
            }
        }

        // Pencarian berdasarkan nama kegiatan
        if ($request->has('search')) {
            $query->where('nama_kegiatan', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan range waktu
        if ($request->has('start_date')) {
            $query->where('waktu_mulai_kegiatan', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('waktu_selesai_kegiatan', '<=', $request->end_date);
        }

        // Urutkan data
        $sortField = $request->input('sort_by', 'waktu_mulai_kegiatan');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $jadwalList = $query->paginate($perPage);

        return JadwalKegiatanResource::collection($jadwalList);
    }

    

    /**
     * @OA\Get(
     *     path="/api/v1/jadwal-kegiatan/{id}",
     *     summary="Mendapatkan detail jadwal kegiatan",
     *     tags={"JadwalKegiatan"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Jadwal Kegiatan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail jadwal kegiatan berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="kursus", type="object", 
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah")
     *                 ),
     *                 @OA\Property(property="nama_kegiatan", type="string", example="Workshop Pengelolaan Keuangan"),
     *                 @OA\Property(property="waktu_mulai_kegiatan", type="string", format="date-time", example="2025-10-30T09:00:00.000000Z"),
     *                 @OA\Property(property="waktu_selesai_kegiatan", type="string", format="date-time", example="2025-10-30T11:00:00.000000Z"),
     *                 @OA\Property(property="lokasi", type="string", example="Ruang 101"),
     *                 @OA\Property(property="tipe", type="string", example="offline"),
     *                 @OA\Property(property="tipe_text", type="string", example="Offline"),
     *                 @OA\Property(property="link_meeting", type="string", example=null),
     *                 @OA\Property(property="keterangan", type="string", example="Bawa laptop masing-masing"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=120)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Jadwal kegiatan tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $jadwal = JadwalKegiatan::with('kursus')->findOrFail($id);
        return new JadwalKegiatanResource($jadwal);
    }

  
    /**
     * @OA\Get(
     *     path="/api/v1/kursus/{kursus_id}/jadwal-kegiatan",
     *     summary="Mendapatkan daftar jadwal kegiatan berdasarkan kursus",
     *     tags={"JadwalKegiatan"},
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
     *         description="Filter berdasarkan status kegiatan",
     *         required=false,
     *         @OA\Schema(type="string", enum={"upcoming", "ongoing", "past"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar jadwal kegiatan untuk kursus tertentu berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kegiatan", type="string", example="Workshop Pengelolaan Keuangan"),
     *                     @OA\Property(property="waktu_mulai_kegiatan", type="string", format="date-time"),
     *                     @OA\Property(property="waktu_selesai_kegiatan", type="string", format="date-time"),
     *                     @OA\Property(property="tipe", type="string", example="offline")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="kursus",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah"),
     *                 @OA\Property(property="total_kegiatan", type="integer", example=5),
     *                 @OA\Property(property="upcoming_kegiatan", type="integer", example=2),
     *                 @OA\Property(property="past_kegiatan", type="integer", example=3)
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

        // Query jadwal
        $query = JadwalKegiatan::where('kursus_id', $kursusId);

        // Filter berdasarkan status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'ongoing':
                    $query->ongoing();
                    break;
                case 'past':
                    $query->past();
                    break;
            }
        }

        // Filter berdasarkan tipe
        if ($request->has('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Urutkan berdasarkan waktu mulai
        $query->orderBy('waktu_mulai_kegiatan', 'asc');

        // Ambil data
        $jadwalList = $query->get();

        // Hitung statistik
        $totalKegiatan = JadwalKegiatan::where('kursus_id', $kursusId)->count();
        $upcomingKegiatan = JadwalKegiatan::where('kursus_id', $kursusId)->upcoming()->count();
        $pastKegiatan = JadwalKegiatan::where('kursus_id', $kursusId)->past()->count();

        // Siapkan response
        $response = [
            'data' => JadwalKegiatanResource::collection($jadwalList),
            'kursus' => [
                'id' => $kursus->id,
                'judul' => $kursus->judul,
                'total_kegiatan' => $totalKegiatan,
                'upcoming_kegiatan' => $upcomingKegiatan,
                'past_kegiatan' => $pastKegiatan
            ]
        ];

        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jadwal-kegiatan/upcoming",
     *     summary="Mendapatkan daftar jadwal kegiatan yang akan datang",
     *     tags={"JadwalKegiatan"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah maksimum data yang dikembalikan",
     *         required=false,
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar jadwal kegiatan yang akan datang berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kegiatan", type="string", example="Workshop Pengelolaan Keuangan"),
     *                     @OA\Property(property="waktu_mulai_kegiatan", type="string", format="date-time"),
     *                     @OA\Property(property="kursus", type="object")
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
    public function getUpcoming(Request $request)
    {
        $limit = $request->input('limit', 5);
        $jadwalList = JadwalKegiatan::with('kursus')
            ->upcoming()
            ->orderBy('waktu_mulai_kegiatan', 'asc')
            ->take($limit)
            ->get();

        return JadwalKegiatanResource::collection($jadwalList);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jadwal-kegiatan/today",
     *     summary="Mendapatkan daftar jadwal kegiatan hari ini",
     *     tags={"JadwalKegiatan"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar jadwal kegiatan hari ini berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kegiatan", type="string", example="Workshop Pengelolaan Keuangan"),
     *                     @OA\Property(property="waktu_mulai_kegiatan", type="string", format="date-time"),
     *                     @OA\Property(property="waktu_selesai_kegiatan", type="string", format="date-time"),
     *                     @OA\Property(property="kursus", type="object")
     *                 )
     *             ),
     *             @OA\Property(property="date", type="string", example="2025-10-29")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getToday()
    {
        $today = Carbon::today();

        $jadwalList = JadwalKegiatan::with('kursus')
            ->whereDate('waktu_mulai_kegiatan', $today)
            ->orderBy('waktu_mulai_kegiatan', 'asc')
            ->get();

        return response()->json([
            'data' => JadwalKegiatanResource::collection($jadwalList),
            'date' => $today->toDateString()
        ]);
    }
}
