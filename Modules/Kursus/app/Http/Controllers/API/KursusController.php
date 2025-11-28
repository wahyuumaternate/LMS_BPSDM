<?php

namespace Modules\Kursus\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Transformers\KursusResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Kursus",
 *     description="API endpoints for managing Kursus"
 * )
 */
class KursusController extends Controller
{
    /**
     * Get paginated list of Kursus
     * 
     * @OA\Get(
     *     path="/api/v1/kursus",
     *     tags={"Kursus"},
     *     summary="Get all Kursus",
     *     description="Returns paginated list of all Kursus with filtering options",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by course status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "aktif", "nonaktif", "selesai"})
     *     ),
     *     @OA\Parameter(
     *         name="jenis_kursus_id",
     *         in="query",
     *         description="Filter by jenis kursus ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="kategori_kursus_id",
     *         in="query",
     *         description="Filter by kategori kursus ID (parent of jenis kursus)",
     *         required=false,
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Filter by course level",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dasar", "menengah", "lanjut"})
     *     ),
     *     @OA\Parameter(
     *         name="tipe",
     *         in="query",
     *         description="Filter by course type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"daring", "luring", "hybrid"})
     *     ),
     *     @OA\Parameter(
     *         name="admin_instruktur_id",
     *         in="query",
     *         description="Filter by instructor ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in course title, code, or description",
     *         required=false,
     *         @OA\Schema(type="string", example="Data Science")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="jenis_kursus_id", type="integer", example=1),
     *                     @OA\Property(property="jenis_kursus", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="kode_jenis", type="string", example="PKA"),
     *                         @OA\Property(property="nama_jenis", type="string", example="Pelatihan Kepemimpinan Administrator"),
     *                         @OA\Property(property="slug", type="string", example="pelatihan-kepemimpinan-administrator")
     *                     ),
     *                     @OA\Property(property="kategori_id", type="integer", example=1),
     *                     @OA\Property(property="kategori", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_kategori", type="string", example="Kepemimpinan"),
     *                         @OA\Property(property="slug", type="string", example="kepemimpinan")
     *                     ),
     *                     @OA\Property(property="kode_kursus", type="string", example="K001"),
     *                     @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                     @OA\Property(property="level", type="string", example="dasar"),
     *                     @OA\Property(property="tipe", type="string", example="daring"),
     *                     @OA\Property(property="status", type="string", example="aktif")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching Kursus")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $perPage = max(5, min(100, (int)$perPage));

            // Load relasi dengan nested relationship
            $query = Kursus::with([
                'jenisKursus',
                'jenisKursus.kategoriKursus',
                'adminInstruktur'
            ]);

            // Filter by status - HANYA jika ada parameter
            if ($request->has('status') && in_array($request->status, ['draft', 'aktif', 'nonaktif', 'selesai'])) {
                $query->where('status', $request->status);
            }
            // Jika tidak ada parameter status, tampilkan semua

            // Filter by jenis_kursus_id (direct)
            if ($request->has('jenis_kursus_id')) {
                $query->where('jenis_kursus_id', $request->jenis_kursus_id);
            }

            // Filter by kategori_kursus_id (through relationship)
            if ($request->has('kategori_kursus_id')) {
                $query->whereHas('jenisKursus', function ($q) use ($request) {
                    $q->where('kategori_kursus_id', $request->kategori_kursus_id);
                });
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

            // Filter by search term
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('judul', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('kode_kursus', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('deskripsi', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Sort by date
            $query->orderBy('tanggal_mulai_kursus', 'asc');

            $kursus = $query->paginate($perPage);

            return KursusResource::collection($kursus);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching Kursus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/api/v1/kursus/{id}",
     *     tags={"Kursus"},
     *     summary="Get specific Kursus by ID",
     *     description="Returns detailed information about a specific kursus including jenis kursus and kategori",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Kursus ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *                 @OA\Property(property="instruktur", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_lengkap", type="string", example="Dr. John Doe"),
     *                     @OA\Property(property="nama_dengan_gelar", type="string", example="Dr. John Doe, M.Sc.")
     *                 ),
     *                 @OA\Property(property="jenis_kursus_id", type="integer", example=1),
     *                 @OA\Property(property="jenis_kursus", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kode_jenis", type="string", example="PKA"),
     *                     @OA\Property(property="nama_jenis", type="string", example="Pelatihan Kepemimpinan Administrator"),
     *                     @OA\Property(property="slug", type="string", example="pelatihan-kepemimpinan-administrator")
     *                 ),
     *                 @OA\Property(property="kategori_id", type="integer", example=1),
     *                 @OA\Property(property="kategori", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Kepemimpinan"),
     *                     @OA\Property(property="slug", type="string", example="kepemimpinan")
     *                 ),
     *                 @OA\Property(property="kode_kursus", type="string", example="K001"),
     *                 @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science"),
     *                 @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science"),
     *                 @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT"),
     *                 @OA\Property(property="durasi_jam", type="integer", example=40),
     *                 @OA\Property(property="tanggal_buka_pendaftaran", type="string", example="2025-11-01 09:00:00"),
     *                 @OA\Property(property="tanggal_tutup_pendaftaran", type="string", example="2025-11-15 09:00:00"),
     *                 @OA\Property(property="tanggal_mulai_kursus", type="string", example="2025-11-20 09:00:00"),
     *                 @OA\Property(property="tanggal_selesai_kursus", type="string", example="2025-12-20 09:00:00"),
     *                 @OA\Property(property="kuota_peserta", type="integer", example=30),
     *                 @OA\Property(property="level", type="string", example="dasar"),
     *                 @OA\Property(property="tipe", type="string", example="daring"),
     *                 @OA\Property(property="status", type="string", example="aktif"),
     *                 @OA\Property(property="thumbnail", type="string", example="http://localhost/storage/kursus/thumbnail/pengantar-data-science-1698304599.jpg"),
     *                 @OA\Property(property="passing_grade", type="string", example="70.00"),
     *                 @OA\Property(property="is_pendaftaran_open", type="boolean", example=true),
     *                 @OA\Property(property="jumlah_peserta", type="integer", example=25),
     *                 @OA\Property(property="prasyarats", type="array", 
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="kursus_id", type="integer", example=1),
     *                         @OA\Property(property="kursus_prasyarat_id", type="integer", example=2),
     *                         @OA\Property(property="kursusPrasyarat", type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="judul", type="string", example="Dasar Statistik"),
     *                             @OA\Property(property="kode_kursus", type="string", example="K002")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error retrieving course")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            // Load semua relasi yang diperlukan
            $kursus = Kursus::with([
                'jenisKursus',
                'jenisKursus.kategoriKursus',
                'adminInstruktur',
                'prasyarats.kursusPrasyarat'
            ])->find($id);

            if (!$kursus) {
                return response()->json([
                    'message' => 'Course not found'
                ], 404);
            }

            return new KursusResource($kursus);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving course: ' . $e->getMessage()
            ], 500);
        }
    }
}