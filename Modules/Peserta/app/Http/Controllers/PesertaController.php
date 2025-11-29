<?php

namespace Modules\Peserta\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\OPD\Entities\OPD as EntitiesOPD;
use Modules\Peserta\Entities\Peserta;

class PesertaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Peserta::with('opd');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Filter by OPD
        if ($request->filled('opd')) {
            $query->where('opd_id', $request->opd);
        }

        // Filter by status kepegawaian
        if ($request->filled('status')) {
            $query->where('status_kepegawaian', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $pesertas = $query->latest()->paginate($perPage);
        
        $opds = EntitiesOPD::orderBy('nama_opd')->get();

        return view('peserta::index', compact('pesertas', 'opds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $opds = EntitiesOPD::orderBy('nama_opd')->get();
        return view('peserta::create', compact('opds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opd_id' => 'required|exists:opds,id',
            'username' => 'required|string|max:255|unique:pesertas,username',
            'email' => 'required|email|max:255|unique:pesertas,email',
            'password' => 'required|string|min:8|confirmed',
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'nullable|string|max:255|unique:pesertas,nip',
            'pangkat_golongan' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'tempat_lahir' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',
            'pendidikan_terakhir' => 'nullable|in:sma,d3,s1,s2,s3',
            'status_kepegawaian' => 'required|in:pns,pppk,kontrak',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Handle foto profil upload
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('profile/foto', $filename, 'public');
            $validated['foto_profil'] = $filename;
        }

        Peserta::create($validated);

        return redirect()->route('peserta.index')
            ->with('success', 'Data peserta berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Peserta $pesertum)
    {
        $pesertum->load('opd');
        return view('peserta::show', compact('pesertum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Peserta $pesertum)
    {
        $opds = EntitiesOPD::orderBy('nama_opd')->get();
        return view('peserta::edit', compact('pesertum', 'opds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Peserta $pesertum)
    {
        $validated = $request->validate([
            'opd_id' => 'required|exists:opds,id',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pesertas')->ignore($pesertum->id)
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pesertas')->ignore($pesertum->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'nama_lengkap' => 'required|string|max:255',
            'nip' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pesertas')->ignore($pesertum->id)
            ],
            'pangkat_golongan' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'tempat_lahir' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',
            'pendidikan_terakhir' => 'nullable|in:sma,d3,s1,s2,s3',
            'status_kepegawaian' => 'required|in:pns,pppk,kontrak',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle foto profil upload
        if ($request->hasFile('foto_profil')) {
            // Delete old photo if exists
            if ($pesertum->foto_profil) {
                Storage::disk('public')->delete('profile/foto/' . $pesertum->foto_profil);
            }

            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('profile/foto', $filename, 'public');
            $validated['foto_profil'] = $filename;
        }

        $pesertum->update($validated);

        return redirect()->route('peserta.index')
            ->with('success', 'Data peserta berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Peserta $pesertum)
    {
        // Delete foto profil if exists
        if ($pesertum->foto_profil) {
            Storage::disk('public')->delete('profile/foto/' . $pesertum->foto_profil);
        }

        $pesertum->delete();

        return redirect()->route('peserta.index')
            ->with('success', 'Data peserta berhasil dihapus.');
    }
}