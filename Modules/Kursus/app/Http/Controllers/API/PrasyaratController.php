<?php

namespace Modules\Kursus\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Kursus\Entities\Prasyarat;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Transformers\PrasyaratResource;
use Illuminate\Support\Facades\Validator;

class PrasyaratController extends Controller
{
    public function index(Request $request)
    {
        $query = Prasyarat::with(['kursus', 'kursusPrasyarat']);

        // Filter by kursus_id
        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        $prasyarats = $query->get();

        return PrasyaratResource::collection($prasyarats);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'kursus_prasyarat_id' => 'required|exists:kursus,id|different:kursus_id',
            'deskripsi' => 'nullable|string',
            'is_wajib' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if prasyarat already exists
        $existingPrasyarat = Prasyarat::where('kursus_id', $request->kursus_id)
            ->where('kursus_prasyarat_id', $request->kursus_prasyarat_id)
            ->first();
        if ($existingPrasyarat) {
            return response()->json([
                'message' => 'Prasyarat already exists.'
            ], 422);
        }

        $prasyarat = Prasyarat::create($request->all());

        return response()->json([
            'message' => 'Prasyarat created successfully',
            'data' => new PrasyaratResource($prasyarat)
        ], 201);
    }

    public function show($id)
    {
        $prasyarat = Prasyarat::with(['kursus', 'kursusPrasyarat'])->findOrFail($id);
        return new PrasyaratResource($prasyarat);
    }

    public function update(Request $request, $id)
    {
        $prasyarat = Prasyarat::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kursus_id' => 'sometimes|required|exists:kursus,id',
            'kursus_prasyarat_id' => 'sometimes|required|exists:kursus,id|different:kursus_id',
            'deskripsi' => 'nullable|string',
            'is_wajib' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If trying to change kursus_id or kursus_prasyarat_id, check if new combination already exists
        if (($request->filled('kursus_id') && $request->kursus_id != $prasyarat->kursus_id) ||
            ($request->filled('kursus_prasyarat_id') && $request->kursus_prasyarat_id != $prasyarat->kursus_prasyarat_id)
        ) {

            $kursus_id = $request->filled('kursus_id') ? $request->kursus_id : $prasyarat->kursus_id;
            $kursus_prasyarat_id = $request->filled('kursus_prasyarat_id') ? $request->kursus_prasyarat_id : $prasyarat->kursus_prasyarat_id;

            $existingPrasyarat = Prasyarat::where('kursus_id', $kursus_id)
                ->where('kursus_prasyarat_id', $kursus_prasyarat_id)
                ->where('id', '!=', $id)
                ->first();
            if ($existingPrasyarat) {
                return response()->json([
                    'message' => 'Prasyarat already exists with these kursus combinations.'
                ], 422);
            }
        }

        $prasyarat->update($request->all());

        return response()->json([
            'message' => 'Prasyarat updated successfully',
            'data' => new PrasyaratResource($prasyarat)
        ]);
    }

    public function destroy($id)
    {
        $prasyarat = Prasyarat::findOrFail($id);
        $prasyarat->delete();

        return response()->json([
            'message' => 'Prasyarat deleted successfully'
        ]);
    }
}
