<?php

namespace Modules\Materi\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Materi\Entities\ProgresMateri;
use Modules\Materi\Entities\Materi;
use Modules\Materi\Transformers\ProgresMateriResource;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="ProgresMateri",
 *     description="API endpoints for managing learning progress"
 * )
 * 
 * @OA\Schema(
 *     schema="ProgresMateri",
 *     title="ProgresMateri",
 *     description="Progress record for participant learning materials",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="peserta_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="materi_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="is_selesai", type="boolean", example=false),
 *     @OA\Property(property="progress_persen", type="integer", example=50),
 *     @OA\Property(property="tanggal_mulai", type="string", format="date-time", example="2025-10-28T10:00:00Z"),
 *     @OA\Property(property="tanggal_selesai", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="durasi_belajar_menit", type="integer", example=30)
 * )
 */
class ProgresMateriController extends Controller
{
    /**
     * Get list of progress records
     * 
     * @OA\Get(
     *     path="/api/v1/progres-materi",
     *     tags={"Progress Materi"},
     *     summary="Get all progress records",
     *     description="Retrieves all progress records with optional filtering by peserta ID, materi ID, and completion status",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="peserta_id",
     *         in="query",
     *         description="Filter by participant ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="materi_id",
     *         in="query",
     *         description="Filter by material ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_selesai",
     *         in="query",
     *         description="Filter by completion status",
     *         required=false,
     *         @OA\Schema(type="boolean")
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
     *                     @OA\Property(property="materi_id", type="integer", example=1),
     *                     @OA\Property(property="is_selesai", type="boolean", example=false),
     *                     @OA\Property(property="progress_persen", type="integer", example=50),
     *                     @OA\Property(property="tanggal_mulai", type="string", format="date-time", example="2025-10-28T10:00:00Z"),
     *                     @OA\Property(property="tanggal_selesai", type="string", format="date-time", nullable=true, example=null),
     *                     @OA\Property(property="durasi_belajar_menit", type="integer", example=30),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-28T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-28T10:30:00Z"),
     *                     @OA\Property(
     *                         property="materi",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science")
     *                     ),
     *                     @OA\Property(
     *                         property="peserta",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama", type="string", example="John Doe")
     *                     )
     *                 )
     *             )
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
     *             @OA\Property(property="message", type="string", example="Error fetching progress records")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = ProgresMateri::with(['materi', 'peserta']);

            // Filter by peserta_id
            if ($request->has('peserta_id')) {
                $query->where('peserta_id', $request->peserta_id);
            }

            // Filter by materi_id
            if ($request->has('materi_id')) {
                $query->where('materi_id', $request->materi_id);
            }

            // Filter by is_selesai
            if ($request->has('is_selesai')) {
                $query->where('is_selesai', $request->boolean('is_selesai'));
            }

            $progresMateri = $query->get();

            return ProgresMateriResource::collection($progresMateri);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching progress records: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new progress record
     * 
     * @OA\Post(
     *     path="/api/v1/progres-materi",
     *     tags={"Progress Materi"},
     *     summary="Create a new progress record",
     *     description="Creates a new progress record for a participant and material",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"peserta_id","materi_id"},
     *             @OA\Property(property="peserta_id", type="integer", example=1, description="Participant ID"),
     *             @OA\Property(property="materi_id", type="integer", example=1, description="Material ID"),
     *             @OA\Property(property="is_selesai", type="boolean", example=false, description="Completion status"),
     *             @OA\Property(property="progress_persen", type="integer", example=0, description="Progress percentage (0-100)"),
     *             @OA\Property(property="durasi_belajar_menit", type="integer", example=0, description="Learning duration in minutes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Progress created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Progress created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="materi_id", type="integer", example=1),
     *                 @OA\Property(property="is_selesai", type="boolean", example=false),
     *                 @OA\Property(property="progress_persen", type="integer", example=0),
     *                 @OA\Property(property="tanggal_mulai", type="string", format="date-time", example="2025-10-28T10:00:00Z"),
     *                 @OA\Property(property="tanggal_selesai", type="string", format="date-time", nullable=true, example=null),
     *                 @OA\Property(property="durasi_belajar_menit", type="integer", example=0),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-28T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-28T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or progress already exists",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\AdditionalProperties(
     *                             type="array",
     *                             @OA\Items(type="string")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="message", type="string", example="Progress already exists for this peserta and materi."),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="peserta_id", type="integer", example=1),
     *                         @OA\Property(property="materi_id", type="integer", example=1)
     *                     )
     *                 )
     *             }
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
     *             @OA\Property(property="message", type="string", example="Error creating progress record")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'peserta_id' => 'required|exists:pesertas,id',
                'materi_id' => 'required|exists:materis,id',
                'is_selesai' => 'nullable|boolean',
                'progress_persen' => 'nullable|integer|min:0|max:100',
                'durasi_belajar_menit' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if progress already exists
            $existingProgres = ProgresMateri::where('peserta_id', $request->peserta_id)
                ->where('materi_id', $request->materi_id)
                ->first();

            if ($existingProgres) {
                return response()->json([
                    'message' => 'Progress already exists for this peserta and materi.',
                    'data' => new ProgresMateriResource($existingProgres)
                ], 422);
            }

            $data = $request->all();

            // Set default values
            if (!isset($data['is_selesai'])) {
                $data['is_selesai'] = false;
            }

            if (!isset($data['progress_persen'])) {
                $data['progress_persen'] = 0;
            }

            if (!isset($data['tanggal_mulai'])) {
                $data['tanggal_mulai'] = now();
            }

            // If marked as complete, set tanggal_selesai and progress_persen
            if ($data['is_selesai']) {
                $data['tanggal_selesai'] = now();
                $data['progress_persen'] = 100;
            }

            $progresMateri = ProgresMateri::create($data);

            return response()->json([
                'message' => 'Progress created successfully',
                'data' => new ProgresMateriResource($progresMateri)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating progress record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single progress record
     * 
     * @OA\Get(
     *     path="/api/v1/progres-materi/{id}",
     *     tags={"Progress Materi"},
     *     summary="Get a single progress record",
     *     description="Retrieves a specific progress record by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Progress record ID",
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
     *                 @OA\Property(property="materi_id", type="integer", example=1),
     *                 @OA\Property(property="is_selesai", type="boolean", example=false),
     *                 @OA\Property(property="progress_persen", type="integer", example=50),
     *                 @OA\Property(property="tanggal_mulai", type="string", format="date-time", example="2025-10-28T10:00:00Z"),
     *                 @OA\Property(property="tanggal_selesai", type="string", format="date-time", nullable=true, example=null),
     *                 @OA\Property(property="durasi_belajar_menit", type="integer", example=30),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-28T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-28T10:30:00Z"),
     *                 @OA\Property(
     *                     property="materi",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science")
     *                 ),
     *                 @OA\Property(
     *                     property="peserta",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="John Doe")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Progress record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Progress record not found")
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
     *             @OA\Property(property="message", type="string", example="Error fetching progress record")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $progresMateri = ProgresMateri::with(['materi', 'peserta'])->findOrFail($id);
            return new ProgresMateriResource($progresMateri);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Progress record not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching progress record: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a progress record
     * 
     * @OA\Put(
     *     path="/api/v1/progres-materi/{id}",
     *     tags={"Progress Materi"},
     *     summary="Update a progress record",
     *     description="Updates a specific progress record by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Progress record ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="peserta_id", type="integer", example=1, description="Participant ID"),
     *             @OA\Property(property="materi_id", type="integer", example=1, description="Material ID"),
     *             @OA\Property(property="is_selesai", type="boolean", example=false, description="Completion status"),
     *             @OA\Property(property="progress_persen", type="integer", example=50, description="Progress percentage (0-100)"),
     *             @OA\Property(property="durasi_belajar_menit", type="integer", example=30, description="Learning duration in minutes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Progress updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Progress updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="materi_id", type="integer", example=1),
     *                 @OA\Property(property="is_selesai", type="boolean", example=false),
     *                 @OA\Property(property="progress_persen", type="integer", example=50),
     *                 @OA\Property(property="tanggal_mulai", type="string", format="date-time", example="2025-10-28T10:00:00Z"),
     *                 @OA\Property(property="tanggal_selesai", type="string", format="date-time", nullable=true, example=null),
     *                 @OA\Property(property="durasi_belajar_menit", type="integer", example=30)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Progress record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Progress record not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
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
     *             @OA\Property(property="message", type="string", example="Error updating progress record")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $progresMateri = ProgresMateri::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'peserta_id' => 'sometimes|required|exists:pesertas,id',
                'materi_id' => 'sometimes|required|exists:materis,id',
                'is_selesai' => 'nullable|boolean',
                'progress_persen' => 'nullable|integer|min:0|max:100',
                'durasi_belajar_menit' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();

            // If marked as complete and wasn't complete before, set tanggal_selesai and progress_persen
            if ($request->filled('is_selesai') && $request->boolean('is_selesai') && !$progresMateri->is_selesai) {
                $data['tanggal_selesai'] = now();
                $data['progress_persen'] = 100;
            }

            // If marked as incomplete and was complete before, unset tanggal_selesai
            if ($request->filled('is_selesai') && !$request->boolean('is_selesai') && $progresMateri->is_selesai) {
                $data['tanggal_selesai'] = null;
            }

            $progresMateri->update($data);

            return response()->json([
                'message' => 'Progress updated successfully',
                'data' => new ProgresMateriResource($progresMateri)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Progress record not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating progress record: ' . $e->getMessage()
            ], 500);
        }
    }

   
    /**
     * Update progress percentage
     * 
     * @OA\Post(
     *     path="/api/v1/progres-materi/update-progress",
     *     tags={"Progress Materi"},
     *     summary="Update progress percentage",
     *     description="Updates or creates a progress record with a specific progress percentage",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"peserta_id","materi_id","progress_persen"},
     *             @OA\Property(property="peserta_id", type="integer", example=1, description="Participant ID"),
     *             @OA\Property(property="materi_id", type="integer", example=1, description="Material ID"),
     *             @OA\Property(property="progress_persen", type="integer", example=75, description="Progress percentage (0-100)"),
     *             @OA\Property(property="durasi_belajar_menit", type="integer", example=45, description="Learning duration in minutes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Progress updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Progress updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="materi_id", type="integer", example=1),
     *                 @OA\Property(property="is_selesai", type="boolean", example=true),
     *                 @OA\Property(property="progress_persen", type="integer", example=75),
     *                 @OA\Property(property="tanggal_mulai", type="string", format="date-time", example="2025-10-28T10:00:00Z"),
     *                 @OA\Property(property="tanggal_selesai", type="string", format="date-time", nullable=true, example="2025-10-28T11:30:00Z"),
     *                 @OA\Property(property="durasi_belajar_menit", type="integer", example=45)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
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
     *             @OA\Property(property="message", type="string", example="Error updating progress")
     *         )
     *     )
     * )
     */
    public function updateProgress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'peserta_id' => 'required|exists:pesertas,id',
                'materi_id' => 'required|exists:materis,id',
                'progress_persen' => 'required|integer|min:0|max:100',
                'durasi_belajar_menit' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Find or create progress
            $progresMateri = ProgresMateri::firstOrNew([
                'peserta_id' => $request->peserta_id,
                'materi_id' => $request->materi_id,
            ]);

            // If new, set tanggal_mulai
            if (!$progresMateri->exists) {
                $progresMateri->tanggal_mulai = now();
            }

            // Update progress
            $progresMateri->progress_persen = $request->progress_persen;

            // Update durasi_belajar_menit if provided
            if ($request->filled('durasi_belajar_menit')) {
                $progresMateri->durasi_belajar_menit = $request->durasi_belajar_menit;
            }

            // If progress is 100%, mark as complete
            if ($request->progress_persen == 100) {
                $progresMateri->is_selesai = true;
                $progresMateri->tanggal_selesai = now();
            }

            $progresMateri->save();

            return response()->json([
                'message' => 'Progress updated successfully',
                'data' => new ProgresMateriResource($progresMateri)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating progress: ' . $e->getMessage()
            ], 500);
        }
    }
}
