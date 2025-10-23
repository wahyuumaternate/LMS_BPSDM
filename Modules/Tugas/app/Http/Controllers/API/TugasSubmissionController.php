<?php

namespace Modules\Tugas\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tugas\Entities\Tugas;
use Modules\Tugas\Entities\TugasSubmission;
use Modules\Tugas\Transformers\TugasSubmissionResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TugasSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = TugasSubmission::with(['tugas', 'peserta', 'penilaian']);

        // Filter by tugas_id
        if ($request->has('tugas_id')) {
            $query->where('tugas_id', $request->tugas_id);
        }

        // Filter by peserta_id
        if ($request->has('peserta_id')) {
            $query->where('peserta_id', $request->peserta_id);
        }

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['submitted', 'graded', 'revision', 'resubmitted'])) {
            $query->where('status', $request->status);
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        return TugasSubmissionResource::collection($submissions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tugas_id' => 'required|exists:tugas,id',
            'peserta_id' => 'required|exists:pesertas,id',
            'jawaban_text' => 'nullable|string',
            'file_jawaban' => 'nullable|file|max:20480', // 20MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check tugas first
        $tugas = Tugas::findOrFail($request->tugas_id);

        // Check if there's already a submission for this peserta
        $existingSubmission = TugasSubmission::where('tugas_id', $request->tugas_id)
            ->where('peserta_id', $request->peserta_id)
            ->first();

        if ($existingSubmission) {
            return response()->json([
                'message' => 'You have already submitted this assignment. Please update your submission instead.'
            ], 422);
        }

        $data = $request->except('file_jawaban');
        $data['tanggal_submit'] = now();

        // Check if submission is late
        if ($tugas->deadline && now() > $tugas->deadline) {
            if (!$tugas->allow_late_submission) {
                return response()->json([
                    'message' => 'The deadline for this assignment has passed and late submissions are not allowed.'
                ], 422);
            }
            $data['is_late'] = true;
        }

        // Upload file jawaban if provided
        if ($request->hasFile('file_jawaban')) {
            // Check if file size exceeds the limit
            if ($tugas->max_file_size > 0 && $request->file('file_jawaban')->getSize() > ($tugas->max_file_size * 1024)) {
                return response()->json([
                    'message' => 'File size exceeds the maximum allowed size (' . $tugas->max_file_size . 'KB).'
                ], 422);
            }

            // Check if file extension is allowed
            $extension = $request->file('file_jawaban')->getClientOriginalExtension();
            $allowedExtensions = $tugas->getAllowedExtensionsArray();

            if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'message' => 'File type not allowed. Allowed types: ' . $tugas->allowed_extensions
                ], 422);
            }

            $file = $request->file('file_jawaban');
            $filename = 'submission-' . $request->peserta_id . '-' . time() . '.' . $extension;
            $file->storeAs('public/tugas/jawaban', $filename);
            $data['file_jawaban'] = $filename;
        }

        $data['status'] = 'submitted';
        $data['attempt'] = 1;

        $submission = TugasSubmission::create($data);

        return response()->json([
            'message' => 'Assignment submitted successfully',
            'data' => new TugasSubmissionResource($submission)
        ], 201);
    }

    public function show($id)
    {
        $submission = TugasSubmission::with(['tugas', 'peserta', 'penilaian'])->findOrFail($id);
        return new TugasSubmissionResource($submission);
    }

    public function update(Request $request, $id)
    {
        $submission = TugasSubmission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jawaban_text' => 'nullable|string',
            'file_jawaban' => 'nullable|file|max:20480', // 20MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get tugas
        $tugas = $submission->tugas;

        // Only allow updates if not graded yet
        if (in_array($submission->status, ['graded', 'returned'])) {
            return response()->json([
                'message' => 'Cannot update submission that has already been graded.'
            ], 422);
        }

        $data = $request->except('file_jawaban');
        $data['tanggal_submit'] = now();

        // Check if submission is late
        if ($tugas->deadline && now() > $tugas->deadline) {
            if (!$tugas->allow_late_submission) {
                return response()->json([
                    'message' => 'The deadline for this assignment has passed and late submissions are not allowed.'
                ], 422);
            }
            $data['is_late'] = true;
        }

        // Upload file jawaban if provided
        if ($request->hasFile('file_jawaban')) {
            // Check if file size exceeds the limit
            if ($tugas->max_file_size > 0 && $request->file('file_jawaban')->getSize() > ($tugas->max_file_size * 1024)) {
                return response()->json([
                    'message' => 'File size exceeds the maximum allowed size (' . $tugas->max_file_size . 'KB).'
                ], 422);
            }

            // Check if file extension is allowed
            $extension = $request->file('file_jawaban')->getClientOriginalExtension();
            $allowedExtensions = $tugas->getAllowedExtensionsArray();

            if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'message' => 'File type not allowed. Allowed types: ' . $tugas->allowed_extensions
                ], 422);
            }

            // Delete old file if exists
            if ($submission->file_jawaban) {
                Storage::delete('public/tugas/jawaban/' . $submission->file_jawaban);
            }

            $file = $request->file('file_jawaban');
            $filename = 'submission-' . $submission->peserta_id . '-' . time() . '.' . $extension;
            $file->storeAs('public/tugas/jawaban', $filename);
            $data['file_jawaban'] = $filename;
        }

        // If status was revision, change to resubmitted
        if ($submission->status === 'revision') {
            $data['status'] = 'resubmitted';
            $data['attempt'] = $submission->attempt + 1;
        }

        $submission->update($data);

        return response()->json([
            'message' => 'Assignment submission updated successfully',
            'data' => new TugasSubmissionResource($submission)
        ]);
    }

    public function destroy($id)
    {
        $submission = TugasSubmission::findOrFail($id);

        // Delete file jawaban if exists
        if ($submission->file_jawaban) {
            Storage::delete('public/tugas/jawaban/' . $submission->file_jawaban);
        }

        $submission->delete();

        return response()->json([
            'message' => 'Assignment submission deleted successfully'
        ]);
    }

    public function getByPeserta(Request $request, $pesertaId)
    {
        $validator = Validator::make(['peserta_id' => $pesertaId], [
            'peserta_id' => 'required|exists:pesertas,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = TugasSubmission::with(['tugas.kursus', 'penilaian'])
            ->where('peserta_id', $pesertaId);

        if ($request->has('tugas_id')) {
            $query->where('tugas_id', $request->tugas_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        return TugasSubmissionResource::collection($submissions);
    }
}
