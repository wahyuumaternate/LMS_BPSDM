<?php

namespace Modules\Peserta\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Peserta\Entities\Peserta;
use Modules\Peserta\Transformers\PesertaResource;

class AuthController extends Controller
{
    /**
     * @OA\Tag(
     *     name="Peserta Management",
     *     description="Endpoint terkait pengelolaan user (daftar, edit, hapus, dsb)"
     * )
     */
    /**
     * Register peserta
     * 
     * @OA\Post(
     *     path="/api/v1/peserta/register",
     *     tags={"Peserta Management"},
     *     summary="Register new peserta",
     *     description="Register a new peserta account",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"opd_id","username","email","password","password_confirmation","nama_lengkap","status_kepegawaian"},
     *             @OA\Property(property="opd_id", type="integer", example=1, description="ID of OPD"),
     *             @OA\Property(property="username", type="string", example="johndoe", description="Unique username"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Unique email address"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Password (min 8 characters)"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123", description="Password confirmation"),
     *             @OA\Property(property="nama_lengkap", type="string", example="John Doe", description="Full name"),
     *             @OA\Property(property="nip", type="string", example="199001012020121001", description="NIP (optional)"),
     *             @OA\Property(property="status_kepegawaian", type="string", enum={"pns","pppk","kontrak"}, example="pns", description="Employment status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Peserta registered successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="nip", type="string", example="199001012020121001"),
     *                 @OA\Property(property="status_kepegawaian", type="string", example="pns"),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             ),
     *             @OA\Property(property="token", type="string", example="4|Bvle2fJqOMe8hHLmjocdRaBkMVQaYz0PX9Sf9Ztff7ae3ab3")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email has already been taken.")
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="array",
     *                     @OA\Items(type="string", example="The username has already been taken.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'opd_id' => 'required|exists:opds,id',
            'username' => 'required|string|max:255|unique:pesertas',
            'email' => 'required|string|email|max:255|unique:pesertas',
            'password' => 'required|string|min:8|confirmed',
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:pesertas',
            'status_kepegawaian' => 'required|in:pns,pppk,kontrak',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $peserta = Peserta::create([
            'opd_id' => $request->opd_id,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama_lengkap' => $request->nama_lengkap,
            'nip' => $request->nip,
            'status_kepegawaian' => $request->status_kepegawaian,
        ]);

        $token = $peserta->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Peserta registered successfully',
            'data' => new PesertaResource($peserta),
            'token' => $token,
        ], 201);
    }

    /**
     * Login peserta
     * 
     * @OA\Post(
     *     path="/api/v1/peserta/login",
     *     tags={"Peserta Management"},
     *     summary="Login peserta",
     *     description="Login using email, username, or NIP with password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login","password"},
     *             @OA\Property(property="login", type="string", example="john@example.com", description="Email, username, or NIP"),
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
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="nip", type="string", example="199001012020121001"),
     *                 @OA\Property(property="status_kepegawaian", type="string", example="pns"),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             ),
     *             @OA\Property(property="token", type="string", example="4|Bvle2fJqOMe8hHLmjocdRaBkMVQaYz0PX9Sf9Ztff7ae3ab3")
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

        // Tentukan tipe login: email, username, atau NIP
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : (is_numeric($request->login) ? 'nip' : 'username');

        // Ambil user berdasarkan tipe login
        $peserta = \Modules\Peserta\Entities\Peserta::where($loginType, $request->login)->first();

        if (!$peserta || !Hash::check($request->password, $peserta->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Opsional: hapus token lama supaya satu sesi saja
        $peserta->tokens()->delete();

        // Buat token Sanctum baru
        $token = $peserta->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => new \Modules\Peserta\Transformers\PesertaResource($peserta),
            'token' => $token,
        ]);
    }



    /**
     * Logout peserta
     * 
     * @OA\Post(
     *     path="/api/v1/peserta/logout",
     *     tags={"Peserta Management"},
     *     summary="Logout peserta",
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
        $request->user('sanctum')->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated peserta profile
     * 
     * @OA\Get(
     *     path="/api/v1/peserta/me",
     *     tags={"Peserta Management"},
     *     summary="Get authenticated peserta profile",
     *     description="Get current authenticated peserta information",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="nip", type="string", example="199001012020121001"),
     *                 @OA\Property(property="status_kepegawaian", type="string", example="pns"),
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
            'data' => new PesertaResource($request->user('sanctum'))
        ]);
    }
}
