<?php

namespace Modules\Master\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Master\Entities\OPD;
use Illuminate\Support\Facades\Validator;

class OPDController extends Controller
{
    public function index()
    {
        $opd = OPD::all();
        return response()->json([
            'status' => 'success',
            'data' => $opd
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_opd' => 'required|unique:opd,kode_opd',
            'nama_opd' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'nama_kepala' => 'nullable|string|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $opd = OPD::create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'OPD berhasil ditambahkan',
            'data' => $opd
        ], 201);
    }
    public function show($id)
    {
        $opd = OPD::find($id);
        if (!$opd) {
            return response()->json([
                'status' => 'error',
                'message' => 'OPD tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $opd
        ]);
    }
    public function update(Request $request, $id)
    {
        $opd = OPD::find($id);
        if (!$opd) {
            return response()->json([
                'status' => 'error',
                'message' => 'OPD tidak ditemukan'
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'kode_opd' => 'required|unique:opd,kode_opd,' . $id,
            'nama_opd' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'nama_kepala' => 'nullable|string|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $opd->update($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'OPD berhasil diperbarui',
            'data' => $opd
        ]);
    }
    public function destroy($id)
    {
        $opd = OPD::find($id);
        if (!$opd) {
            return response()->json([
                'status' => 'error',
                'message' => 'OPD tidak ditemukan'
            ], 404);
        }
        // Check if OPD has peserta
        $pesertaCount = $opd->peserta()->count();
        if ($pesertaCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'OPD tidak dapat dihapus karena masih memiliki ' . $pesertaCount . ' peserta'
            ], 400);
        }
        $opd->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'OPD berhasil dihapus'
        ]);
    }
}
