<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Entities\AdminInstruktur;
use Modules\Auth\Entities\Peserta;

class AuthController extends Controller
{
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $admin = AdminInstruktur::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $admin,
                'token' => $admin->createToken('admin-token')->plainTextToken,
                'token_type' => 'Bearer'
            ]
        ]);
    }
    public function loginPeserta(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $peserta = Peserta::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();
        if (!$peserta || !Hash::check($request->password, $peserta->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $peserta,
                'token' => $peserta->createToken('peserta-token')->plainTextToken,
                'token_type' => 'Bearer'
            ]
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    }
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $rules = [
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:' . ($user instanceof AdminInstruktur ? 'admin_instruktur' : 'peserta') . ',email,' . $user->id,
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ];
        if ($user instanceof Peserta) {
            $rules['pangkat_golongan'] = 'nullable|string|max:50';
            $rules['jabatan'] = 'nullable|string|max:100';
            $rules['pendidikan_terakhir'] = 'nullable|in:sma,d3,s1,s2,s3';
        }
        if ($user instanceof AdminInstruktur) {
            $rules['gelar_depan'] = 'nullable|string|max:20';
            $rules['gelar_belakang'] = 'nullable|string|max:50';
            $rules['bidang_keahlian'] = 'nullable|string';
        }
        $request->validate($rules);
        $user->update($request->only(array_keys($rules)));
        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak cocok.'],
            ]);
        }
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah'
        ]);
    }
}
