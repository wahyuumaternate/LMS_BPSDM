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

   
}
