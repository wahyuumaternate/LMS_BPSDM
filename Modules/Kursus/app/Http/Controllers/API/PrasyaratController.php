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
 *     name="Persyaratan Kursus",
 *     description="API endpoints for managing Persyaratan Kursus"
 * )
 */
class PrasyaratController extends Controller
{
    /**
     * Get list of Persyaratan Kursus
     * 
     * @OA\Get(
     *     path="/api/v1/prasyarat",
     *     tags={"Persyaratan Kursus"},
     *     summary="Get all Persyaratan Kursus",
     *     description="Returns list of all Persyaratan Kursus with optional filtering by course ID",
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
     * Get specific course prerequisite by ID
     * 
     * @OA\Get(
     *     path="/api/v1/prasyarat/{id}",
     *     tags={"Persyaratan Kursus"},
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

   
}
