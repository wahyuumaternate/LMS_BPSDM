<?php

namespace Modules\AdminInstruktur\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index()
    {
        return view('admininstruktur::profile.index');
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:admin_instrukturs,username,' . $user->id,
            'email' => 'required|email|max:255|unique:admin_instrukturs,email,' . $user->id,
            'nama_lengkap' => 'required|string|max:255',
            'gelar_depan' => 'nullable|string|max:50',
            'gelar_belakang' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:50|unique:admin_instrukturs,nip,' . $user->id,
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'bidang_keahlian' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'nip.unique' => 'NIP sudah digunakan',
            'foto_profil.image' => 'File harus berupa gambar',
            'foto_profil.mimes' => 'Format foto harus: jpeg, png, jpg',
            'foto_profil.max' => 'Ukuran foto maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->except(['foto_profil', '_token', '_method']);

            // Handle foto profil upload
            if ($request->hasFile('foto_profil')) {
                // Hapus foto lama jika ada
                if ($user->foto_profil) {
                    Storage::disk('public')->delete('profile/foto/' . $user->foto_profil);
                }

                $file = $request->file('foto_profil');
                $filename = Str::slug($request->nama_lengkap) . '-' . time() . '.' . $file->getClientOriginalExtension();
                
                $file->storeAs('profile/foto', $filename, 'public');
                
                $data['foto_profil'] = $filename;
            }

            $user->update($data);

            return redirect()->route('profile.index')
                ->with('success', 'Profile berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error memperbarui profile: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the user's password.
     */
public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^()_+\-=\[\]{};:,.<>])[A-Za-z\d@$!%*?&#^()_+\-=\[\]{};:,.<>]{8,}$/'
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
            'new_password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, 1 angka, dan 1 karakter khusus (!@#$%^&*)',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Cek apakah password saat ini benar
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai'])
                ->withInput();
        }

        // Cek apakah password baru sama dengan password lama
        if (Hash::check($request->new_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['new_password' => 'Password baru tidak boleh sama dengan password lama'])
                ->withInput();
        }

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return redirect()->route('profile.index')
                ->with('success', 'Password berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error mengubah password: ' . $e->getMessage())
                ->withInput();
        }
    }
}