<?php

namespace Modules\Tugas\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tugas\Entities\PenilaianTugas;
use Modules\Tugas\Entities\TugasSubmission;
use Modules\Tugas\Transformers\PenilaianTugasResource;
use Illuminate\Support\Facades\Validator;

class PenilaianTugasController extends Controller
{
    public function index(Request $request)
    {
        $query = TugasSubmission::with(['submission.peserta', 'submission.tugas', 'penilai']);

        // Filter by submission_id
        if ($request->has('submission_id')) {
            $query->where('submission_id', $request->submission_id);
        }

        // Filter by admin_instruktur_id
        if ($request->has('admin_instruktur_id')) {
            $query->where('admin_instruktur_id', $request->admin_instruktur_id);
        }

        $penilaian = $query->get();

        return PenilaianTugasResource::collection($penilaian);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'submission_id' => 'required|exists:tugas_submissions,id',
            'admin_instruktur_id' => 'required|exists:admin_instrukturs,id',
            'nilai' => 'required|numeric|min:0|max:100',
            'komentar_instruktur' => 'nullable|string',
            'catatan_revisi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if penilaian already exists for this submission
        $existingPenilaian = TugasSubmission::where('submission_id', $request->submission_id)->first();

        if ($existingPenilaian) {
            return response()->json([
                'message' => 'This submission has already been graded. Please update the existing grade.'
            ], 422);
        }

        // Get the submission
        $submission = TugasSubmission::findOrFail($request->submission_id);

        // Add tanggal_dinilai
        $data = $request->all();
        $data['tanggal_dinilai'] = now();

        // Create penilaian
        $penilaian = TugasSubmission::create($data);

        // Update submission status
        $submission->status = 'graded';
        $submission->save();

        return response()->json([
            'message' => 'Assignment graded successfully',
            'data' => new PenilaianTugasResource($penilaian)
        ], 201);
    }

    public function show($id)
    {
        $penilaian = TugasSubmission::with(['submission.peserta', 'submission.tugas', 'penilai'])->findOrFail($id);
        return new PenilaianTugasResource($penilaian);
    }

    public function update(Request $request, $id)
    {
        $penilaian = TugasSubmission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'admin_instruktur_id' => 'sometimes|required|exists:admin_instrukturs,id',
            'nilai' => 'sometimes|required|numeric|min:0|max:100',
            'komentar_instruktur' => 'nullable|string',
            'catatan_revisi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update tanggal_dinilai
        $data = $request->all();
        $data['tanggal_dinilai'] = now();

        // Update penilaian
        $penilaian->update($data);

        // If there's catatan_revisi, update submission status to 'revision'
        if ($request->filled('catatan_revisi')) {
            $submission = $penilaian->submission;
            $submission->status = 'revision';
            $submission->save();
        }

        return response()->json([
            'message' => 'Penilaian updated successfully',
            'data' => new PenilaianTugasResource($penilaian)
        ]);
    }

    public function destroy($id)
    {
        $penilaian = TugasSubmission::findOrFail($id);

        // Update submission status
        $submission = $penilaian->submission;
        $submission->status = 'submitted';
        $submission->save();

        $penilaian->delete();

        return response()->json([
            'message' => 'Penilaian deleted successfully'
        ]);
    }
}
