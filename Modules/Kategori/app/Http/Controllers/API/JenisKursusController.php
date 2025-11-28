<?php

namespace Modules\Kategori\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kategori\Entities\JenisKursus;
use Modules\Kategori\Transformers\JenisKursusResource;

/**
 * @OA\Tag(
 *     name="Jenis Kursus",
 *     description="API endpoints for managing Course Types"
 * )
 */
class JenisKursusController extends Controller
{
    /**
     * Get all Jenis Kursus
     * 
     * @OA\Get(
     *     path="/api/v1/jenis-kursus",
     *     tags={"Jenis Kursus"},
     *     summary="Get all course types",
     *     description="Returns list of all course types with optional category filter",
     *     @OA\Parameter(
     *         name="kategori_kursus_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
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
     *                     @OA\Property(property="kategori_kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kategori_kursus", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                         @OA\Property(property="slug", type="string", example="teknologi-informasi")
     *                     ),
     *                     @OA\Property(property="kode_jenis", type="string", example="WEB"),
     *                     @OA\Property(property="nama_jenis", type="string", example="Web Development"),
     *                     @OA\Property(property="slug", type="string", example="web-development"),
     *                     @OA\Property(property="deskripsi", type="string", example="Pembelajaran tentang pengembangan web"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="kursus_count", type="integer", example=10),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching course types")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = JenisKursus::with('kategoriKursus')
                ->withCount('kursus');

            // Filter by kategori_kursus_id
            if ($request->has('kategori_kursus_id')) {
                $query->where('kategori_kursus_id', $request->kategori_kursus_id);
            }

            // Filter by is_active
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $jenisKursus = $query->orderBy('urutan')->get();
            
            return JenisKursusResource::collection($jenisKursus);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching course types: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific course type by ID
     * 
     * @OA\Get(
     *     path="/api/v1/jenis-kursus/{id}",
     *     tags={"Jenis Kursus"},
     *     summary="Get course type by ID",
     *     description="Returns specific course type details with its category",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course type ID",
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
     *                 @OA\Property(property="kategori_kursus_id", type="integer", example=1),
     *                 @OA\Property(property="kategori_kursus", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                     @OA\Property(property="slug", type="string", example="teknologi-informasi")
     *                 ),
     *                 @OA\Property(property="kode_jenis", type="string", example="WEB"),
     *                 @OA\Property(property="nama_jenis", type="string", example="Web Development"),
     *                 @OA\Property(property="slug", type="string", example="web-development"),
     *                 @OA\Property(property="deskripsi", type="string", example="Pembelajaran tentang pengembangan web"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course type not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Course type not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error retrieving course type")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $jenisKursus = JenisKursus::with('kategoriKursus')->find($id);

            if (!$jenisKursus) {
                return response()->json([
                    'message' => 'Course type not found'
                ], 404);
            }

            return new JenisKursusResource($jenisKursus);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving course type: ' . $e->getMessage()
            ], 500);
        }
    }
}