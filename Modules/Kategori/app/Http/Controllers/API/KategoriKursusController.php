<?php

namespace Modules\Kategori\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kategori\Entities\KategoriKursus;
use Modules\Kategori\Transformers\KategoriKursusResource;

/**
 * @OA\Tag(
 *     name="Kategori Kursus",
 *     description="API endpoints for managing Course Categories"
 * )
 */
class KategoriKursusController extends Controller
{
    /**
     * Get all Kategori Kursus
     * 
     * @OA\Get(
     *     path="/api/v1/kategori-kursus",
     *     tags={"Kategori Kursus"},
     *     summary="Get all course categories",
     *     description="Returns list of all course categories ordered by sequence",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                     @OA\Property(property="slug", type="string", example="teknologi-informasi"),
     *                     @OA\Property(property="deskripsi", type="string", example="Kategori untuk kursus-kursus terkait teknologi informasi"),
     *                     @OA\Property(property="icon", type="string", example="fa-laptop-code"),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="jenis_kursus_count", type="integer", example=5),
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
     *             @OA\Property(property="message", type="string", example="Error fetching categories")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $kategori = KategoriKursus::withCount('jenisKursus')
                ->orderBy('urutan')
                ->get();
            
            return KategoriKursusResource::collection($kategori);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific course category by ID
     * 
     * @OA\Get(
     *     path="/api/v1/kategori-kursus/{id}",
     *     tags={"Kategori Kursus"},
     *     summary="Get course category by ID",
     *     description="Returns specific course category details with its jenis kursus",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
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
     *                 @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                 @OA\Property(property="slug", type="string", example="teknologi-informasi"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kategori untuk kursus-kursus terkait teknologi informasi"),
     *                 @OA\Property(property="icon", type="string", example="fa-laptop-code"),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(property="jenis_kursus", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="kode_jenis", type="string", example="WEB"),
     *                         @OA\Property(property="nama_jenis", type="string", example="Web Development"),
     *                         @OA\Property(property="slug", type="string", example="web-development")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error retrieving category")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $kategori = KategoriKursus::with(['jenisKursus' => function ($query) {
                $query->where('is_active', true)->orderBy('urutan');
            }])->find($id);

            if (!$kategori) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            return new KategoriKursusResource($kategori);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving category: ' . $e->getMessage()
            ], 500);
        }
    }
}