<?php

namespace Modules\AdminInstruktur\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminInstrukturController extends Controller
{
    /**
     * Display a listing of admins and instructors
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

            return view('admininstruktur::index', compact('adminInstrukturs'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error fetching admin/instructors: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new admin or instructor
     */
    public function create()
    {
        // Only super_admin can create new admin_instruktur
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only Super Admin can create new admin/instruktur.');
        }

        return view('admininstruktur::create');
    }

    /**
     * Store a newly created admin or instructor
     */
    public function store(Request $request)
    {
        try {
            // Only super_admin can create new admin_instruktur
            if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
                return redirect()->back()->with('error', 'Unauthorized. Only Super Admin can create new admin/instruktur.');
            }

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|unique:admin_instrukturs',
                'email' => 'required|string|email|max:255|unique:admin_instrukturs',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^()_+\-=\[\]{};:,.<>])[A-Za-z\d@$!%*?&#^()_+\-=\[\]{};:,.<>]{8,}$/'
                ],
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
            ], [
                'password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, 1 angka, dan 1 karakter khusus (!@#$%^&*)',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except(['foto_profil']);
            $data['password'] = Hash::make($request->password);

            // Upload foto profil jika ada
            if ($request->hasFile('foto_profil')) {
                try {
                    $file = $request->file('foto_profil');
                    
                    // Generate unique filename dengan user ID untuk menghindari konflik
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    
                    // Simpan file
                    $path = $file->storeAs('profile/foto', $filename);
                    
                    if ($path) {
                        $data['foto_profil'] = $filename;
                        Log::info('Photo uploaded successfully: ' . $filename);
                    } else {
                        Log::error('Failed to upload photo');
                    }
                } catch (\Exception $e) {
                    Log::error('Error uploading photo: ' . $e->getMessage());
                    return redirect()->back()
                        ->with('error', 'Error uploading photo: ' . $e->getMessage())
                        ->withInput();
                }
            }

            AdminInstruktur::create($data);

            return redirect()->route('admin.index')->with('success', 'Admin/Instruktur created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating admin/instructor: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating admin/instructor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified admin or instructor
     */
    public function show($id)
    {
        try {
            $admin = AdminInstruktur::findOrFail($id);
            return view('admininstruktur::show', compact('admin'));
        } catch (ModelNotFoundException $e) {
            abort(404, 'Admin/Instructor not found');
        }
    }

    /**
     * Show the form for editing the specified admin or instructor
     */
    public function edit($id)
    {
        try {
            $admin = AdminInstruktur::findOrFail($id);

            // Only super_admin can update any admin/instruktur, while normal admin can only update their own account
            if (!auth()->user()->isSuperAdmin() && auth()->id() != $id) {
                abort(403, 'Unauthorized. You can only update your own account.');
            }

            return view('admininstruktur::edit', compact('admin'));
        } catch (ModelNotFoundException $e) {
            abort(404, 'Admin/Instructor not found');
        }
    }

    /**
     * Update the specified admin or instructor
     */
    public function update(Request $request, $id)
    {
        try {
            // Find admin or return 404 if not found
            try {
                $admin = AdminInstruktur::findOrFail($id);
            } catch (ModelNotFoundException $e) {
                abort(404, 'Admin/Instructor not found');
            }

            // Only super_admin can update any admin/instruktur, while normal admin can only update their own account
            if (!auth()->user()->isSuperAdmin() && auth()->id() != $id) {
                return redirect()->back()->with('error', 'Unauthorized. You can only update your own account.');
            }

            // Validation rules
            $rules = [
                'username' => 'sometimes|required|string|max:255|unique:admin_instrukturs,username,' . $id,
                'email' => 'sometimes|required|string|email|max:255|unique:admin_instrukturs,email,' . $id,
                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^()_+\-=\[\]{};:,.<>])[A-Za-z\d@$!%*?&#^()_+\-=\[\]{};:,.<>]{8,}$/'
                ],
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
            ];

            $messages = [
                'password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, 1 angka, dan 1 karakter khusus (!@#$%^&*)',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except(['foto_profil', 'password']);

            // Handle password update
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // Only super_admin can change role
            if (isset($data['role']) && !auth()->user()->isSuperAdmin()) {
                unset($data['role']);
            }

            // Upload foto profil jika ada
            if ($request->hasFile('foto_profil')) {
                try {
                    // Hapus foto lama jika ada
                    if ($admin->foto_profil && Storage::exists('profile/foto/' . $admin->foto_profil)) {
                        Storage::delete('profile/foto/' . $admin->foto_profil);
                        Log::info('Old photo deleted: ' . $admin->foto_profil);
                    }

                    $file = $request->file('foto_profil');
                    
                    // Generate unique filename
                    $filename = time() . '_' . $id . '.' . $file->getClientOriginalExtension();
                    
                    // Simpan file
                    $path = $file->storeAs('profile/foto', $filename);
                    
                    if ($path) {
                        $data['foto_profil'] = $filename;
                        Log::info('New photo uploaded successfully: ' . $filename);
                    } else {
                        Log::error('Failed to upload new photo');
                    }
                } catch (\Exception $e) {
                    Log::error('Error uploading photo: ' . $e->getMessage());
                    return redirect()->back()
                        ->with('error', 'Error uploading photo: ' . $e->getMessage())
                        ->withInput();
                }
            }

            $admin->update($data);

            return redirect()->route('admin.index')->with('success', 'Admin/Instruktur updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating admin/instructor: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating admin/instructor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified admin or instructor
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Only super_admin can delete admin/instruktur
            if (!auth()->user()->isSuperAdmin()) {
                return redirect()->back()->with('error', 'Unauthorized. Only Super Admin can delete admin/instruktur.');
            }

            // Find admin or return 404 if not found
            try {
                $admin = AdminInstruktur::findOrFail($id);
            } catch (ModelNotFoundException $e) {
                abort(404, 'Admin/Instructor not found');
            }

            // Prevent deleting the last super_admin
            if ($admin->isSuperAdmin() && AdminInstruktur::where('role', 'super_admin')->count() <= 1) {
                return redirect()->back()->with('error', 'Cannot delete the last Super Admin account.');
            }

            // Hapus foto profil jika ada sebelum soft delete
            if ($admin->foto_profil && Storage::exists('profile/foto/' . $admin->foto_profil)) {
                Storage::delete('profile/foto/' . $admin->foto_profil);
            }

            // Soft delete karena menggunakan SoftDeletes trait
            $admin->delete();

            return redirect()->route('admin.index')->with('success', 'Admin/Instruktur deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting admin/instructor: ' . $e->getMessage());
        }
    }
}