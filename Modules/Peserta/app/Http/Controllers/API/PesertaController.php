<?php

namespace Modules\Peserta\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Peserta\Entities\Peserta;
use Modules\Peserta\Transformers\PesertaResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Kursus\Entities\PendaftaranKursus;
use Modules\Kursus\Transformers\KursusResource;

/**
 * @OA\Tag(
 *     name="Peserta",
 *     description="API endpoints untuk mengelola Peserta dan pendaftaran kursus mereka"
 * )
 */

class PesertaController extends Controller
{
    /**
     * Get paginated list of Peserta
     * 
     * @OA\Get(
     *     path="/api/v1/peserta",
     *     tags={"Peserta"},
     *     summary="Get all Peserta",
     *     description="Returns paginated list of all Peserta with their OPD",
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
     *         name="search",
     *         in="query",
     *         description="Search term to filter results (searches username, email, nama_lengkap and nip)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status_kepegawaian",
     *         in="query",
     *         description="Filter by employment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pns", "pppk", "kontrak"})
     *     ),
     *     @OA\Parameter(
     *         name="opd_id",
     *         in="query",
     *         description="Filter by OPD ID",
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
     *                     @OA\Property(property="opd_id", type="integer", example=1),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                     @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                     @OA\Property(property="pangkat_golongan", type="string", example="III/a"),
     *                     @OA\Property(property="jabatan", type="string", example="Staff"),
     *                     @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *                     @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *                     @OA\Property(property="jenis_kelamin", type="string", example="laki_laki"),
     *                     @OA\Property(property="pendidikan_terakhir", type="string", example="s1"),
     *                     @OA\Property(property="status_kepegawaian", type="string", example="pns"),
     *                     @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                     @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                     @OA\Property(property="foto_profil", type="string", example="1698304599.jpg"),
     *                     @OA\Property(property="opd", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan"),
     *                         @OA\Property(property="kode_opd", type="string", example="DISDIK"),
     *                         @OA\Property(property="alamat", type="string", example="Jl. Pendidikan No. 1")
     *                     ),
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
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            // Get pagination parameters
            $perPage = $request->input('per_page', 15);
            $perPage = max(5, min(100, (int)$perPage));

            // Build query
            $query = Peserta::with('opd');

            // Handle search
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            }

            // Filter by status_kepegawaian
            if ($request->has('status_kepegawaian') && in_array($request->status_kepegawaian, ['pns', 'pppk', 'kontrak'])) {
                $query->where('status_kepegawaian', $request->status_kepegawaian);
            }

            // Filter by OPD
            if ($request->has('opd_id') && is_numeric($request->opd_id)) {
                $query->where('opd_id', $request->opd_id);
            }

            $pesertas = $query->paginate($perPage);

            return PesertaResource::collection($pesertas);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching Peserta: ' . $e->getMessage()], 500);
        }
    }

 

    /**
     * Get specific Peserta by ID
     * 
     * @OA\Get(
     *     path="/api/v1/peserta/{id}",
     *     tags={"Peserta"},
     *     summary="Get Peserta by ID",
     *     description="Returns specific Peserta details with their OPD",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Peserta ID",
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
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="pangkat_golongan", type="string", example="III/a"),
     *                 @OA\Property(property="jabatan", type="string", example="Staff"),
     *                 @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *                 @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *                 @OA\Property(property="jenis_kelamin", type="string", example="laki_laki"),
     *                 @OA\Property(property="pendidikan_terakhir", type="string", example="s1"),
     *                 @OA\Property(property="status_kepegawaian", type="string", example="pns"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                 @OA\Property(property="foto_profil", type="string", example="1698304599.jpg"),
     *                 @OA\Property(property="opd", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan"),
     *                     @OA\Property(property="kode_opd", type="string", example="DISDIK"),
     *                     @OA\Property(property="alamat", type="string", example="Jl. Pendidikan No. 1")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Peserta not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Peserta not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            // Find Peserta or return 404 if not found
            $peserta = Peserta::with('opd')->find($id);

            if (!$peserta) {
                return response()->json(['message' => 'Peserta not found'], 404);
            }

            return new PesertaResource($peserta);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving Peserta: ' . $e->getMessage()], 500);
        }
    }

   

   /**
 * Get courses by Peserta ID
 * 
 * @OA\Get(
 *     path="/api/v1/peserta/{id}/kursus",
 *     tags={"Peserta"},
 *     summary="Get courses by Peserta ID",
 *     description="Returns courses that a participant is enrolled in with status information",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Peserta ID",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filter by enrollment status",
 *         required=false,
 *         @OA\Schema(type="string", enum={"pending", "disetujui", "ditolak", "aktif", "selesai", "batal"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", 
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
 *                     @OA\Property(property="kode_kursus", type="string", example="K001"),
 *                     @OA\Property(property="deskripsi", type="string", example="Pengenalan dasar tentang Data Science"),
 *                     @OA\Property(property="level", type="string", example="dasar"),
 *                     @OA\Property(property="tipe", type="string", example="daring"),
 *                     @OA\Property(property="durasi_jam", type="integer", example=20),
 *                     @OA\Property(property="tanggal_mulai", type="string", format="date", example="2025-11-01"),
 *                     @OA\Property(property="tanggal_selesai", type="string", format="date", example="2025-12-01"),
 *                     @OA\Property(property="pendaftaran_buka", type="string", format="date", example="2025-10-01"),
 *                     @OA\Property(property="pendaftaran_tutup", type="string", format="date", example="2025-10-25"),
 *                     @OA\Property(property="kuota_peserta", type="integer", example=30),
 *                     @OA\Property(property="status", type="string", example="aktif"),
 *                     @OA\Property(property="thumbnail", type="string", example="http://localhost:8000/storage/kursus/thumbnail/pengantar-data-science-1698304599.jpg"),
 *                     @OA\Property(property="kategori_id", type="integer", example=1),
 *                     @OA\Property(property="nama_kategori", type="string", example="Data Science"),
 *                     @OA\Property(property="enrollment", type="object",
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="tanggal_daftar", type="string", format="date", example="2025-11-01"),
 *                         @OA\Property(property="status", type="string", example="aktif"),
 *                         @OA\Property(property="tanggal_disetujui", type="string", format="date", example="2025-11-05"),
 *                         @OA\Property(property="tanggal_selesai", type="string", format="date", example=null),
 *                         @OA\Property(property="nilai_akhir", type="number", format="float", example=null),
 *                         @OA\Property(property="predikat", type="string", example=null)
 *                     ),
 *                     @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Peserta not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Peserta not found")
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
 *             @OA\Property(property="message", type="string", example="Error retrieving courses")
 *         )
 *     )
 * )
 */
public function getKursus(Request $request, $id)
{
    try {
        // Check if peserta exists
        $peserta = Peserta::find($id);
        if (!$peserta) {
            return response()->json(['message' => 'Peserta not found'], 404);
        }

        // Get enrollments for the peserta
        $query = PendaftaranKursus::where('peserta_id', $id);

        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'disetujui', 'ditolak', 'aktif', 'selesai', 'batal'])) {
            $query->where('status', $request->status);
        }

        // Get the enrollments with courses and kategori
        $enrollments = $query->with(['kursus.kategori'])->get();

        // Transform to required format
        $result = $enrollments->map(function ($enrollment) {
            $kursus = $enrollment->kursus;

            // Add enrollment info to the course
            $kursus->enrollment = [
                'id' => $enrollment->id,
                'tanggal_daftar' => $enrollment->tanggal_daftar,
                'status' => $enrollment->status,
                'tanggal_disetujui' => $enrollment->tanggal_disetujui,
                'tanggal_selesai' => $enrollment->tanggal_selesai,
                'nilai_akhir' => $enrollment->nilai_akhir,
                'predikat' => $enrollment->predikat,
            ];

            // Add nama_kategori
            $kursus->nama_kategori = $kursus->kategori ? $kursus->kategori->nama : null;

            return $kursus;
        });

        // Return collection of courses with enrollment information
        return KursusResource::collection($result);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error retrieving courses: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Enroll a Peserta in a course
     * 
     * @OA\Post(
     *     path="/api/v1/kursus/{id}/peserta",
     *     tags={"Kursus", "Peserta"},
     *     summary="Enroll Peserta in a course",
     *     description="Enroll a participant in a specific course",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Kursus ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"peserta_id"},
     *             @OA\Property(property="peserta_id", type="integer", example=1),
     *             @OA\Property(property="tanggal_daftar", type="string", format="date", example="2025-11-01"),
     *             @OA\Property(property="status", type="string", enum={"pending", "disetujui", "ditolak", "aktif", "selesai", "batal"}, example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Enrollment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enrollment created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="tanggal_daftar", type="string", format="date", example="2025-11-01"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="tanggal_disetujui", type="string", format="date", example=null),
     *                 @OA\Property(property="tanggal_selesai", type="string", format="date", example=null),
     *                 @OA\Property(property="nilai_akhir", type="number", format="float", example=null),
     *                 @OA\Property(property="predikat", type="string", example=null),
     *                 @OA\Property(property="alasan_ditolak", type="string", example=null),
     *                 @OA\Property(property="kursus", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                     @OA\Property(property="kode_kursus", type="string", example="K001")
     *                 ),
     *                 @OA\Property(property="peserta", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Peserta or Kursus not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kursus not found")
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
     *             @OA\Property(property="message", type="string", example="Error creating enrollment")
     *         )
     *     )
     * )
     */
    public function enrollKursus(Request $request, $id)
    {
        try {
            // Check if kursus exists
            $kursus = \Modules\Kursus\Entities\Kursus::find($id);
            if (!$kursus) {
                return response()->json(['message' => 'Kursus not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'peserta_id' => 'required|exists:pesertas,id',
                'tanggal_daftar' => 'nullable|date',
                'status' => 'nullable|in:pending,disetujui,ditolak,aktif,selesai,batal',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if already registered
            $existingPendaftaran = PendaftaranKursus::where('peserta_id', $request->peserta_id)
                ->where('kursus_id', $id)
                ->first();

            if ($existingPendaftaran) {
                return response()->json([
                    'message' => 'Peserta already registered for this course.'
                ], 422);
            }

            // Check if registration is open
            if (!$kursus->isPendaftaranOpen() && $request->user()->role !== 'super_admin') {
                return response()->json([
                    'message' => 'Registration is not open for this course.'
                ], 422);
            }

            // Check if quota is full
            $enrolledCount = PendaftaranKursus::where('kursus_id', $id)
                ->whereIn('status', ['pending', 'disetujui', 'aktif'])
                ->count();
            if ($enrolledCount >= $kursus->kuota_peserta && $kursus->kuota_peserta > 0) {
                return response()->json([
                    'message' => 'Course quota is full.'
                ], 422);
            }

            // Set default values
            $data = $request->all();
            $data['kursus_id'] = $id;
            if (!isset($data['tanggal_daftar'])) {
                $data['tanggal_daftar'] = now()->format('Y-m-d');
            }
            if (!isset($data['status'])) {
                $data['status'] = 'pending';
            }

            $pendaftaran = PendaftaranKursus::create($data);
            $pendaftaran->load(['kursus', 'peserta']);

            return response()->json([
                'message' => 'Enrollment created successfully',
                'data' => new \Modules\Kursus\Transformers\PendaftaranKursusResource($pendaftaran)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating enrollment: ' . $e->getMessage()
            ], 500);
        }
    }
}
