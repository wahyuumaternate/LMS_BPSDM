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

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if login is email, username, or nip
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : (is_numeric($request->login) ? 'nip' : 'username');

        $credentials = [
            $loginType => $request->login,
            'password' => $request->password,
        ];

        if (!Auth::guard('peserta')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $peserta = Auth::guard('peserta')->user();
        $token = $peserta->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => new PesertaResource($peserta),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'data' => new PesertaResource($request->user())
        ]);
    }
}
