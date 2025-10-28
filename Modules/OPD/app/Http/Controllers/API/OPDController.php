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
     * @OA\Post(
     *     path="/api/v1/opds",
     *     summary="Membuat OPD baru",
     *     tags={"OPD"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kode_opd", "nama_opd"},
     *             @OA\Property(property="kode_opd", type="string", maxLength=100, example="DISDIK"),
     *             @OA\Property(property="nama_opd", type="string", maxLength=255, example="Dinas Pendidikan"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Merdeka No. 123, Jakarta Pusat"),
     *             @OA\Property(property="no_telepon", type="string", maxLength=20, example="021-12345678"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="disdik@pemda.go.id"),
     *             @OA\Property(property="nama_kepala", type="string", maxLength=255, example="Dr. John Doe, M.Pd")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OPD berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OPD created successfully"),
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
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="kode_opd",
     *                     type="array",
     *                     @OA\Items(type="string", example="The kode opd has already been taken.")
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

    /**
     * @OA\Put(
     *     path="/api/v1/opds/{id}",
     *     summary="Mengupdate OPD",
     *     tags={"OPD"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID OPD",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="kode_opd", type="string", maxLength=100, example="DISDIK-01"),
     *             @OA\Property(property="nama_opd", type="string", maxLength=255, example="Dinas Pendidikan dan Kebudayaan"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Merdeka No. 123, Jakarta Pusat 10110"),
     *             @OA\Property(property="no_telepon", type="string", maxLength=20, example="021-12345678"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="disdikbud@pemda.go.id"),
     *             @OA\Property(property="nama_kepala", type="string", maxLength=255, example="Dr. Jane Smith, M.Pd")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OPD berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OPD updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kode_opd", type="string", example="DISDIK-01"),
     *                 @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan dan Kebudayaan"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Merdeka No. 123, Jakarta Pusat 10110"),
     *                 @OA\Property(property="no_telepon", type="string", example="021-12345678"),
     *                 @OA\Property(property="email", type="string", example="disdikbud@pemda.go.id"),
     *                 @OA\Property(property="nama_kepala", type="string", example="Dr. Jane Smith, M.Pd"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
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

    /**
     * @OA\Delete(
     *     path="/api/v1/opds/{id}",
     *     summary="Menghapus OPD",
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
     *         description="OPD berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OPD deleted successfully")
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
    public function destroy($id)
    {
        $opd = OPD::findOrFail($id);
        $opd->delete();

        return response()->json([
            'message' => 'OPD deleted successfully'
        ]);
    }
}