<?php

namespace Modules\Materi\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Materi\Entities\ProgresMateri;
use Modules\Materi\Entities\Materi;
use Modules\Materi\Transformers\ProgresMateriResource;
use Illuminate\Support\Facades\Validator;

class ProgresMateriController extends Controller
{
    public function index(Request $request)
    {
        $query = ProgresMateri::with(['materi', 'peserta']);

        // Filter by peserta_id
        if ($request->has('peserta_id')) {
            $query->where('peserta_id', $request->peserta_id);
        }

        // Filter by materi_id
        if ($request->has('materi_id')) {
            $query->where('materi_id', $request->materi_id);
        }

        // Filter by is_selesai
        if ($request->has('is_selesai')) {
            $query->where('is_selesai', $request->boolean('is_selesai'));
        }

        $progresMateri = $query->get();

        return ProgresMateriResource::collection($progresMateri);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peserta_id' => 'required|exists:pesertas,id',
            'materi_id' => 'required|exists:materis,id',
            'is_selesai' => 'nullable|boolean',
            'progress_persen' => 'nullable|integer|min:0|max:100',
            'durasi_belajar_menit' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if progress already exists
        $existingProgres = ProgresMateri::where('peserta_id', $request->peserta_id)
            ->where('materi_id', $request->materi_id)
            ->first();

        if ($existingProgres) {
            return response()->json([
                'message' => 'Progress already exists for this peserta and materi.',
                'data' => new ProgresMateriResource($existingProgres)
            ], 422);
        }

        $data = $request->all();

        // Set default values
        if (!isset($data['is_selesai'])) {
            $data['is_selesai'] = false;
        }

        if (!isset($data['progress_persen'])) {
            $data['progress_persen'] = 0;
        }

        if (!isset($data['tanggal_mulai'])) {
            $data['tanggal_mulai'] = now();
        }

        // If marked as complete, set tanggal_selesai and progress_persen
        if ($data['is_selesai']) {
            $data['tanggal_selesai'] = now();
            $data['progress_persen'] = 100;
        }

        $progresMateri = ProgresMateri::create($data);

        return response()->json([
            'message' => 'Progress created successfully',
            'data' => new ProgresMateriResource($progresMateri)
        ], 201);
    }

    public function show($id)
    {
        $progresMateri = ProgresMateri::with(['materi', 'peserta'])->findOrFail($id);
        return new ProgresMateriResource($progresMateri);
    }

    public function update(Request $request, $id)
    {
        $progresMateri = ProgresMateri::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'peserta_id' => 'sometimes|required|exists:pesertas,id',
            'materi_id' => 'sometimes|required|exists:materis,id',
            'is_selesai' => 'nullable|boolean',
            'progress_persen' => 'nullable|integer|min:0|max:100',
            'durasi_belajar_menit' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // If marked as complete and wasn't complete before, set tanggal_selesai and progress_persen
        if ($request->filled('is_selesai') && $request->boolean('is_selesai') && !$progresMateri->is_selesai) {
            $data['tanggal_selesai'] = now();
            $data['progress_persen'] = 100;
        }

        // If marked as incomplete and was complete before, unset tanggal_selesai
        if ($request->filled('is_selesai') && !$request->boolean('is_selesai') && $progresMateri->is_selesai) {
            $data['tanggal_selesai'] = null;
        }

        $progresMateri->update($data);

        return response()->json([
            'message' => 'Progress updated successfully',
            'data' => new ProgresMateriResource($progresMateri)
        ]);
    }

    public function destroy($id)
    {
        $progresMateri = ProgresMateri::findOrFail($id);
        $progresMateri->delete();

        return response()->json([
            'message' => 'Progress deleted successfully'
        ]);
    }

    public function updateProgress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peserta_id' => 'required|exists:pesertas,id',
            'materi_id' => 'required|exists:materis,id',
            'progress_persen' => 'required|integer|min:0|max:100',
            'durasi_belajar_menit' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find or create progress
        $progresMateri = ProgresMateri::firstOrNew([
            'peserta_id' => $request->peserta_id,
            'materi_id' => $request->materi_id,
        ]);

        // If new, set tanggal_mulai
        if (!$progresMateri->exists) {
            $progresMateri->tanggal_mulai = now();
        }

        // Update progress
        $progresMateri->progress_persen = $request->progress_persen;

        // Update durasi_belajar_menit if provided
        if ($request->filled('durasi_belajar_menit')) {
            $progresMateri->durasi_belajar_menit = $request->durasi_belajar_menit;
        }

        // If progress is 100%, mark as complete
        if ($request->progress_persen == 100) {
            $progresMateri->is_selesai = true;
            $progresMateri->tanggal_selesai = now();
        }

        $progresMateri->save();

        return response()->json([
            'message' => 'Progress updated successfully',
            'data' => new ProgresMateriResource($progresMateri)
        ]);
    }
}
