<?php

namespace Modules\AdminInstruktur\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\AdminInstruktur\Transformers\AdminInstrukturResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminInstrukturController extends Controller
{
    /**
     * @OA\Tag(
     *     name="Admin & Instruktur",
     *     description="Endpoint terkait pengelolaan user (daftar, edit, hapus, dsb)"
     * )
     */

    /**
     * Get paginated list of admins and instructors
     * 
     * 
     * @OA\Get(
     *     path="/api/v1/admin",
     *     tags={"Admin & Instruktur"},
     *     summary="Get all admins and instructors",
     *     description="Returns paginated list of all admins and instructors",
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
     *         name="search",
     *         in="query",
     *         description="Search term to filter results (searches username, email, nama_lengkap and nip)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by role (super_admin or instruktur)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"super_admin", "instruktur"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="username", type="string", example="superadmin"),
     *                     @OA\Property(property="email", type="string", example="superadmin@example.com"),
     *                     @OA\Property(property="role", type="string", example="super_admin"),
     *                     @OA\Property(property="nama_lengkap", type="string", example="Super Admin"),
     *                     @OA\Property(property="nama_dengan_gelar", type="string", example="Super Admin"),
     *                     @OA\Property(property="nip", type="string", example=null),
     *                     @OA\Property(property="gelar_depan", type="string", example=null),
     *                     @OA\Property(property="gelar_belakang", type="string", example=null),
     *                     @OA\Property(property="bidang_keahlian", type="string", example=null),
     *                     @OA\Property(property="no_telepon", type="string", example=null),
     *                     @OA\Property(property="alamat", type="string", example=null),
     *                     @OA\Property(property="foto_profil", type="string", example=null),
     *                     @OA\Property(property="email_verified_at", type="string", example=null),
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
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            // Get the per_page parameter from the request, default to 15 if not specified
            $perPage = $request->input('per_page', 15);

            // Constrain per_page to be between 5 and 100 to prevent abuse
            $perPage = max(5, min(100, (int)$perPage));

            // Build the query
            $query = AdminInstruktur::query();

            // Add search functionality if search parameter is provided
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            }

            // Filter by role if specified
            if ($request->has('role') && in_array($request->role, ['super_admin', 'instruktur'])) {
                $query->where('role', $request->role);
            }

            // Execute the query with pagination
            $adminInstrukturs = $query->paginate($perPage);

            // Check if we have results
            if ($adminInstrukturs->isEmpty() && $request->has('page') && $request->input('page', 1) > 1) {
                return response()->json([
                    'message' => 'No more records found',
                    'data' => [],
                    'meta' => [
                        'current_page' => (int)$request->input('page', 1),
                        'per_page' => $perPage,
                        'total' => $adminInstrukturs->total()
                    ]
                ], 200);
            }

            return AdminInstrukturResource::collection($adminInstrukturs);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching admin/instructors: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Get specific admin or instructor by ID
     * 
     * @OA\Get(
     *     path="/api/v1/admin/{id}",
     *     tags={"Admin & Instruktur"},
     *     summary="Get admin or instructor by ID",
     *     description="Returns specific admin or instructor details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin/Instructor ID",
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
     *                 @OA\Property(property="username", type="string", example="superadmin"),
     *                 @OA\Property(property="email", type="string", example="superadmin@example.com"),
     *                 @OA\Property(property="role", type="string", example="super_admin"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Super Admin"),
     *                 @OA\Property(property="nama_dengan_gelar", type="string", example="Super Admin"),
     *                 @OA\Property(property="nip", type="string", example=null),
     *                 @OA\Property(property="gelar_depan", type="string", example=null),
     *                 @OA\Property(property="gelar_belakang", type="string", example=null),
     *                 @OA\Property(property="bidang_keahlian", type="string", example=null),
     *                 @OA\Property(property="no_telepon", type="string", example=null),
     *                 @OA\Property(property="alamat", type="string", example=null),
     *                 @OA\Property(property="foto_profil", type="string", example=null),
     *                 @OA\Property(property="email_verified_at", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Admin/Instructor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin/Instructor not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        // Remove the abort(404) line
        try {
            $admin = AdminInstruktur::findOrFail($id);
            return new AdminInstrukturResource($admin);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Admin/Instructor not found'], 404);
        }
    }

 
}
