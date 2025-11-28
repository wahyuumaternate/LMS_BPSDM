<?php

namespace Modules\Tugas\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tugas\Entities\TugasSubmission;
use Modules\Tugas\Entities\Tugas;
use Modules\Tugas\Transformers\TugasSubmissionResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Tugas Submission",
 *     description="API Endpoints untuk pengumpulan dan penilaian tugas"
 * )
 */
class TugasSubmissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/tugas-submissions",
     *     summary="Mendapatkan daftar submission tugas",
     *     tags={"Tugas Submission"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="tugas_id",
     *         in="query",
     *         description="Filter berdasarkan ID tugas",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="peserta_id",
     *         in="query",
     *         description="Filter berdasarkan ID peserta",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "submitted", "graded", "returned", "late"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar submission berhasil diambil",
     *         @OA\JsonContent(type="object", @OA\Property(property="data", type="array", @OA\Items(type="object")))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $query = TugasSubmission::with(['tugas', 'peserta', 'penilai']);

        if ($request->has('tugas_id')) {
            $query->where('tugas_id', $request->tugas_id);
        }

        if ($request->has('peserta_id')) {
            $query->where('peserta_id', $request->peserta_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->orderBy('tanggal_submit', 'desc')->get();

        return TugasSubmissionResource::collection($submissions);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tugas-submissions",
     *     summary="Submit tugas (peserta)",
     *     tags={"Tugas Submission"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Pilih salah satu: application/json (tanpa file) atau multipart/form-data (dengan file)",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"tugas_id", "peserta_id"},
     *                 @OA\Property(property="tugas_id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="catatan_peserta", type="string", example="Ini adalah jawaban tugas saya")
     *             )
     *         ),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"tugas_id", "peserta_id"},
     *                 @OA\Property(property="tugas_id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="catatan_peserta", type="string", example="Ini adalah jawaban tugas saya"),
     *                 @OA\Property(property="file_jawaban", type="string", format="binary", description="File jawaban (PDF, DOC, DOCX, ZIP, max 10MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tugas berhasil disubmit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Assignment submitted successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tugas_id' => 'required|exists:tugas,id',
            'peserta_id' => 'required|exists:pesertas,id',
            'catatan_peserta' => 'nullable|string',
            'file_jawaban' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if submission already exists
        $existingSubmission = TugasSubmission::where('tugas_id', $request->tugas_id)
            ->where('peserta_id', $request->peserta_id)
            ->first();

        if ($existingSubmission) {
            return response()->json([
                'message' => 'You have already submitted this assignment. Please update your existing submission.'
            ], 422);
        }

        $data = $request->except('file_jawaban');

        // Upload file jawaban if provided
        if ($request->hasFile('file_jawaban')) {
            $file = $request->file('file_jawaban');
            $filename = 'submission-' . $request->peserta_id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/tugas/submissions', $filename);
            $data['file_jawaban'] = 'tugas/submissions/' . $filename;
        }

        // Check if late submission
        $tugas = Tugas::findOrFail($request->tugas_id);
        $isLate = $tugas->tanggal_deadline && now()->gt($tugas->tanggal_deadline);

        $data['tanggal_submit'] = now();
        $data['status'] = $isLate ? 'late' : 'submitted';

        $submission = TugasSubmission::create($data);

        return response()->json([
            'message' => 'Assignment submitted successfully',
            'data' => new TugasSubmissionResource($submission)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tugas-submissions/{id}",
     *     summary="Mendapatkan detail submission",
     *     tags={"Tugas Submission"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID submission",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail submission berhasil diambil",
     *         @OA\JsonContent(@OA\Property(property="data", type="object"))
     *     ),
     *     @OA\Response(response=404, description="Submission tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show($id)
    {
        $submission = TugasSubmission::with(['tugas', 'peserta', 'penilai'])->findOrFail($id);
        return new TugasSubmissionResource($submission);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tugas-submissions/{id}",
     *     summary="Update submission (gunakan POST dengan _method=PUT)",
     *     tags={"Tugas Submission"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID submission",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="catatan_peserta", type="string"),
     *                 @OA\Property(property="file_jawaban", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Submission berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Submission updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Submission tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(Request $request, $id)
    {
        $submission = TugasSubmission::findOrFail($id);

        // Can only update if status is draft or returned
        if (!in_array($submission->status, ['draft', 'returned'])) {
            return response()->json([
                'message' => 'Cannot update submitted or graded assignment'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'catatan_peserta' => 'nullable|string',
            'file_jawaban' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('file_jawaban');

        // Upload file jawaban if provided
        if ($request->hasFile('file_jawaban')) {
            // Delete old file if exists
            if ($submission->file_jawaban) {
                Storage::delete('public/' . $submission->file_jawaban);
            }

            $file = $request->file('file_jawaban');
            $filename = 'submission-' . $submission->peserta_id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/tugas/submissions', $filename);
            $data['file_jawaban'] = 'tugas/submissions/' . $filename;
        }

        $submission->update($data);

        return response()->json([
            'message' => 'Submission updated successfully',
            'data' => new TugasSubmissionResource($submission)
        ]);
    }

}
