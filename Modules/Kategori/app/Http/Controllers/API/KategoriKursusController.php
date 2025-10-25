<?php

namespace Modules\Kategori\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kategori\Entities\KategoriKursus;
use Modules\Kategori\Transformers\KategoriKursusResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KategoriKursusController extends Controller
{
    /**
     * Get all Kategori Kursus
     * 
     * @OA\Get(
     *     path="/api/v1/kategori-kursus",
     *     tags={"Kategori Kursus"},
     *     summary="Get all Kategori Kursus",
     *     description="Returns list of all Kategori Kursus ordered by sequence",
     *     security={{"sanctum":{}}},
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
     *             @OA\Property(property="message", type="string", example="Error fetching categories")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $kategori = KategoriKursus::orderBy('urutan')->get();
            return KategoriKursusResource::collection($kategori);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching categories: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new course category
     * 
     * @OA\Post(
     *     path="/api/v1/kategori-kursus",
     *     tags={"Kategori Kursus"},
     *     summary="Create new course category",
     *     description="Creates a new course category",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama_kategori"},
     *             @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *             @OA\Property(property="slug", type="string", example="teknologi-informasi"),
     *             @OA\Property(property="deskripsi", type="string", example="Kategori untuk kursus-kursus terkait teknologi informasi"),
     *             @OA\Property(property="icon", type="string", example="fa-laptop-code"),
     *             @OA\Property(property="urutan", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kategori kursus created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi"),
     *                 @OA\Property(property="slug", type="string", example="teknologi-informasi"),
     *                 @OA\Property(property="deskripsi", type="string", example="Kategori untuk kursus-kursus terkait teknologi informasi"),
     *                 @OA\Property(property="icon", type="string", example="fa-laptop-code"),
     *                 @OA\Property(property="urutan", type="integer", example=1),
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
     *             @OA\Property(property="message", type="string", example="Error creating category")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Get input data - try JSON first, then fallback to request->all()
            $input = $request->json()->all();
            if (empty($input) && $request->isJson()) {
                $input = json_decode($request->getContent(), true);
            }
            if (empty($input)) {
                $input = $request->all();
            }

            // Validasi input
            $validator = Validator::make($input, [
                'nama_kategori' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:kategori_kursus',
                'deskripsi' => 'nullable|string',
                'icon' => 'nullable|string|max:255',
                'urutan' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $input;

            // Generate slug jika tidak disediakan
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['nama_kategori']);
            }

            $kategori = KategoriKursus::create($data);

            return response()->json([
                'message' => 'Kategori kursus created successfully',
                'data' => new KategoriKursusResource($kategori)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating category',
                'error' => $e->getMessage()
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
     *     description="Returns specific course category details",
     *     security={{"sanctum":{}}},
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
     *             @OA\Property(property="message", type="string", example="Error retrieving category")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            // Find category or return 404 if not found
            $kategori = KategoriKursus::find($id);

            if (!$kategori) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            return new KategoriKursusResource($kategori);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update course category
     * 
     * @OA\Put(
     *     path="/api/v1/kategori-kursus/{id}",
     *     tags={"Kategori Kursus"},
     *     summary="Update course category",
     *     description="Update an existing course category",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi Updated"),
     *             @OA\Property(property="slug", type="string", example="teknologi-informasi-updated"),
     *             @OA\Property(property="deskripsi", type="string", example="Deskripsi yang telah diperbarui"),
     *             @OA\Property(property="icon", type="string", example="fa-code"),
     *             @OA\Property(property="urutan", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kategori kursus updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_kategori", type="string", example="Teknologi Informasi Updated"),
     *                 @OA\Property(property="slug", type="string", example="teknologi-informasi-updated"),
     *                 @OA\Property(property="deskripsi", type="string", example="Deskripsi yang telah diperbarui"),
     *                 @OA\Property(property="icon", type="string", example="fa-code"),
     *                 @OA\Property(property="urutan", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 07:13:19")
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
     *             @OA\Property(property="message", type="string", example="Error updating category")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            // Find category or return 404 if not found
            $kategori = KategoriKursus::find($id);

            if (!$kategori) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            // Get input data - try JSON first, then fallback to request->all()
            $input = $request->json()->all();
            if (empty($input) && $request->isJson()) {
                $input = json_decode($request->getContent(), true);
            }
            if (empty($input)) {
                $input = $request->all();
            }

            $validator = Validator::make($input, [
                'nama_kategori' => 'sometimes|required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:kategori_kursus,slug,' . $id,
                'deskripsi' => 'nullable|string',
                'icon' => 'nullable|string|max:255',
                'urutan' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Update slug if nama_kategori is updated but slug isn't provided
            if (isset($input['nama_kategori']) && !isset($input['slug'])) {
                $input['slug'] = Str::slug($input['nama_kategori']);
            }

            $kategori->update($input);

            return response()->json([
                'message' => 'Kategori kursus updated successfully',
                'data' => new KategoriKursusResource($kategori)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete course category
     * 
     * @OA\Delete(
     *     path="/api/v1/kategori-kursus/{id}",
     *     tags={"Kategori Kursus"},
     *     summary="Delete course category",
     *     description="Delete a course category if it has no related courses",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kategori kursus deleted successfully")
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
     *         response=422,
     *         description="Cannot delete category with related courses",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete kategori. It has related kursus.")
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
     *             @OA\Property(property="message", type="string", example="Error deleting category")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            // Find category or return 404 if not found
            $kategori = KategoriKursus::find($id);

            if (!$kategori) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            // Check if kategori has related kursus
            if ($kategori->kursus()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete kategori. It has related kursus.'
                ], 422);
            }

            $kategori->delete();

            return response()->json([
                'message' => 'Kategori kursus deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
