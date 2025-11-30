<?php

namespace Modules\Blog\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Blog\Entities\Berita;
use Modules\Blog\Transformers\BeritaResource;

/**
 * @OA\Tag(
 *     name="Berita",
 *     description="API endpoints for managing News/Blog posts"
 * )
 */
class BeritaController extends Controller
{
    /**
     * Get all Berita
     * 
     * @OA\Get(
     *     path="/api/v1/berita",
     *     tags={"Berita"},
     *     summary="Get all news/blog posts",
     *     description="Returns list of all news posts with optional filters",
     *     @OA\Parameter(
     *         name="kategori_berita_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (draft, published, archived)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "published", "archived"})
     *     ),
     *     @OA\Parameter(
     *         name="is_featured",
     *         in="query",
     *         description="Filter by featured status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by title or content",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kategori_berita_id", type="integer", example=1),
     *                     @OA\Property(property="kategori", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_kategori", type="string", example="Berita Umum"),
     *                         @OA\Property(property="slug", type="string", example="berita-umum"),
     *                         @OA\Property(property="icon", type="string", example="bi-newspaper"),
     *                         @OA\Property(property="color", type="string", example="primary")
     *                     ),
     *                     @OA\Property(property="penulis", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_lengkap", type="string", example="Admin User")
     *                     ),
     *                     @OA\Property(property="judul", type="string", example="Judul Berita Terbaru"),
     *                     @OA\Property(property="slug", type="string", example="judul-berita-terbaru"),
     *                     @OA\Property(property="ringkasan", type="string", example="Ringkasan singkat berita"),
     *                     @OA\Property(property="konten", type="string", example="<p>Konten lengkap berita...</p>"),
     *                     @OA\Property(property="gambar_utama", type="string", example="1234567890_image.jpg"),
     *                     @OA\Property(property="gambar_utama_url", type="string", example="http://example.com/storage/berita/1234567890_image.jpg"),
     *                     @OA\Property(property="sumber_gambar", type="string", example="Unsplash"),
     *                     @OA\Property(property="status", type="string", example="published"),
     *                     @OA\Property(property="is_featured", type="boolean", example=true),
     *                     @OA\Property(property="view_count", type="integer", example=150),
     *                     @OA\Property(property="published_at", type="string", example="2025-11-30 10:00:00"),
     *                     @OA\Property(property="reading_time", type="string", example="5 menit baca"),
     *                     @OA\Property(property="excerpt", type="string", example="Ringkasan singkat..."),
     *                     @OA\Property(property="created_at", type="string", example="2025-11-30 09:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-11-30 09:30:00")
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
     *             @OA\Property(property="message", type="string", example="Error fetching news")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Berita::with(['kategori', 'penulis']);

            // Filter by kategori_berita_id
            if ($request->has('kategori_berita_id')) {
                $query->where('kategori_berita_id', $request->kategori_berita_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            } else {
                // Default: only published
                $query->published();
            }

            // Filter by is_featured
            if ($request->has('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            // Search
            if ($request->has('search')) {
                $query->search($request->search);
            }

            $perPage = $request->get('per_page', 10);
            $berita = $query->orderBy('published_at', 'desc')->paginate($perPage);
            
            return BeritaResource::collection($berita);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching news: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific berita by ID
     * 
     * @OA\Get(
     *     path="/api/v1/berita/{id}",
     *     tags={"Berita"},
     *     summary="Get news post by ID",
     *     description="Returns specific news post details with category and author",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Berita ID",
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
     *                 @OA\Property(property="kategori_berita_id", type="integer", example=1),
     *                 @OA\Property(property="kategori", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_kategori", type="string", example="Berita Umum"),
     *                     @OA\Property(property="slug", type="string", example="berita-umum"),
     *                     @OA\Property(property="icon", type="string", example="bi-newspaper"),
     *                     @OA\Property(property="color", type="string", example="primary")
     *                 ),
     *                 @OA\Property(property="penulis", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_lengkap", type="string", example="Admin User")
     *                 ),
     *                 @OA\Property(property="judul", type="string", example="Judul Berita Terbaru"),
     *                 @OA\Property(property="slug", type="string", example="judul-berita-terbaru"),
     *                 @OA\Property(property="ringkasan", type="string", example="Ringkasan singkat berita"),
     *                 @OA\Property(property="konten", type="string", example="<p>Konten lengkap berita...</p>"),
     *                 @OA\Property(property="gambar_utama", type="string", example="1234567890_image.jpg"),
     *                 @OA\Property(property="gambar_utama_url", type="string", example="http://example.com/storage/berita/1234567890_image.jpg"),
     *                 @OA\Property(property="sumber_gambar", type="string", example="Unsplash"),
     *                 @OA\Property(property="status", type="string", example="published"),
     *                 @OA\Property(property="is_featured", type="boolean", example=true),
     *                 @OA\Property(property="view_count", type="integer", example=150),
     *                 @OA\Property(property="published_at", type="string", example="2025-11-30 10:00:00"),
     *                 @OA\Property(property="reading_time", type="string", example="5 menit baca"),
     *                 @OA\Property(property="excerpt", type="string", example="Ringkasan singkat..."),
     *                 @OA\Property(property="meta_title", type="string", example="Judul SEO"),
     *                 @OA\Property(property="meta_description", type="string", example="Deskripsi SEO"),
     *                 @OA\Property(property="meta_keywords", type="string", example="keyword1, keyword2"),
     *                 @OA\Property(property="created_at", type="string", example="2025-11-30 09:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-11-30 09:30:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="News not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="News not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error retrieving news")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $berita = Berita::with(['kategori', 'penulis'])->find($id);

            if (!$berita) {
                return response()->json([
                    'message' => 'News not found'
                ], 404);
            }

            // Increment view count
            $berita->incrementViewCount();

            return new BeritaResource($berita);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving news: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific berita by slug
     * 
     * @OA\Get(
     *     path="/api/v1/berita/slug/{slug}",
     *     tags={"Berita"},
     *     summary="Get news post by slug",
     *     description="Returns specific news post details by slug",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Berita slug",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="News not found"
     *     )
     * )
     */
    public function showBySlug($slug)
    {
        try {
            $berita = Berita::with(['kategori', 'penulis'])
                           ->where('slug', $slug)
                           ->first();

            if (!$berita) {
                return response()->json([
                    'message' => 'News not found'
                ], 404);
            }

            // Increment view count
            $berita->incrementViewCount();

            return new BeritaResource($berita);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving news: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest berita
     * 
     * @OA\Get(
     *     path="/api/v1/berita/latest",
     *     tags={"Berita"},
     *     summary="Get latest news posts",
     *     description="Returns latest published news posts",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items to return",
     *         required=false,
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function latest(Request $request)
    {
        try {
            $limit = $request->get('limit', 5);
            
            $berita = Berita::with(['kategori', 'penulis'])
                           ->published()
                           ->latest($limit)
                           ->get();
            
            return BeritaResource::collection($berita);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching latest news: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular berita
     * 
     * @OA\Get(
     *     path="/api/v1/berita/popular",
     *     tags={"Berita"},
     *     summary="Get popular news posts",
     *     description="Returns most viewed news posts",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items to return",
     *         required=false,
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function popular(Request $request)
    {
        try {
            $limit = $request->get('limit', 5);
            
            $berita = Berita::with(['kategori', 'penulis'])
                           ->published()
                           ->popular($limit)
                           ->get();
            
            return BeritaResource::collection($berita);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching popular news: ' . $e->getMessage()
            ], 500);
        }
    }
}