<?php

namespace Modules\OPD\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\OPD\Entities\OPD;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OPDController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $opds = OPD::when($search, function ($query) use ($search) {
            return $query->search($search);
        })
            ->orderBy('nama_opd', 'asc')
            ->paginate(10);

        if ($request->ajax()) {
            return view('opd::partials.opd_table', compact('opds'));
        }

        return view('opd::index', compact('opds'));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_opd' => 'required|string|max:100|unique:opds',
            'nama_opd' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'nama_kepala' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $opd = OPD::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'OPD berhasil dibuat',
                'data' => $opd
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $opd = OPD::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $opd
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'OPD tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $opd = OPD::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'kode_opd' => 'required|string|max:100|unique:opds,kode_opd,' . $opd->id,
                'nama_opd' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'nama_kepala' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $opd->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'OPD berhasil diperbarui',
                'data' => $opd
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $opd = OPD::findOrFail($id);

            // Cek apakah OPD memiliki peserta
            if ($opd->pesertas()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'OPD tidak dapat dihapus karena masih memiliki peserta'
                ], 422);
            }

            DB::beginTransaction();

            $opd->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'OPD berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
