<?php

namespace Modules\Modul\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Modul\Entities\Modul;
use Modules\Modul\Transformers\ModulResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Modul Kursus",
 *     description="API endpoints for managing Modul Kursus"
 * )
 */
class ModulController extends Controller
{
    /**
     * Get list of Modul Kursus - Public (No Auth)
     * 
     * @OA\Get(
     *     path="/api/v1/modul/no-auth",
     *     tags={"Modul Kursus"},
     *     summary="Get all Modul Kursus (Public - No materials)",
     *     description="Returns list of all Modul Kursus WITHOUT materials for unauthenticated users",
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter by course ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_published",
     *         in="query",
     *         description="Filter by published status",
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
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kursus", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                         @OA\Property(property="kode_kursus", type="string", example="K001")
     *                     ),
     *                     @OA\Property(property="nama_modul", type="string", example="Pendahuluan Data Science"),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="deskripsi", type="string", example="Modul pengantar tentang konsep dasar data science"),
     *                     @OA\Property(property="is_published", type="boolean", example=true),
     *                     @OA\Property(property="total_durasi", type="integer", example=120),
     *                     @OA\Property(property="jumlah_materi", type="integer", example=5),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="is_authenticated", type="boolean", example=false),
     *                 @OA\Property(property="message", type="string", example="Login required to view materials")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching modules")
     *         )
     *     )
     * )
     */
    public function indexNoAuth(Request $request)
    {
        try {
            // Explicitly do NOT load materis for unauthenticated users
            $query = Modul::with(['kursus']);

            // Filter by kursus_id
            if ($request->has('kursus_id')) {
                $query->where('kursus_id', $request->kursus_id);
            }

            // Filter by is_published
            if ($request->has('is_published')) {
                $query->where('is_published', $request->boolean('is_published'));
            }

            // Order by urutan
            $query->orderBy('urutan');

            $moduls = $query->get();

            return ModulResource::collection($moduls)->additional([
                'meta' => [
                    'is_authenticated' => false,
                    'message' => 'Login required to view materials'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching modules: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get list of Modul Kursus - Authenticated
     * 
     * @OA\Get(
     *     path="/api/v1/modul",
     *     tags={"Modul Kursus"},
     *     summary="Get all Modul Kursus (Authenticated)",
     *     description="Returns list of all Modul Kursus WITH materials for authenticated users",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter by course ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_published",
     *         in="query",
     *         description="Filter by published status",
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
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kursus", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                         @OA\Property(property="kode_kursus", type="string", example="K001")
     *                     ),
     *                     @OA\Property(property="nama_modul", type="string", example="Pendahuluan Data Science"),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="deskripsi", type="string", example="Modul pengantar tentang konsep dasar data science"),
     *                     @OA\Property(property="is_published", type="boolean", example=true),
     *                     @OA\Property(property="total_durasi", type="integer", example=120),
     *                     @OA\Property(property="jumlah_materi", type="integer", example=5),
     *                     @OA\Property(property="materis", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="judul", type="string", example="Pengenalan Data Science")
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="is_authenticated", type="boolean", example=true)
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
     *             @OA\Property(property="message", type="string", example="Error fetching modules")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            // Load materis for authenticated users
            $query = Modul::with(['kursus', 'materis']);

            // Filter by kursus_id
            if ($request->has('kursus_id')) {
                $query->where('kursus_id', $request->kursus_id);
            }

            // Filter by is_published
            if ($request->has('is_published')) {
                $query->where('is_published', $request->boolean('is_published'));
            }

            // Order by urutan
            $query->orderBy('urutan');

            $moduls = $query->get();

            return ModulResource::collection($moduls)->additional([
                'meta' => [
                    'is_authenticated' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching modules: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get specific course module by ID - Authenticated
     * 
     * @OA\Get(
     *     path="/api/v1/modul/{id}",
     *     tags={"Modul Kursus"},
     *     summary="Get course module by ID (Authenticated)",
     *     description="Returns specific course module details WITH materials for authenticated users",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Module ID",
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
     *                 @OA\Property(property="kursus", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
     *                     @OA\Property(property="kode_kursus", type="string", example="K001")
     *                 ),
     *                 @OA\Property(property="nama_modul", type="string", example="Pendahuluan Data Science"),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(property="deskripsi", type="string", example="Modul pengantar tentang konsep dasar data science"),
     *                 @OA\Property(property="is_published", type="boolean", example=true),
     *                 @OA\Property(property="total_durasi", type="integer", example=120),
     *                 @OA\Property(property="jumlah_materi", type="integer", example=3),
     *                 @OA\Property(property="materis", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="modul_id", type="integer", example=1),
     *                         @OA\Property(property="judul", type="string", example="Pengenalan Data Science"),
     *                         @OA\Property(property="urutan", type="integer", example=1),
     *                         @OA\Property(property="tipe", type="string", example="video"),
     *                         @OA\Property(property="durasi", type="integer", example=45),
     *                         @OA\Property(property="konten", type="string", example="https://example.com/videos/intro.mp4"),
     *                         @OA\Property(property="is_published", type="boolean", example=true),
     *                         @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                         @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="is_authenticated", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Module not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Module not found")
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
     *             @OA\Property(property="message", type="string", example="Error retrieving module")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            // Load materis for authenticated users
            $modul = Modul::with(['kursus', 'materis'])->find($id);

            if (!$modul) {
                return response()->json(['message' => 'Module not found'], 404);
            }

            return (new ModulResource($modul))->additional([
                'meta' => [
                    'is_authenticated' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving module: ' . $e->getMessage()], 500);
        }
    }

    // /**
    //  * Get specific course module by ID - NO AUTH
    //  * 
    //  * @OA\Get(
    //  *     path="/api/v1/modul/{id}/no-auth",
    //  *     tags={"Modul Kursus"},
    //  *     summary="Get course module by ID (Public - No materials)",
    //  *     description="Returns specific course module details WITHOUT materials for unauthenticated users",
    //  *      @OA\Parameter(
    //  *         name="id",
    //  *         in="path",
    //  *         description="Module ID",
    //  *         required=true,
    //  *         @OA\Schema(type="integer")
    //  *     ),
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="Success",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(
    //  *                 property="data",
    //  *                 type="object",
    //  *                 @OA\Property(property="id", type="integer", example=1),
    //  *                 @OA\Property(property="kursus_id", type="integer", example=1),
    //  *                 @OA\Property(property="kursus", type="object",
    //  *                     @OA\Property(property="id", type="integer", example=1),
    //  *                     @OA\Property(property="judul", type="string", example="Pengantar Data Science"),
    //  *                     @OA\Property(property="kode_kursus", type="string", example="K001")
    //  *                 ),
    //  *                 @OA\Property(property="nama_modul", type="string", example="Pendahuluan Data Science"),
    //  *                 @OA\Property(property="urutan", type="integer", example=1),
    //  *                 @OA\Property(property="deskripsi", type="string", example="Modul pengantar tentang konsep dasar data science"),
    //  *                 @OA\Property(property="is_published", type="boolean", example=true),
    //  *                 @OA\Property(property="total_durasi", type="integer", example=120),
    //  *                 @OA\Property(property="jumlah_materi", type="integer", example=3),
    //  *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
    //  *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
    //  *             ),
    //  *             @OA\Property(property="meta", type="object",
    //  *                 @OA\Property(property="is_authenticated", type="boolean", example=false),
    //  *                 @OA\Property(property="message", type="string", example="Login required to view materials")
    //  *             )
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response=404,
    //  *         description="Module not found",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="message", type="string", example="Module not found")
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response=500,
    //  *         description="Server error",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="message", type="string", example="Error retrieving module")
    //  *         )
    //  *     )
    //  * )
    //  */
    // public function showNoAuth($id)
    // {
    //     try {
    //         // Explicitly do NOT load materis for unauthenticated users
    //         $modul = Modul::with(['kursus'])->find($id);

    //         if (!$modul) {
    //             return response()->json(['message' => 'Module not found'], 404);
    //         }

    //         return (new ModulResource($modul))->additional([
    //             'meta' => [
    //                 'is_authenticated' => false,
    //                 'message' => 'Login required to view materials'
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Error retrieving module: ' . $e->getMessage()], 500);
    //     }
    // }

    /**
     * Reorder Modul Kursus
     * 
     * @OA\Post(
     *     path="/api/v1/modul/reorder",
     *     tags={"Modul Kursus"},
     *     summary="Reorder Modul Kursus",
     *     description="Update the order/sequence of multiple modules at once",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kursus_id","modules"},
     *             @OA\Property(property="kursus_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="modules",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id","urutan"},
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="urutan", type="integer", example=3)
     *                 ),
     *                 example={
     *                     {"id": 1, "urutan": 3},
     *                     {"id": 2, "urutan": 1},
     *                     {"id": 3, "urutan": 2}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Modules reordered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Modules reordered successfully")
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
     *             @OA\Property(property="message", type="string", example="Error reordering modules")
     *         )
     *     )
     * )
     */
    public function reorder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kursus_id' => 'required|exists:kursus,id',
                'modules' => 'required|array',
                'modules.*.id' => 'required|exists:moduls,id',
                'modules.*.urutan' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if all modules belong to the specified kursus
            $moduleIds = collect($request->modules)->pluck('id')->toArray();
            $invalidModules = Modul::whereIn('id', $moduleIds)
                ->where('kursus_id', '!=', $request->kursus_id)
                ->exists();

            if ($invalidModules) {
                return response()->json([
                    'message' => 'Some modules do not belong to the specified kursus.'
                ], 422);
            }

            // Update urutan for each module
            foreach ($request->modules as $module) {
                Modul::where('id', $module['id'])
                    ->update(['urutan' => $module['urutan']]);
            }

            return response()->json([
                'message' => 'Modules reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error reordering modules: ' . $e->getMessage()
            ], 500);
        }
    }
}