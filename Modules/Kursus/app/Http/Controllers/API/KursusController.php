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
 *     name="Courses",
 *     description="API endpoints for managing courses"
 * )
 */
class KursusController extends Controller
{
    /**
     * Get paginated list of courses
     * 
     * @OA\Get(
     *     path="/api/v1/kursus",
     *     tags={"Courses"},
     *     summary="Get all courses",
     *     description="Returns paginated list of all courses with filtering options",
     *     security={{"sanctum":{}}},
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
     *         name="kategori_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
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
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *                     @OA\Property(property="instruktur", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_lengkap", type="string", example="Dr. John Doe"),
     *                         @OA\Property(property="nama_dengan_gelar", type="string", example="Dr. John Doe, M.Sc.")
     *                     ),
     *                     @OA\Property(property="kategori_id", type="integer", example=1),
     *                     @OA\Property(property="kategori", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                         @OA\Property(property="slug", type="string", example="teknologi-informasi")
     *                     ),
     *                     @OA\Property(property="kode_kursus", type="string", example="K001"),
     *                     @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                     @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science"),
     *                     @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science"),
     *                     @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT"),
     *                     @OA\Property(property="durasi_jam", type="integer", example=40),
     *                     @OA\Property(property="tanggal_buka_pendaftaran", type="string", format="date", example="2025-11-01"),
     *                     @OA\Property(property="tanggal_tutup_pendaftaran", type="string", format="date", example="2025-11-15"),
     *                     @OA\Property(property="tanggal_mulai_kursus", type="string", format="date", example="2025-11-20"),
     *                     @OA\Property(property="tanggal_selesai_kursus", type="string", format="date", example="2025-12-20"),
     *                     @OA\Property(property="kuota_peserta", type="integer", example=30),
     *                     @OA\Property(property="level", type="string", example="dasar"),
     *                     @OA\Property(property="tipe", type="string", example="daring"),
     *                     @OA\Property(property="status", type="string", example="aktif"),
     *                     @OA\Property(property="thumbnail", type="string", example="http://localhost:8000/storage/kursus/thumbnail/pengantar-data-science-1698304599.jpg"),
     *                     @OA\Property(property="passing_grade", type="number", format="float", example=70),
     *                     @OA\Property(property="is_pendaftaran_open", type="boolean", example=true),
     *                     @OA\Property(property="jumlah_peserta", type="integer", example=25),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching courses")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $perPage = max(5, min(100, (int)$perPage));

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

            $kursus = $query->paginate($perPage);

            return KursusResource::collection($kursus);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching courses: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new course
     * 
     * @OA\Post(
     *     path="/api/v1/kursus",
     *     tags={"Courses"},
     *     summary="Create new course",
     *     description="Create a new course record",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"admin_instruktur_id","kategori_id","kode_kursus","judul","deskripsi","level","tipe","status"},
     *                 @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *                 @OA\Property(property="kategori_id", type="integer", example=1),
     *                 @OA\Property(property="kode_kursus", type="string", example="K001"),
     *                 @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science"),
     *                 @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science"),
     *                 @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT"),
     *                 @OA\Property(property="durasi_jam", type="integer", example=40),
     *                 @OA\Property(property="tanggal_buka_pendaftaran", type="string", format="date", example="2025-11-01"),
     *                 @OA\Property(property="tanggal_tutup_pendaftaran", type="string", format="date", example="2025-11-15"),
     *                 @OA\Property(property="tanggal_mulai_kursus", type="string", format="date", example="2025-11-20"),
     *                 @OA\Property(property="tanggal_selesai_kursus", type="string", format="date", example="2025-12-20"),
     *                 @OA\Property(property="kuota_peserta", type="integer", example=30),
     *                 @OA\Property(property="level", type="string", enum={"dasar", "menengah", "lanjut"}, example="dasar"),
     *                 @OA\Property(property="tipe", type="string", enum={"daring", "luring", "hybrid"}, example="daring"),
     *                 @OA\Property(property="status", type="string", enum={"draft", "aktif", "nonaktif", "selesai"}, example="draft"),
     *                 @OA\Property(property="thumbnail", type="file", format="binary"),
     *                 @OA\Property(property="passing_grade", type="number", format="float", example=70)
     *             )
     *         ),
     *         @OA\JsonContent(
     *             required={"admin_instruktur_id","kategori_id","kode_kursus","judul","deskripsi","level","tipe","status"},
     *             @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *             @OA\Property(property="kategori_id", type="integer", example=1),
     *             @OA\Property(property="kode_kursus", type="string", example="K001"),
     *             @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *             @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science"),
     *             @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science"),
     *             @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT"),
     *             @OA\Property(property="durasi_jam", type="integer", example=40),
     *             @OA\Property(property="tanggal_buka_pendaftaran", type="string", format="date", example="2025-11-01"),
     *             @OA\Property(property="tanggal_tutup_pendaftaran", type="string", format="date", example="2025-11-15"),
     *             @OA\Property(property="tanggal_mulai_kursus", type="string", format="date", example="2025-11-20"),
     *             @OA\Property(property="tanggal_selesai_kursus", type="string", format="date", example="2025-12-20"),
     *             @OA\Property(property="kuota_peserta", type="integer", example=30),
     *             @OA\Property(property="level", type="string", enum={"dasar", "menengah", "lanjut"}, example="dasar"),
     *             @OA\Property(property="tipe", type="string", enum={"daring", "luring", "hybrid"}, example="daring"),
     *             @OA\Property(property="status", type="string", enum={"draft", "aktif", "nonaktif", "selesai"}, example="draft"),
     *             @OA\Property(property="passing_grade", type="number", format="float", example=70)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kursus created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *                 @OA\Property(property="instruktur", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_lengkap", type="string", example="Dr. John Doe"),
     *                     @OA\Property(property="nama_dengan_gelar", type="string", example="Dr. John Doe, M.Sc.")
     *                 ),
     *                 @OA\Property(property="kategori_id", type="integer", example=1),
     *                 @OA\Property(property="kategori", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                     @OA\Property(property="slug", type="string", example="teknologi-informasi")
     *                 ),
     *                 @OA\Property(property="kode_kursus", type="string", example="K001"),
     *                 @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science"),
     *                 @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science"),
     *                 @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT"),
     *                 @OA\Property(property="durasi_jam", type="integer", example=40),
     *                 @OA\Property(property="tanggal_buka_pendaftaran", type="string", format="date", example="2025-11-01"),
     *                 @OA\Property(property="tanggal_tutup_pendaftaran", type="string", format="date", example="2025-11-15"),
     *                 @OA\Property(property="tanggal_mulai_kursus", type="string", format="date", example="2025-11-20"),
     *                 @OA\Property(property="tanggal_selesai_kursus", type="string", format="date", example="2025-12-20"),
     *                 @OA\Property(property="kuota_peserta", type="integer", example=30),
     *                 @OA\Property(property="level", type="string", example="dasar"),
     *                 @OA\Property(property="tipe", type="string", example="daring"),
     *                 @OA\Property(property="status", type="string", example="draft"),
     *                 @OA\Property(property="thumbnail", type="string", example="http://localhost:8000/storage/kursus/thumbnail/pengantar-data-science-1698304599.jpg"),
     *                 @OA\Property(property="passing_grade", type="number", format="float", example=70),
     *                 @OA\Property(property="is_pendaftaran_open", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
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
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating course")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Get input data - try JSON first, then fallback to request->all()
            $input = $request->json()->all();
            if (empty($input) && $request->isJson()) {
                $input = json_decode($request->getContent(), true);
            }
            if (empty($input)) {
                $input = $request->all();
            }

            // Validasi input
            $validator = Validator::make($input, [
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

            $data = $input;
            if (isset($data['thumbnail']) && !$request->hasFile('thumbnail')) {
                unset($data['thumbnail']);
            }

            // Upload thumbnail jika ada
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/kursus/thumbnail', $filename);
                $data['thumbnail'] = $filename;
            }

            $kursus = Kursus::create($data);
            $kursus->load(['kategori', 'adminInstruktur']);

            return response()->json([
                'message' => 'Kursus created successfully',
                'data' => new KursusResource($kursus)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific course by ID
     * 
     * @OA\Get(
     *     path="/api/v1/kursus/{id}",
     *     tags={"Courses"},
     *     summary="Get course by ID",
     *     description="Returns specific course details with relationships",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *                 @OA\Property(property="instruktur", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_lengkap", type="string", example="Dr. John Doe"),
     *                     @OA\Property(property="nama_dengan_gelar", type="string", example="Dr. John Doe, M.Sc.")
     *                 ),
     *                 @OA\Property(property="kategori_id", type="integer", example=1),
     *                 @OA\Property(property="kategori", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                     @OA\Property(property="slug", type="string", example="teknologi-informasi")
     *                 ),
     *                 @OA\Property(property="kode_kursus", type="string", example="K001"),
     *                 @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science"),
     *                 @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science"),
     *                 @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT"),
     *                 @OA\Property(property="durasi_jam", type="integer", example=40),
     *                 @OA\Property(property="tanggal_buka_pendaftaran", type="string", format="date", example="2025-11-01"),
     *                 @OA\Property(property="tanggal_tutup_pendaftaran", type="string", format="date", example="2025-11-15"),
     *                 @OA\Property(property="tanggal_mulai_kursus", type="string", format="date", example="2025-11-20"),
     *                 @OA\Property(property="tanggal_selesai_kursus", type="string", format="date", example="2025-12-20"),
     *                 @OA\Property(property="kuota_peserta", type="integer", example=30),
     *                 @OA\Property(property="level", type="string", example="dasar"),
     *                 @OA\Property(property="tipe", type="string", example="daring"),
     *                 @OA\Property(property="status", type="string", example="aktif"),
     *                 @OA\Property(property="thumbnail", type="string", example="http://localhost:8000/storage/kursus/thumbnail/pengantar-data-science-1698304599.jpg"),
     *                 @OA\Property(property="passing_grade", type="number", format="float", example=70),
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
     *                             @OA\Property(property="judul", type="string", example="Dasar-dasar Pemrograman"),
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
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
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
            // Find course or return 404 if not found
            $kursus = Kursus::with(['kategori', 'adminInstruktur', 'prasyarats.kursusPrasyarat'])->find($id);

            if (!$kursus) {
                return response()->json(['message' => 'Course not found'], 404);
            }

            return new KursusResource($kursus);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving course: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update course
     * 
     * @OA\Put(
     *     path="/api/v1/kursus/{id}",
     *     tags={"Courses"},
     *     summary="Update course",
     *     description="Update an existing course",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *                 @OA\Property(property="kategori_id", type="integer", example=1),
     *                 @OA\Property(property="kode_kursus", type="string", example="K001-UPDATED"),
     *                 @OA\Property(property="judul", type="string", example="Pengantar Data Science - Updated"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science yang diperbarui"),
     *                 @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science dengan lebih mendalam"),
     *                 @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT atau Matematika"),
     *                 @OA\Property(property="durasi_jam", type="integer", example=45),
     *                 @OA\Property(property="tanggal_buka_pendaftaran", type="string", format="date", example="2025-11-05"),
     *                 @OA\Property(property="tanggal_tutup_pendaftaran", type="string", format="date", example="2025-11-20"),
     *                 @OA\Property(property="tanggal_mulai_kursus", type="string", format="date", example="2025-11-25"),
     *                 @OA\Property(property="tanggal_selesai_kursus", type="string", format="date", example="2025-12-25"),
     *                 @OA\Property(property="kuota_peserta", type="integer", example=35),
     *                 @OA\Property(property="level", type="string", enum={"dasar", "menengah", "lanjut"}, example="menengah"),
     *                 @OA\Property(property="tipe", type="string", enum={"daring", "luring", "hybrid"}, example="hybrid"),
     *                 @OA\Property(property="status", type="string", enum={"draft", "aktif", "nonaktif", "selesai"}, example="aktif"),
     *                 @OA\Property(property="thumbnail", type="file", format="binary"),
     *                 @OA\Property(property="passing_grade", type="number", format="float", example=75)
     *             )
     *         ),
     *         @OA\JsonContent(
     *             @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *             @OA\Property(property="kategori_id", type="integer", example=1),
     *             @OA\Property(property="kode_kursus", type="string", example="K001-UPDATED"),
     *             @OA\Property(property="judul", type="string", example="Pengantar Data Science - Updated"),
     *             @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science yang diperbarui"),
     *             @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science dengan lebih mendalam"),
     *             @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT atau Matematika"),
     *             @OA\Property(property="durasi_jam", type="integer", example=45),
     *             @OA\Property(property="tanggal_buka_pendaftaran", type="string", format="date", example="2025-11-05"),
     *             @OA\Property(property="tanggal_tutup_pendaftaran", type="string", format="date", example="2025-11-20"),
     *             @OA\Property(property="tanggal_mulai_kursus", type="string", format="date", example="2025-11-25"),
     *             @OA\Property(property="tanggal_selesai_kursus", type="string", format="date", example="2025-12-25"),
     *             @OA\Property(property="kuota_peserta", type="integer", example=35),
     *             @OA\Property(property="level", type="string", enum={"dasar", "menengah", "lanjut"}, example="menengah"),
     *             @OA\Property(property="tipe", type="string", enum={"daring", "luring", "hybrid"}, example="hybrid"),
     *             @OA\Property(property="status", type="string", enum={"draft", "aktif", "nonaktif", "selesai"}, example="aktif"),
     *             @OA\Property(property="passing_grade", type="number", format="float", example=75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kursus updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="admin_instruktur_id", type="integer", example=1),
     *                 @OA\Property(property="instruktur", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_lengkap", type="string", example="Dr. John Doe"),
     *                     @OA\Property(property="nama_dengan_gelar", type="string", example="Dr. John Doe, M.Sc.")
     *                 ),
     *                 @OA\Property(property="kategori_id", type="integer", example=1),
     *                 @OA\Property(property="kategori", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                     @OA\Property(property="slug", type="string", example="teknologi-informasi")
     *                 ),
     *                 @OA\Property(property="kode_kursus", type="string", example="K001-UPDATED"),
     *                 @OA\Property(property="judul", type="string", example="Pengantar Data Science - Updated"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kursus pengantar untuk data science yang diperbarui"),
     *                 @OA\Property(property="tujuan_pembelajaran", type="string", example="Memahami dasar-dasar data science dengan lebih mendalam"),
     *                 @OA\Property(property="sasaran_peserta", type="string", example="PNS dengan latar belakang IT atau Matematika"),
     *                 @OA\Property(property="durasi_jam", type="integer", example=45),
     *                 @OA\Property(property="tanggal_buka_pendaftaran", type="string", format="date", example="2025-11-05"),
     *                 @OA\Property(property="tanggal_tutup_pendaftaran", type="string", format="date", example="2025-11-20"),
     *                 @OA\Property(property="tanggal_mulai_kursus", type="string", format="date", example="2025-11-25"),
     *                 @OA\Property(property="tanggal_selesai_kursus", type="string", format="date", example="2025-12-25"),
     *                 @OA\Property(property="kuota_peserta", type="integer", example=35),
     *                 @OA\Property(property="level", type="string", example="menengah"),
     *                 @OA\Property(property="tipe", type="string", example="hybrid"),
     *                 @OA\Property(property="status", type="string", example="aktif"),
     *                 @OA\Property(property="thumbnail", type="string", example="http://localhost:8000/storage/kursus/thumbnail/pengantar-data-science-updated-1698304799.jpg"),
     *                 @OA\Property(property="passing_grade", type="number", format="float", example=75),
     *                 @OA\Property(property="is_pendaftaran_open", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 07:15:22")
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
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating course")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            // Find course or return 404 if not found
            $kursus = Kursus::find($id);

            if (!$kursus) {
                return response()->json(['message' => 'Course not found'], 404);
            }

            // Get input data - try JSON first, then fallback to request->all()
            $input = $request->json()->all();
            if (empty($input) && $request->isJson()) {
                $input = json_decode($request->getContent(), true);
            }
            if (empty($input)) {
                $input = $request->all();
            }

            $validator = Validator::make($input, [
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

            $data = $input;
            if (isset($data['thumbnail']) && !$request->hasFile('thumbnail')) {
                unset($data['thumbnail']);
            }

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
            $kursus->load(['kategori', 'adminInstruktur']);

            return response()->json([
                'message' => 'Kursus updated successfully',
                'data' => new KursusResource($kursus)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete course
     * 
     * @OA\Delete(
     *     path="/api/v1/kursus/{id}",
     *     tags={"Courses"},
     *     summary="Delete course",
     *     description="Delete a course if it has no related enrollments",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kursus deleted successfully")
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
     *         response=422,
     *         description="Cannot delete course with enrollments",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete kursus. It has related pendaftaran.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error deleting course")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            // Find course or return 404 if not found
            $kursus = Kursus::find($id);

            if (!$kursus) {
                return response()->json(['message' => 'Course not found'], 404);
            }

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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting course',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
