<?php

namespace Modules\AdminInstruktur\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\AdminInstruktur\Transformers\AdminInstrukturResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminInstrukturController extends Controller
{
    public function index()
    {
        $adminInstrukturs = AdminInstruktur::paginate(15);
        return AdminInstrukturResource::collection($adminInstrukturs);
    }

    public function store(Request $request)
    {
        // Only super_admin can create new admin_instruktur
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized. Only Super Admin can create new admin/instruktur.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:admin_instrukturs',
            'email' => 'required|string|email|max:255|unique:admin_instrukturs',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,instruktur',
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:admin_instrukturs',
            'gelar_depan' => 'nullable|string|max:20',
            'gelar_belakang' => 'nullable|string|max:20',
            'bidang_keahlian' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('foto_profil', 'password');
        $data['password'] = Hash::make($request->password);

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
    }

    public function show($id)
    {
        $admin = AdminInstruktur::findOrFail($id);
        return new AdminInstrukturResource($admin);
    }

    public function update(Request $request, $id)
    {
        $admin = AdminInstruktur::findOrFail($id);

        // Only super_admin can update any admin/instruktur, while normal admin can only update their own account
        if (!$request->user()->isSuperAdmin() && $request->user()->id != $id) {
            return response()->json(['message' => 'Unauthorized. You can only update your own account.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|required|string|max:255|unique:admin_instrukturs,username,' . $id,
            'email' => 'sometimes|required|string|email|max:255|unique:admin_instrukturs,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|required|in:super_admin,instruktur',
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'nip' => 'nullable|string|unique:admin_instrukturs,nip,' . $id,
            'gelar_depan' => 'nullable|string|max:20',
            'gelar_belakang' => 'nullable|string|max:20',
            'bidang_keahlian' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['foto_profil', 'password', 'role']);

        // Only super_admin can change role
        if ($request->filled('role') && $request->user()->isSuperAdmin()) {
            $data['role'] = $request->role;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
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
    }

    public function destroy(Request $request, $id)
    {
        // Only super_admin can delete admin/instruktur
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized. Only Super Admin can delete admin/instruktur.'], 403);
        }

        $admin = AdminInstruktur::findOrFail($id);

        // Prevent deleting the last super_admin
        if ($admin->isSuperAdmin() && AdminInstruktur::where('role', 'super_admin')->count() <= 1) {
            return response()->json(['message' => 'Cannot delete the last Super Admin account.'], 422);
        }

        // Soft delete karena menggunakan SoftDeletes trait
        $admin->delete();

        return response()->json([
            'message' => 'Admin/Instruktur deleted successfully'
        ]);
    }
}
