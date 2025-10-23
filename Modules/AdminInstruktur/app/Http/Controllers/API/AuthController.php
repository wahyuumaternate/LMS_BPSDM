<?php

namespace Modules\AdminInstruktur\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\AdminInstruktur\Transformers\AdminInstrukturResource;

class AuthController extends Controller
{
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

        $admin = Auth::guard('admin_instruktur')->user();
        $token = $admin->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => new AdminInstrukturResource($admin),
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
            'data' => new AdminInstrukturResource($request->user())
        ]);
    }
}
