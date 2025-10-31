<?php

namespace Modules\AdminInstruktur\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\AdminInstruktur\Transformers\AdminInstrukturResource;

class AuthController extends Controller
{
    /**
     * Create authentication token
     *
     * @param \Modules\AdminInstruktur\Entities\AdminInstruktur $user
     * @return string
     */
    private function createAuthToken($user)
    {
        // Delete previous tokens (optional - for single session)
        $user->tokens()->delete();

        // Create new token with expiry (30 days)
        return $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;
    }

    /**
     * @OA\Tag(
     *     name="Admin & Instruktur",
     *     description="Endpoint terkait pengelolaan user (daftar, edit, hapus, dsb)"
     * )
     */

    /**
     * Login admin
     * 
     * @OA\Post(
     *     path="/api/v1/admin/login",
     *     tags={"Admin & Instruktur"},
     *     summary="Login admin",
     *     description="Login using email and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="superadmin@example.com", description="Email address"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="User password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="superadmin"),
     *                 @OA\Property(property="email", type="string", example="superadmin@example.com"),
     *                 @OA\Property(property="role", type="string", example="super_admin"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Super Admin"),
     *                 @OA\Property(property="nama_dengan_gelar", type="string", example="Dr. Super Admin, M.Kom"),
     *                 @OA\Property(property="nip", type="string", example="199001012020121001"),
     *                 @OA\Property(property="gelar_depan", type="string", example="Dr."),
     *                 @OA\Property(property="gelar_belakang", type="string", example="M.Kom"),
     *                 @OA\Property(property="bidang_keahlian", type="string", example="Machine Learning"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123, Jakarta"),
     *                 @OA\Property(property="foto_profil", type="string", example=null),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             ),
     *             @OA\Property(property="token", type="string", example="3|Bvle2fJqOMe8hHLmjocdRaBkMVQaYz0PX9Sf9Ztff7ae3ab3")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
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
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (!Auth::guard('admin_instruktur')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Ambil user yang login
        $admin = Auth::guard('admin_instruktur')->user();

        // Gunakan method token creation 
        $token = $this->createAuthToken($admin);

        return response()->json([
            'message' => 'Login successful',
            'data' => new AdminInstrukturResource($admin),
            'token' => $token,
        ]);
    }
    /**
     * Logout admin
     * 
     * @OA\Post(
     *     path="/api/v1/admin/logout",
     *     tags={"Admin & Instruktur"},
     *     summary="Logout admin",
     *     description="Logout and revoke current access token",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
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
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user('sanctum')->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated admin profile
     * 
     * @OA\Get(
     *     path="/api/v1/admin/me",
     *     tags={"Admin & Instruktur"},
     *     summary="Get authenticated admin profile",
     *     description="Get current authenticated user information",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
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
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
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
    public function me(Request $request)
    {
        return response()->json([
            'data' => new AdminInstrukturResource($request->user('sanctum'))
        ]);
    }
}
