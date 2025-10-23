<?php

namespace Modules\Peserta\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Peserta\Entities\Peserta;
use Modules\Peserta\Transformers\PesertaResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PesertaController extends Controller
{
    public function index()
    {
        $pesertas = Peserta::with('opd')->paginate(15);
        return PesertaResource::collection($pesertas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'opd_id' => 'required|exists:opds,id',
            'username' => 'required|string|max:255|unique:pesertas',
            'email' => 'required|string|email|max:255|unique:pesertas',
            'password' => 'required|string|min:8',
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:pesertas',
            'pangkat_golongan' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'tempat_lahir' => 'nullable|string|max:100',
            'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',
            'pendidikan_terakhir' => 'nullable|in:sma,d3,s1,s2,s3',
            'status_kepegawaian' => 'required|in:pns,pppk,kontrak',
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

        $peserta = Peserta::create($data);

        return response()->json([
            'message' => 'Peserta created successfully',
            'data' => new PesertaResource($peserta)
        ], 201);
    }

    public function show($id)
    {
        $peserta = Peserta::with('opd')->findOrFail($id);
        return new PesertaResource($peserta);
    }

    public function update(Request $request, $id)
    {
        $peserta = Peserta::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'opd_id' => 'sometimes|required|exists:opds,id',
            'username' => 'sometimes|required|string|max:255|unique:pesertas,username,' . $id,
            'email' => 'sometimes|required|string|email|max:255|unique:pesertas,email,' . $id,
            'password' => 'nullable|string|min:8',
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'nip' => 'nullable|string|unique:pesertas,nip,' . $id,
            'pangkat_golongan' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'tempat_lahir' => 'nullable|string|max:100',
            'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',
            'pendidikan_terakhir' => 'nullable|in:sma,d3,s1,s2,s3',
            'status_kepegawaian' => 'sometimes|required|in:pns,pppk,kontrak',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['foto_profil', 'password']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Upload foto profil jika ada
        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($peserta->foto_profil) {
                Storage::delete('public/profile/foto/' . $peserta->foto_profil);
            }

            $file = $request->file('foto_profil');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/profile/foto', $filename);
            $data['foto_profil'] = $filename;
        }

        $peserta->update($data);

        return response()->json([
            'message' => 'Peserta updated successfully',
            'data' => new PesertaResource($peserta)
        ]);
    }

    public function destroy($id)
    {
        $peserta = Peserta::findOrFail($id);

        // Soft delete karena menggunakan SoftDeletes trait
        $peserta->delete();

        return response()->json([
            'message' => 'Peserta deleted successfully'
        ]);
    }
}
