<?php

namespace Modules\Kursus\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kursus\Entities\PendaftaranKursus;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Transformers\PendaftaranKursusResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Kursus",
 *     description="API endpoints for managing Kursus"
 * )
 */
class PendaftaranKursusController extends Controller
{
    /**
     * Get paginated list of Kursus
     * 
     * @OA\Get(
     *     path="/api/v1/pendaftaran",
     *     tags={"Kursus"},
     *     summary="Get all Kursus",
     *     description="Returns paginated list of all Kursus with filtering options",
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
     *         description="Filter by enrollment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "disetujui", "ditolak", "aktif", "selesai", "batal"})
     *     ),
     *     @OA\Parameter(
     *         name="peserta_id",
     *         in="query",
     *         description="Filter by participant ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter by course ID",
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
     *                     @OA\Property(property="peserta_id", type="integer", example=1),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="tanggal_daftar", type="string", format="date", example="2025-11-01"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="tanggal_disetujui", type="string", format="date", example=null),
     *                     @OA\Property(property="tanggal_selesai", type="string", format="date", example=null),
     *                     @OA\Property(property="nilai_akhir", type="number", format="float", example=null),
     *                     @OA\Property(property="predikat", type="string", example=null),
     *                     @OA\Property(property="alasan_ditolak", type="string", example=null),
     *                     @OA\Property(property="kursus", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                         @OA\Property(property="kode_kursus", type="string", example="K001"),
     *                         @OA\Property(property="thumbnail", type="string", example="http://localhost:8000/storage/kursus/thumbnail/pengantar-data-science-1698304599.jpg"),
     *                         @OA\Property(property="level", type="string", example="dasar"),
     *                         @OA\Property(property="tipe", type="string", example="daring"),
     *                         @OA\Property(property="status", type="string", example="aktif")
     *                     ),
     *                     @OA\Property(property="peserta", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                         @OA\Property(property="nip", type="string", example="198501012010011001")
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching enrollments")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $perPage = max(5, min(100, (int)$perPage));

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

            $pendaftaran = $query->paginate($perPage);

            return PendaftaranKursusResource::collection($pendaftaran);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching enrollments: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new course enrollment
     * 
     * @OA\Post(
     *     path="/api/v1/pendaftaran",
     *     tags={"Kursus"},
     *     summary="Create new course enrollment",
     *     description="Enroll a participant in a course",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"peserta_id","kursus_id"},
     *             @OA\Property(property="peserta_id", type="integer", example=1),
     *             @OA\Property(property="kursus_id", type="integer", example=1),
     *             @OA\Property(property="tanggal_daftar", type="string", format="date", example="2025-11-01"),
     *             @OA\Property(property="status", type="string", enum={"pending", "disetujui", "ditolak", "aktif", "selesai", "batal"}, example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Enrollment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pendaftaran created successfully"),
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
    public function store(Request $request)
    {
        try {
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
            $pendaftaran->load(['kursus', 'peserta']);

            return response()->json([
                'message' => 'Pendaftaran created successfully',
                'data' => new PendaftaranKursusResource($pendaftaran)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating enrollment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific course enrollment by ID
     * 
     * @OA\Get(
     *     path="/api/v1/pendaftaran/{id}",
     *     tags={"Kursus"},
     *     summary="Get course enrollment by ID",
     *     description="Returns specific course enrollment details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Enrollment ID",
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
     *                     @OA\Property(property="kode_kursus", type="string", example="K001"),
     *                     @OA\Property(property="thumbnail", type="string", example="http://localhost:8000/storage/kursus/thumbnail/pengantar-data-science-1698304599.jpg"),
     *                     @OA\Property(property="level", type="string", example="dasar"),
     *                     @OA\Property(property="tipe", type="string", example="daring"),
     *                     @OA\Property(property="status", type="string", example="aktif")
     *                 ),
     *                 @OA\Property(property="peserta", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="nip", type="string", example="198501012010011001")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enrollment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enrollment not found")
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
     *             @OA\Property(property="message", type="string", example="Error retrieving enrollment")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $pendaftaran = PendaftaranKursus::with(['kursus', 'peserta'])->find($id);

            if (!$pendaftaran) {
                return response()->json(['message' => 'Enrollment not found'], 404);
            }

            return new PendaftaranKursusResource($pendaftaran);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving enrollment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update course enrollment
     * 
     * @OA\Put(
     *     path="/api/v1/pendaftaran/{id}",
     *     tags={"Kursus"},
     *     summary="Update course enrollment",
     *     description="Update an existing course enrollment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Enrollment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="peserta_id", type="integer", example=1),
     *             @OA\Property(property="kursus_id", type="integer", example=1),
     *             @OA\Property(property="tanggal_daftar", type="string", format="date", example="2025-11-01"),
     *             @OA\Property(property="status", type="string", enum={"pending", "disetujui", "ditolak", "aktif", "selesai", "batal"}, example="disetujui"),
     *             @OA\Property(property="alasan_ditolak", type="string", example=""),
     *             @OA\Property(property="nilai_akhir", type="number", format="float", example=85),
     *             @OA\Property(property="predikat", type="string", enum={"sangat_baik", "baik", "cukup", "kurang"}, example="baik"),
     *             @OA\Property(property="tanggal_disetujui", type="string", format="date", example="2025-11-05"),
     *             @OA\Property(property="tanggal_selesai", type="string", format="date", example="2025-12-20")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pendaftaran updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="tanggal_daftar", type="string", format="date", example="2025-11-01"),
     *                 @OA\Property(property="status", type="string", example="disetujui"),
     *                 @OA\Property(property="tanggal_disetujui", type="string", format="date", example="2025-11-05"),
     *                 @OA\Property(property="tanggal_selesai", type="string", format="date", example="2025-12-20"),
     *                 @OA\Property(property="nilai_akhir", type="number", format="float", example=85),
     *                 @OA\Property(property="predikat", type="string", example="baik"),
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
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 07:15:22")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enrollment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enrollment not found")
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
     *             @OA\Property(property="message", type="string", example="Error updating enrollment")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $pendaftaran = PendaftaranKursus::find($id);

            if (!$pendaftaran) {
                return response()->json(['message' => 'Enrollment not found'], 404);
            }

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
            $pendaftaran->load(['kursus', 'peserta']);

            return response()->json([
                'message' => 'Pendaftaran updated successfully',
                'data' => new PendaftaranKursusResource($pendaftaran)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating enrollment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete course enrollment
     * 
     * @OA\Delete(
     *     path="/api/v1/pendaftaran/{id}",
     *     tags={"Kursus"},
     *     summary="Delete course enrollment",
     *     description="Delete a course enrollment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Enrollment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pendaftaran deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enrollment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enrollment not found")
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
     *             @OA\Property(property="message", type="string", example="Error deleting enrollment")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $pendaftaran = PendaftaranKursus::find($id);

            if (!$pendaftaran) {
                return response()->json(['message' => 'Enrollment not found'], 404);
            }

            $pendaftaran->delete();

            return response()->json([
                'message' => 'Pendaftaran deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting enrollment: ' . $e->getMessage()
            ], 500);
        }
    }
}
