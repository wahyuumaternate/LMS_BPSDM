<?php

namespace Modules\Tugas\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tugas\Entities\Tugas;
use Modules\Tugas\Transformers\TugasResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Tugas",
 *     description="API Endpoints untuk manajemen Tugas"
 * )
 */
class TugasController extends Controller
{
   /**
 * @OA\Get(
 *     path="/api/v1/tugas",
 *     summary="Mendapatkan daftar tugas",
 *     tags={"Tugas"},
 *     security={{"sanctum":{}}},
 *
 *     @OA\Parameter(
 *         name="kursus_id",
 *         in="query",
 *         description="Filter berdasarkan ID kursus (melalui relasi modul)",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="modul_id",
 *         in="query",
 *         description="Filter berdasarkan ID modul",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="is_published",
 *         in="query",
 *         description="Filter berdasarkan status publish",
 *         required=false,
 *         @OA\Schema(type="boolean")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Daftar tugas berhasil diambil",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="modul_id", type="integer", example=1),
 *                     @OA\Property(property="judul", type="string", example="Tugas Membuat Aplikasi CRUD"),
 *                     @OA\Property(property="deskripsi", type="string", example="Buat aplikasi CRUD sederhana"),
 *                     @OA\Property(property="petunjuk", type="string", example="Gunakan Laravel 10 dan MySQL"),
 *                     @OA\Property(property="file_tugas", type="string", example="tugas/file.pdf"),
 *                     @OA\Property(property="tanggal_mulai", type="string", format="date", example="2024-01-01"),
 *                     @OA\Property(property="tanggal_deadline", type="string", format="date", example="2024-01-15"),
 *                     @OA\Property(property="nilai_maksimal", type="integer", example=100),
 *                     @OA\Property(property="bobot_nilai", type="integer", example=1),
 *                     @OA\Property(property="is_published", type="boolean", example=true),
 *                     @OA\Property(property="published_at", type="string", format="date-time"),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
public function index(Request $request)
{
    $query = Tugas::with(['modul']);

    // Filter berdasarkan kursus_id melalui relasi modul
    if ($request->has('kursus_id')) {
        $query->whereHas('modul', function ($q) use ($request) {
            $q->where('kursus_id', $request->kursus_id);
        });
    }

    // Filter berdasarkan modul_id
    if ($request->has('modul_id')) {
        $query->where('modul_id', $request->modul_id);
    }

    // Filter status publish
    if ($request->has('is_published')) {
        $query->where('is_published', $request->boolean('is_published'));
    }

    $tugas = $query->orderBy('tanggal_deadline', 'asc')->get();

    return TugasResource::collection($tugas);
}


    /**
     * @OA\Post(
     *     path="/api/v1/tugas",
     *     summary="Membuat tugas baru",
     *     tags={"Tugas"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Pilih salah satu: application/json (tanpa file) atau multipart/form-data (dengan file)",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"modul_id", "judul", "deskripsi"},
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Tugas Membuat Aplikasi CRUD"),
     *                 @OA\Property(property="deskripsi", type="string", example="Buat aplikasi CRUD sederhana"),
     *                 @OA\Property(property="petunjuk", type="string", example="Gunakan Laravel 10 dan MySQL"),
     *                 @OA\Property(property="tanggal_mulai", type="string", format="date", example="2024-01-01"),
     *                 @OA\Property(property="tanggal_deadline", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="nilai_maksimal", type="integer", example=100),
     *                 @OA\Property(property="bobot_nilai", type="integer", example=1),
     *                 @OA\Property(property="is_published", type="boolean", example=false)
     *             )
     *         ),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"modul_id", "judul", "deskripsi"},
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Tugas Membuat Aplikasi CRUD"),
     *                 @OA\Property(property="deskripsi", type="string", example="Buat aplikasi CRUD sederhana"),
     *                 @OA\Property(property="petunjuk", type="string", example="Gunakan Laravel 10 dan MySQL"),
     *                 @OA\Property(property="file_tugas", type="string", format="binary", description="File tugas (PDF, DOC, DOCX, max 10MB)"),
     *                 @OA\Property(property="tanggal_mulai", type="string", format="date", example="2024-01-01"),
     *                 @OA\Property(property="tanggal_deadline", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="nilai_maksimal", type="integer", example=100),
     *                 @OA\Property(property="bobot_nilai", type="integer", example=1),
     *                 @OA\Property(property="is_published", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tugas berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tugas created successfully"),
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
            'modul_id' => 'required|exists:moduls,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'petunjuk' => 'nullable|string',
            'file_tugas' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'tanggal_mulai' => 'nullable|date',
            'tanggal_deadline' => 'nullable|date|after_or_equal:tanggal_mulai',
            'nilai_maksimal' => 'nullable|integer|min:1|max:100',
            'bobot_nilai' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('file_tugas');

        // Upload file tugas if provided
        if ($request->hasFile('file_tugas')) {
            $file = $request->file('file_tugas');
            $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/tugas', $filename);
            $data['file_tugas'] = 'tugas/' . $filename;
        }

        // Set published_at if is_published is true
        if ($request->boolean('is_published')) {
            $data['published_at'] = now();
        }

        $tugas = Tugas::create($data);

        return response()->json([
            'message' => 'Tugas created successfully',
            'data' => new TugasResource($tugas)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tugas/{id}",
     *     summary="Mendapatkan detail tugas",
     *     tags={"Tugas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID tugas",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail tugas berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Tugas tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show($id)
    {
        // dd($id);
        $tugas = Tugas::with(['modul', 'submissions'])->findOrFail($id);
        return new TugasResource($tugas);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tugas/{id}",
     *     summary="Mengupdate tugas (gunakan POST dengan _method=PUT)",
     *     tags={"Tugas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID tugas",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Tugas Membuat Aplikasi CRUD Updated"),
     *                 @OA\Property(property="deskripsi", type="string"),
     *                 @OA\Property(property="petunjuk", type="string"),
     *                 @OA\Property(property="file_tugas", type="string", format="binary"),
     *                 @OA\Property(property="tanggal_mulai", type="string", format="date"),
     *                 @OA\Property(property="tanggal_deadline", type="string", format="date"),
     *                 @OA\Property(property="nilai_maksimal", type="integer"),
     *                 @OA\Property(property="bobot_nilai", type="integer"),
     *                 @OA\Property(property="is_published", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tugas berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tugas updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Tugas tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(Request $request, $id)
    {
        $tugas = Tugas::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'modul_id' => 'sometimes|required|exists:moduls,id',
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
            'petunjuk' => 'nullable|string',
            'file_tugas' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_deadline' => 'nullable|date|after_or_equal:tanggal_mulai',
            'nilai_maksimal' => 'nullable|integer|min:1|max:100',
            'bobot_nilai' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('file_tugas');

        // Upload file tugas if provided
        if ($request->hasFile('file_tugas')) {
            // Delete old file if exists
            if ($tugas->file_tugas) {
                Storage::delete('public/' . $tugas->file_tugas);
            }

            $file = $request->file('file_tugas');
            $filename = Str::slug($request->judul ?? $tugas->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/tugas', $filename);
            $data['file_tugas'] = 'tugas/' . $filename;
        }

        // Set published_at if is_published changed to true
        if ($request->has('is_published') && $request->boolean('is_published') && !$tugas->is_published) {
            $data['published_at'] = now();
        }

        $tugas->update($data);

        return response()->json([
            'message' => 'Tugas updated successfully',
            'data' => new TugasResource($tugas)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tugas/{id}",
     *     summary="Menghapus tugas",
     *     tags={"Tugas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID tugas",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tugas berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tugas deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Tugas tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy($id)
    {
        $tugas = Tugas::findOrFail($id);

        // Delete file tugas if exists
        if ($tugas->file_tugas) {
            Storage::delete('public/' . $tugas->file_tugas);
        }

        $tugas->delete();

        return response()->json([
            'message' => 'Tugas deleted successfully'
        ]);
    }
}
