<?php

namespace Modules\Blog\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Blog\Entities\KategoriBerita;
use Modules\Blog\Transformers\KategoriBeritaResource;

/**
 * @OA\Tag(
 *     name="Kategori Berita",
 *     description="API endpoints for managing News Categories"
 * )
 */
class KategoriBeritaController extends Controller
{
    /**
     * Get all Kategori Berita
     * 
     * @OA\Get(
     *     path="/api/v1/kategori-berita",
     *     tags={"Kategori Berita"},
     *     summary="Get all news categories",
     *     description="Returns list of all news categories with optional filter",
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
     *                     @OA\Property(property="nama_kategori", type="string", example="Berita Umum"),
     *                     @OA\Property(property="slug", type="string", example="berita-umum"),
     *                     @OA\Property(property="deskripsi", type="string", example="Berita umum dan informasi terkini"),
     *                     @OA\Property(property="icon", type="string", example="bi-newspaper"),
     *                     @OA\Property(property="color", type="string", example="primary"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="berita_count", type="integer", example=25),
     *                     @OA\Property(property="berita_published_count", type="integer", example=20),
     *                     @OA\Property(property="created_at", type="string", example="2025-11-30 09:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-11-30 09:00:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching news categories")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = KategoriBerita::withCount(['berita', 'beritaPublished']);

            // Filter by is_active
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            } else {
                // Default: only active
                $query->active();
            }

            $kategoris = $query->ordered()->get();
            
            return KategoriBeritaResource::collection($kategoris);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching news categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific kategori by ID
     * 
     * @OA\Get(
     *     path="/api/v1/kategori-berita/{id}",
     *     tags={"Kategori Berita"},
     *     summary="Get news category by ID",
     *     description="Returns specific news category details with berita count",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Kategori ID",
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
     *                 @OA\Property(property="nama_kategori", type="string", example="Berita Umum"),
     *                 @OA\Property(property="slug", type="string", example="berita-umum"),
     *                 @OA\Property(property="deskripsi", type="string", example="Berita umum dan informasi terkini"),
     *                 @OA\Property(property="icon", type="string", example="bi-newspaper"),
     *                 @OA\Property(property="color", type="string", example="primary"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(property="berita_count", type="integer", example=25),
     *                 @OA\Property(property="berita_published_count", type="integer", example=20),
     *                 @OA\Property(property="created_at", type="string", example="2025-11-30 09:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-11-30 09:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="News category not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error retrieving news category")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $kategori = KategoriBerita::withCount(['berita', 'beritaPublished'])
                                     ->find($id);

            if (!$kategori) {
                return response()->json([
                    'message' => 'News category not found'
                ], 404);
            }

            return new KategoriBeritaResource($kategori);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving news category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific kategori by slug
     * 
     * @OA\Get(
     *     path="/api/v1/kategori-berita/slug/{slug}",
     *     tags={"Kategori Berita"},
     *     summary="Get news category by slug",
     *     description="Returns specific news category details by slug",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Kategori slug",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function showBySlug($slug)
    {
        try {
            $kategori = KategoriBerita::withCount(['berita', 'beritaPublished'])
                                     ->where('slug', $slug)
                                     ->first();

            if (!$kategori) {
                return response()->json([
                    'message' => 'News category not found'
                ], 404);
            }

            return new KategoriBeritaResource($kategori);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving news category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get berita by kategori ID
     * 
     * @OA\Get(
     *     path="/api/v1/kategori-berita/{id}/berita",
     *     tags={"Kategori Berita"},
     *     summary="Get news posts by category ID",
     *     description="Returns all news posts in specific category",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Kategori ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function berita(Request $request, $id)
    {
        try {
            $kategori = KategoriBerita::find($id);

            if (!$kategori) {
                return response()->json([
                    'message' => 'News category not found'
                ], 404);
            }

            $perPage = $request->get('per_page', 10);
            
            $berita = $kategori->beritaPublished()
                              ->with(['kategori', 'penulis'])
                              ->orderBy('published_at', 'desc')
                              ->paginate($perPage);
            
            return \Modules\Blog\Transformers\BeritaResource::collection($berita);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching news: ' . $e->getMessage()
            ], 500);
        }
    }
}