<?php

namespace Modules\Tugas\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tugas\Entities\Tugas;
use Modules\Tugas\Transformers\TugasResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TugasController extends Controller
{
    public function index(Request $request)
    {
        $query = Tugas::with(['kursus']);
        
        // Filter by kursus_id
        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }
        
        $tugas = $query->get();
        
        return TugasResource::collection($tugas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'judul_tugas' => 'required|string|max:255',
            'soal' => 'required|string',
            'petunjuk_pengerjaan' => 'nullable|string',
            'deadline' => 'nullable|date',
            'bobot_nilai' => 'nullable|numeric|min:0.01|max:100',
            'file_soal' => 'nullable|file|max:10240', // 10MB max
            'max_file_size' => 'nullable|integer|min:1',
            'allowed_extensions' => 'nullable|string',
            'allow_late_submission' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('file_soal');

        // Upload file soal if provided
        if ($request->hasFile('file_soal')) {
            $file = $request->file('file_soal');
            $filename = Str::slug($request->judul_tugas) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/tugas/soal', $filename);
            $data['file_soal'] = $filename;
        }

        $tugas = Tugas::create($data);

        return response()->json([
            'message' => 'Tugas created successfully',
            'data' => new TugasResource($tugas)
        ], 201);
    }

    public function show($id)
    {
        $tugas = Tugas::with(['kursus', 'submissions'])->findOrFail($id);
        return new TugasResource($tugas);
    }

    public function update(Request $request, $id)
    {
        $tugas = Tugas::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kursus_id' => 'sometimes|required|exists:kursus,id',
            'judul_tugas' => 'sometimes|required|string|max:255',
            'soal' => 'sometimes|required|string',
            'petunjuk_pengerjaan' => 'nullable|string',
            'deadline' => 'nullable|date',
            'bobot_nilai' => 'nullable|numeric|min:0.01|max:100',
            'file_soal' => 'nullable|file|max:10240', // 10MB max
            'max_file_size' => 'nullable|integer|min:1',
            'allowed_extensions' => 'nullable|string',
            'allow_late_submission' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('file_soal');

        // Upload file soal if provided
        if ($request->hasFile('file_soal')) {
            // Delete old file if exists
            if ($tugas->file_soal) {
                Storage::delete('public/tugas/soal/' . $tugas->file_soal);
            }
            
            $file = $request->file('file_soal');
            $filename = Str::slug($request->judul_tugas ?? $tugas->judul_tugas) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/tugas/soal', $filename);
            $data['file_soal'] = $filename;
        }

        $tugas->update($data);

        return response()->json([
            'message' => 'Tugas updated successfully',
            'data' => new TugasResource($tugas)
        ]);
    }

    public function destroy($id)
    {
        $tugas = Tugas::findOrFail($id);
        
        // Delete file soal if exists
        if ($tugas->file_soal) {
            Storage::delete('public/tugas/soal/' . $tugas->file_soal);
        }
        
        $tugas->delete();

        return response()->json([
            'message' => 'Tugas deleted successfully'
        ]);
    }
}
