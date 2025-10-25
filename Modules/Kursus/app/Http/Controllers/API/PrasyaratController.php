<?php

namespace Modules\Kursus\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kursus\Entities\Prasyarat;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Transformers\PrasyaratResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Course Prerequisites",
 *     description="API endpoints for managing course prerequisites"
 * )
 */
class PrasyaratController extends Controller
{
    /**
     * Get list of course prerequisites
     * 
     * @OA\Get(
     *     path="/api/v1/prasyarat",
     *     tags={"Course Prerequisites"},
     *     summary="Get all course prerequisites",
     *     description="Returns list of all course prerequisites with optional filtering by course ID",
     *     security={{"sanctum":{}}},
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
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kursus_prasyarat_id", type="integer", example=2),
     *                     @OA\Property(property="deskripsi", type="string", example="Kursus ini wajib diselesaikan sebelum mengikuti kursus lanjutan"),
     *                     @OA\Property(property="is_wajib", type="boolean", example=true),
     *                     @OA\Property(property="kursus", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="judul", type="string", example="Pemrograman Python Lanjutan"),
     *                         @OA\Property(property="kode_kursus", type="string", example="PYT-002")
     *                     ),
     *                     @OA\Property(property="kursusPrasyarat", type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="judul", type="string", example="Dasar-dasar Pemrograman Python"),
     *                         @OA\Property(property="kode_kursus", type="string", example="PYT-001")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
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
     *             @OA\Property(property="message", type="string", example="Error fetching prerequisites")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Prasyarat::with(['kursus', 'kursusPrasyarat']);

            // Filter by kursus_id
            if ($request->has('kursus_id')) {
                $query->where('kursus_id', $request->kursus_id);
            }

            $prasyarats = $query->get();

            return PrasyaratResource::collection($prasyarats);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching prerequisites: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new course prerequisite
     * 
     * @OA\Post(
     *     path="/api/v1/prasyarat",
     *     tags={"Course Prerequisites"},
     *     summary="Create new course prerequisite",
     *     description="Create a new prerequisite relationship between courses",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kursus_id","kursus_prasyarat_id"},
     *             @OA\Property(property="kursus_id", type="integer", example=1, description="The course that requires a prerequisite"),
     *             @OA\Property(property="kursus_prasyarat_id", type="integer", example=2, description="The prerequisite course that must be completed"),
     *             @OA\Property(property="deskripsi", type="string", example="Kursus ini wajib diselesaikan sebelum mengikuti kursus lanjutan"),
     *             @OA\Property(property="is_wajib", type="boolean", example=true, description="Whether the prerequisite is mandatory")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Prerequisite created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prasyarat created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="kursus_prasyarat_id", type="integer", example=2),
     *                 @OA\Property(property="deskripsi", type="string", example="Kursus ini wajib diselesaikan sebelum mengikuti kursus lanjutan"),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="kursus", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul", type="string", example="Pemrograman Python Lanjutan"),
     *                     @OA\Property(property="kode_kursus", type="string", example="PYT-002")
     *                 ),
     *                 @OA\Property(property="kursusPrasyarat", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="judul", type="string", example="Dasar-dasar Pemrograman Python"),
     *                     @OA\Property(property="kode_kursus", type="string", example="PYT-001")
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
     *             @OA\Property(property="message", type="string", example="Error creating prerequisite")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kursus_id' => 'required|exists:kursus,id',
                'kursus_prasyarat_id' => 'nullable|exists:kursus,id', // Hapus 'different:kursus_id'
                'deskripsi' => 'nullable|string',
                'is_wajib' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if prasyarat already exists
            $existingPrasyarat = Prasyarat::where('kursus_id', $request->kursus_id)
                ->where('kursus_prasyarat_id', $request->kursus_prasyarat_id)
                ->first();
            if ($existingPrasyarat) {
                return response()->json([
                    'message' => 'Prasyarat already exists.'
                ], 422);
            }

            $prasyarat = Prasyarat::create($request->all());
            $prasyarat->load(['kursus', 'kursusPrasyarat']);

            return response()->json([
                'message' => 'Prasyarat created successfully',
                'data' => new PrasyaratResource($prasyarat)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating prerequisite: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific course prerequisite by ID
     * 
     * @OA\Get(
     *     path="/api/v1/prasyarat/{id}",
     *     tags={"Course Prerequisites"},
     *     summary="Get course prerequisite by ID",
     *     description="Returns specific course prerequisite details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Prerequisite ID",
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
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="kursus_prasyarat_id", type="integer", example=2),
     *                 @OA\Property(property="deskripsi", type="string", example="Kursus ini wajib diselesaikan sebelum mengikuti kursus lanjutan"),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="kursus", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul", type="string", example="Pemrograman Python Lanjutan"),
     *                     @OA\Property(property="kode_kursus", type="string", example="PYT-002"),
     *                     @OA\Property(property="status", type="string", example="aktif")
     *                 ),
     *                 @OA\Property(property="kursusPrasyarat", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="judul", type="string", example="Dasar-dasar Pemrograman Python"),
     *                     @OA\Property(property="kode_kursus", type="string", example="PYT-001"),
     *                     @OA\Property(property="status", type="string", example="aktif")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prerequisite not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prerequisite not found")
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
     *             @OA\Property(property="message", type="string", example="Error retrieving prerequisite")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $prasyarat = Prasyarat::with(['kursus', 'kursusPrasyarat'])->find($id);

            if (!$prasyarat) {
                return response()->json(['message' => 'Prerequisite not found'], 404);
            }

            return new PrasyaratResource($prasyarat);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving prerequisite: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update course prerequisite
     * 
     * @OA\Put(
     *     path="/api/v1/prasyarat/{id}",
     *     tags={"Course Prerequisites"},
     *     summary="Update course prerequisite",
     *     description="Update an existing course prerequisite",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Prerequisite ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="kursus_id", type="integer", example=1, description="The course that requires a prerequisite"),
     *             @OA\Property(property="kursus_prasyarat_id", type="integer", example=2, description="The prerequisite course that must be completed"),
     *             @OA\Property(property="deskripsi", type="string", example="Updated description for the prerequisite relationship"),
     *             @OA\Property(property="is_wajib", type="boolean", example=false, description="Whether the prerequisite is mandatory")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prerequisite updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prasyarat updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="kursus_prasyarat_id", type="integer", example=2),
     *                 @OA\Property(property="deskripsi", type="string", example="Updated description for the prerequisite relationship"),
     *                 @OA\Property(property="is_wajib", type="boolean", example=false),
     *                 @OA\Property(property="kursus", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul", type="string", example="Pemrograman Python Lanjutan"),
     *                     @OA\Property(property="kode_kursus", type="string", example="PYT-002")
     *                 ),
     *                 @OA\Property(property="kursusPrasyarat", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="judul", type="string", example="Dasar-dasar Pemrograman Python"),
     *                     @OA\Property(property="kode_kursus", type="string", example="PYT-001")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-26 09:15:45")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prerequisite not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prerequisite not found")
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
     *             @OA\Property(property="message", type="string", example="Error updating prerequisite")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $prasyarat = Prasyarat::find($id);

            if (!$prasyarat) {
                return response()->json(['message' => 'Prerequisite not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'kursus_id' => 'sometimes|required|exists:kursus,id',
                'kursus_prasyarat_id' => 'sometimes|required|exists:kursus,id|different:kursus_id',
                'deskripsi' => 'nullable|string',
                'is_wajib' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // If trying to change kursus_id or kursus_prasyarat_id, check if new combination already exists
            if (($request->filled('kursus_id') && $request->kursus_id != $prasyarat->kursus_id) ||
                ($request->filled('kursus_prasyarat_id') && $request->kursus_prasyarat_id != $prasyarat->kursus_prasyarat_id)
            ) {

                $kursus_id = $request->filled('kursus_id') ? $request->kursus_id : $prasyarat->kursus_id;
                $kursus_prasyarat_id = $request->filled('kursus_prasyarat_id') ? $request->kursus_prasyarat_id : $prasyarat->kursus_prasyarat_id;

                $existingPrasyarat = Prasyarat::where('kursus_id', $kursus_id)
                    ->where('kursus_prasyarat_id', $kursus_prasyarat_id)
                    ->where('id', '!=', $id)
                    ->first();
                if ($existingPrasyarat) {
                    return response()->json([
                        'message' => 'Prasyarat already exists with these kursus combinations.'
                    ], 422);
                }
            }

            $prasyarat->update($request->all());
            $prasyarat->load(['kursus', 'kursusPrasyarat']);

            return response()->json([
                'message' => 'Prasyarat updated successfully',
                'data' => new PrasyaratResource($prasyarat)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating prerequisite: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete course prerequisite
     * 
     * @OA\Delete(
     *     path="/api/v1/prasyarat/{id}",
     *     tags={"Course Prerequisites"},
     *     summary="Delete course prerequisite",
     *     description="Delete a course prerequisite relationship",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Prerequisite ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prerequisite deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prasyarat deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prerequisite not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prerequisite not found")
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
     *             @OA\Property(property="message", type="string", example="Error deleting prerequisite")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $prasyarat = Prasyarat::find($id);

            if (!$prasyarat) {
                return response()->json(['message' => 'Prerequisite not found'], 404);
            }

            $prasyarat->delete();

            return response()->json([
                'message' => 'Prasyarat deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting prerequisite: ' . $e->getMessage()
            ], 500);
        }
    }
}
