<?php

namespace Modules\OPD\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\OPD\Entities\OPD;
use Modules\OPD\Transformers\OPDResource;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="OPD",
 *     description="API Endpoints untuk manajemen Organisasi Perangkat Daerah (OPD)"
 * )
 */
class OPDController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/opds",
     *     summary="Mendapatkan daftar OPD",
     *     tags={"OPD"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar OPD berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kode_opd", type="string", example="DISDIK"),
     *                     @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan"),
     *                     @OA\Property(property="alamat", type="string", example="Jl. Merdeka No. 123"),
     *                     @OA\Property(property="no_telepon", type="string", example="021-12345678"),
     *                     @OA\Property(property="email", type="string", example="disdik@pemda.go.id"),
     *                     @OA\Property(property="nama_kepala", type="string", example="Dr. John Doe, M.Pd"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $opds = OPD::all();
        return OPDResource::collection($opds);
    }

   
    /**
     * @OA\Get(
     *     path="/api/v1/opds/{id}",
     *     summary="Mendapatkan detail OPD",
     *     tags={"OPD"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID OPD",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail OPD berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kode_opd", type="string", example="DISDIK"),
     *                 @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Merdeka No. 123"),
     *                 @OA\Property(property="no_telepon", type="string", example="021-12345678"),
     *                 @OA\Property(property="email", type="string", example="disdik@pemda.go.id"),
     *                 @OA\Property(property="nama_kepala", type="string", example="Dr. John Doe, M.Pd"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="OPD tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $opd = OPD::findOrFail($id);
        return new OPDResource($opd);
    }

   
}