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
     * Login admin
     * 
     * @OA\Post(
     *     path="/api/v1/admin/login",
     *     tags={"Admin Authentication"},
     *     summary="Login admin",
     *     description="Login using email or username and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login","password"},
     *             @OA\Property(property="login", type="string", example="superadmin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
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
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if login is email or username
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        $credentials = [
            $loginType => $request->login,
            'password' => $request->password,
        ];

        if (!Auth::guard('admin_instruktur')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Ambil user yang login
        $admin = Auth::guard('admin_instruktur')->user();

        // Buat token Sanctum dengan guard yang benar
        $token = $admin->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

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
     *     tags={"Admin Authentication"},
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
     *     tags={"Admin Authentication"},
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
