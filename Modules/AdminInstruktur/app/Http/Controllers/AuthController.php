<?php

namespace Modules\AdminInstruktur\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\AdminInstruktur\Entities\AdminInstruktur;

class AuthController extends Controller
{
    /**
     * Display the login page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLoginForm()
    {
        return view('admininstruktur::auth.login');
    }

    /**
     * Process the login request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $remember = $request->has('remember');

        if (Auth::guard('admin_instruktur')->attempt($credentials, $remember)) {
            // Authentication successful
            $request->session()->regenerate();

            // Get the authenticated user
            $admin = Auth::guard('admin_instruktur')->user();

            // // Redirect based on role
            // if ($admin->role === 'super_admin' || $admin->role === 'admin') {
            //     return redirect()->route('admin.dashboard');
            // } else {
            // }
            return redirect()->route('dashboard');
        }

        // Authentication failed
        return redirect()->back()
            ->withErrors(['email' => 'Email atau password yang dimasukkan salah.'])
            ->withInput($request->except('password'));
    }

    /**
     * Process the logout request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('admin_instruktur')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Display the user's profile
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function profile()
    {
        $user = Auth::guard('admin_instruktur')->user();

        return view('admininstruktur::profile.index', compact('user'));
    }

    /**
     * Show the form for editing profile
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function editProfile()
    {
        $user = Auth::guard('admin_instruktur')->user();

        return view('admininstruktur::profile.edit', compact('user'));
    }

    /**
     * Update the user's profile
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('admin_instruktur')->user();

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admin_instruktur,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:admin_instruktur,email,' . $user->id,
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'gelar_depan' => 'nullable|string|max:50',
            'gelar_belakang' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:50',
            'bidang_keahlian' => 'nullable|string|max:255',
            'foto_profil' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update profile data
        $user->nama_lengkap = $request->nama_lengkap;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->no_telepon = $request->no_telepon;
        $user->alamat = $request->alamat;
        $user->gelar_depan = $request->gelar_depan;
        $user->gelar_belakang = $request->gelar_belakang;
        $user->nip = $request->nip;
        $user->bidang_keahlian = $request->bidang_keahlian;

        // Generate nama_dengan_gelar
        $namaLengkap = trim($request->nama_lengkap);
        $gelarDepan = $request->gelar_depan ? trim($request->gelar_depan) . ' ' : '';
        $gelarBelakang = $request->gelar_belakang ? ', ' . trim($request->gelar_belakang) : '';

        $user->nama_dengan_gelar = $gelarDepan . $namaLengkap . $gelarBelakang;

        // Handle photo upload if provided
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = 'profile-' . time() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('public/uploads/profiles', $filename);
            $user->foto_profil = 'uploads/profiles/' . $filename;
        }

        $user->save();

        return redirect()->route('admin.profile')
            ->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Show the change password form
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function showChangePasswordForm()
    {
        return view('admininstruktur::auth.change-password');
    }

    /**
     * Process password change
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $user = Auth::guard('admin_instruktur')->user();

        // Verify current password
        if (!password_verify($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini tidak sesuai']);
        }

        // Update password
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect()->route('admin.profile')
            ->with('success', 'Password berhasil diubah');
    }
}
