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
     *     name="Admin Management",
     *     description="Endpoint terkait pengelolaan user (daftar, edit, hapus, dsb)"
     * )
     */

    /**
     * Get paginated list of admins and instructors
     * 
     * 
     * @OA\Get(
     *     path="/api/v1/admin",
     *     tags={"Admin Management"},
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
     * Create a new admin or instructor
     * 
     * @OA\Post(
     *     path="/api/v1/admin",
     *     tags={"Admin Management"},
     *     summary="Create new admin or instructor",
     *     description="Create a new admin or instructor account (Super Admin only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"username","email","password","role","nama_lengkap"},
     *                 @OA\Property(property="username", type="string", example="instructor1"),
     *                 @OA\Property(property="email", type="string", format="email", example="instructor1@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="password123"),
     *                 @OA\Property(property="role", type="string", enum={"super_admin", "instruktur"}, example="instruktur"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Instructor Name"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="gelar_depan", type="string", example="Dr."),
     *                 @OA\Property(property="gelar_belakang", type="string", example="M.Pd"),
     *                 @OA\Property(property="bidang_keahlian", type="string", example="Matematika"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Pendidikan No. 123"),
     *                 @OA\Property(property="foto_profil", type="file", format="binary")
     *             )
     *         ),
     *         @OA\JsonContent(
     *             required={"username","email","password","role","nama_lengkap"},
     *             @OA\Property(property="username", type="string", example="instructor1"),
     *             @OA\Property(property="email", type="string", format="email", example="instructor1@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"super_admin", "instruktur"}, example="instruktur"),
     *             @OA\Property(property="nama_lengkap", type="string", example="Instructor Name"),
     *             @OA\Property(property="nip", type="string", example="198501012010011001"),
     *             @OA\Property(property="gelar_depan", type="string", example="Dr."),
     *             @OA\Property(property="gelar_belakang", type="string", example="M.Pd"),
     *             @OA\Property(property="bidang_keahlian", type="string", example="Matematika"),
     *             @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Pendidikan No. 123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Admin/Instructor created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin/Instruktur created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="username", type="string", example="instructor1"),
     *                 @OA\Property(property="email", type="string", example="instructor1@example.com"),
     *                 @OA\Property(property="role", type="string", example="instruktur"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Instructor Name"),
     *                 @OA\Property(property="nama_dengan_gelar", type="string", example="Dr. Instructor Name, M.Pd"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="gelar_depan", type="string", example="Dr."),
     *                 @OA\Property(property="gelar_belakang", type="string", example="M.Pd"),
     *                 @OA\Property(property="bidang_keahlian", type="string", example="Matematika"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Pendidikan No. 123"),
     *                 @OA\Property(property="foto_profil", type="string", example="1698304599.jpg"),
     *                 @OA\Property(property="email_verified_at", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Only Super Admin can create new admin/instruktur.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Only super_admin can create new admin_instruktur
            if (!$request->user() || !$request->user()->isSuperAdmin()) {
                return response()->json(['message' => 'Unauthorized. Only Super Admin can create new admin/instruktur.'], 403);
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
                'username' => 'required|string|max:255|unique:admin_instrukturs',
                'email' => 'required|string|email|max:255|unique:admin_instrukturs',
                'password' => 'required|string|min:8',
                'role' => 'required|in:super_admin,instruktur',
                'nama_lengkap' => 'required|string|max:255',
                'nip' => 'nullable|string|unique:admin_instrukturs',
                'gelar_depan' => 'nullable|string|max:255',
                'gelar_belakang' => 'nullable|string|max:255',
                'bidang_keahlian' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:255',
                'alamat' => 'nullable|string',
                'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'email_verified_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $input;
            if (isset($data['foto_profil']) && !$request->hasFile('foto_profil')) {
                unset($data['foto_profil']);
            }
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Upload foto profil jika ada
            if ($request->hasFile('foto_profil')) {
                $file = $request->file('foto_profil');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/profile/foto', $filename);
                $data['foto_profil'] = $filename;
            }

            $admin = AdminInstruktur::create($data);

            return response()->json([
                'message' => 'Admin/Instruktur created successfully',
                'data' => new AdminInstrukturResource($admin)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating admin/instructor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific admin or instructor by ID
     * 
     * @OA\Get(
     *     path="/api/v1/admin/{id}",
     *     tags={"Admin Management"},
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

    /**
     * Update admin or instructor
     * 
     * @OA\Put(
     *     path="/api/v1/admin/{id}",
     *     tags={"Admin Management"},
     *     summary="Update admin or instructor",
     *     description="Update admin or instructor information (Super Admin can update any account, regular admin can only update their own)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin/Instructor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="username", type="string", example="instructor1_updated"),
     *                 @OA\Property(property="email", type="string", format="email", example="instructor1_updated@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *                 @OA\Property(property="role", type="string", enum={"super_admin", "instruktur"}, example="instruktur"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Updated Instructor Name"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="gelar_depan", type="string", example="Prof. Dr."),
     *                 @OA\Property(property="gelar_belakang", type="string", example="M.Pd, Ph.D"),
     *                 @OA\Property(property="bidang_keahlian", type="string", example="Matematika Terapan"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Pendidikan Baru No. 456"),
     *                 @OA\Property(property="foto_profil", type="file", format="binary")
     *             )
     *         ),
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="instructor1_updated"),
     *             @OA\Property(property="email", type="string", format="email", example="instructor1_updated@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="role", type="string", enum={"super_admin", "instruktur"}, example="instruktur"),
     *             @OA\Property(property="nama_lengkap", type="string", example="Updated Instructor Name"),
     *             @OA\Property(property="nip", type="string", example="198501012010011001"),
     *             @OA\Property(property="gelar_depan", type="string", example="Prof. Dr."),
     *             @OA\Property(property="gelar_belakang", type="string", example="M.Pd, Ph.D"),
     *             @OA\Property(property="bidang_keahlian", type="string", example="Matematika Terapan"),
     *             @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Pendidikan Baru No. 456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin/Instructor updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin/Instruktur updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="username", type="string", example="instructor1_updated"),
     *                 @OA\Property(property="email", type="string", example="instructor1_updated@example.com"),
     *                 @OA\Property(property="role", type="string", example="instruktur"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Updated Instructor Name"),
     *                 @OA\Property(property="nama_dengan_gelar", type="string", example="Prof. Dr. Updated Instructor Name, M.Pd, Ph.D"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="gelar_depan", type="string", example="Prof. Dr."),
     *                 @OA\Property(property="gelar_belakang", type="string", example="M.Pd, Ph.D"),
     *                 @OA\Property(property="bidang_keahlian", type="string", example="Matematika Terapan"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Pendidikan Baru No. 456"),
     *                 @OA\Property(property="foto_profil", type="string", example="1698304799.jpg"),
     *                 @OA\Property(property="email_verified_at", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 07:13:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. You can only update your own account.")
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
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            // Find admin or return 404 if not found
            try {
                $admin = AdminInstruktur::findOrFail($id);
            } catch (ModelNotFoundException $e) {
                return response()->json(['message' => 'Admin/Instructor not found'], 404);
            }

            // Only super_admin can update any admin/instruktur, while normal admin can only update their own account
            if (!$request->user()->isSuperAdmin() && $request->user()->id != $id) {
                return response()->json(['message' => 'Unauthorized. You can only update your own account.'], 403);
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
                'username' => 'sometimes|required|string|max:255|unique:admin_instrukturs,username,' . $id,
                'email' => 'sometimes|required|string|email|max:255|unique:admin_instrukturs,email,' . $id,
                'password' => 'nullable|string|min:8',
                'role' => 'sometimes|required|in:super_admin,instruktur',
                'nama_lengkap' => 'sometimes|required|string|max:255',
                'nip' => 'nullable|string|unique:admin_instrukturs,nip,' . $id,
                'gelar_depan' => 'nullable|string|max:255',
                'gelar_belakang' => 'nullable|string|max:255',
                'bidang_keahlian' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:255',
                'alamat' => 'nullable|string',
                'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'email_verified_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $input;
            if (isset($data['foto_profil']) && !$request->hasFile('foto_profil')) {
                unset($data['foto_profil']);
            }

            // Remove password and role from data if they're not being updated
            if (!isset($data['password']) || empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }

            // Only super_admin can change role
            if (isset($data['role']) && !$request->user()->isSuperAdmin()) {
                unset($data['role']);
            }

            // Upload foto profil jika ada
            if ($request->hasFile('foto_profil')) {
                // Hapus foto lama jika ada
                if ($admin->foto_profil) {
                    Storage::delete('public/profile/foto/' . $admin->foto_profil);
                }

                $file = $request->file('foto_profil');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/profile/foto', $filename);
                $data['foto_profil'] = $filename;
            }

            $admin->update($data);

            return response()->json([
                'message' => 'Admin/Instruktur updated successfully',
                'data' => new AdminInstrukturResource($admin)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating admin/instructor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete admin or instructor
     * 
     * @OA\Delete(
     *     path="/api/v1/admin/{id}",
     *     tags={"Admin Management"},
     *     summary="Delete admin or instructor",
     *     description="Delete admin or instructor account (Super Admin only)",
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
     *         description="Admin/Instructor deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin/Instruktur deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Only Super Admin can delete admin/instruktur.")
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
     *         response=422,
     *         description="Cannot delete the last Super Admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete the last Super Admin account.")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Only super_admin can delete admin/instruktur
            if (!$request->user()->isSuperAdmin()) {
                return response()->json(['message' => 'Unauthorized. Only Super Admin can delete admin/instruktur.'], 403);
            }

            // Find admin or return 404 if not found
            try {
                $admin = AdminInstruktur::findOrFail($id);
            } catch (ModelNotFoundException $e) {
                return response()->json(['message' => 'Admin/Instructor not found'], 404);
            }

            // Prevent deleting the last super_admin
            if ($admin->isSuperAdmin() && AdminInstruktur::where('role', 'super_admin')->count() <= 1) {
                return response()->json(['message' => 'Cannot delete the last Super Admin account.'], 422);
            }

            // Soft delete karena menggunakan SoftDeletes trait
            $admin->delete();

            return response()->json([
                'message' => 'Admin/Instruktur deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting admin/instructor: ' . $e->getMessage()], 500);
        }
    }
}
