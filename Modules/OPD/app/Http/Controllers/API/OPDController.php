<?php

namespace Modules\OPD\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\OPD\Entities\OPD;
use Modules\OPD\Transformers\OPDResource;
use Illuminate\Support\Facades\Validator;

class OPDController extends Controller
{
    public function index()
    {
        $opds = OPD::all();
        return OPDResource::collection($opds);
    }

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

        $opd = OPD::create($request->all());

        return response()->json([
            'message' => 'OPD created successfully',
            'data' => new OPDResource($opd)
        ], 201);
    }

    public function show($id)
    {
        $opd = OPD::findOrFail($id);
        return new OPDResource($opd);
    }

    public function update(Request $request, $id)
    {
        $opd = OPD::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode_opd' => 'sometimes|required|string|max:100|unique:opds,kode_opd,' . $opd->id,
            'nama_opd' => 'sometimes|required|string|max:255',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'nama_kepala' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $opd->update($request->all());

        return response()->json([
            'message' => 'OPD updated successfully',
            'data' => new OPDResource($opd)
        ]);
    }

    public function destroy($id)
    {
        $opd = OPD::findOrFail($id);
        $opd->delete();

        return response()->json([
            'message' => 'OPD deleted successfully'
        ]);
    }
}
