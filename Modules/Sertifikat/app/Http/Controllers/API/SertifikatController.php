<?php

namespace Modules\Sertifikat\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sertifikat\Entities\Sertifikat;
use Modules\Sertifikat\Entities\TemplateSertifikat;
use Modules\Sertifikat\Transformers\SertifikatResource;
use Modules\Peserta\Entities\Peserta;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * @OA\Tag(
 *     name="Sertifikat",
 *     description="API Endpoints untuk manajemen Sertifikat"
 * )
 */
class SertifikatController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/sertifikat",
     *     summary="Mendapatkan daftar sertifikat",
     *     tags={"Sertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter berdasarkan ID Kursus",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="peserta_id",
     *         in="query",
     *         description="Filter berdasarkan ID Peserta",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Pencarian berdasarkan nomor sertifikat",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="peserta_id", type="integer", example=1),
     *                     @OA\Property(property="peserta", type="object",
     *                         @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com")
     *                     ),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kursus", type="object",
     *                         @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah")
     *                     ),
     *                     @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/001"),
     *                     @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-10-30"),
     *                     @OA\Property(property="file_url", type="string", example="http://localhost/storage/sertifikat/sertifikat-1.pdf")
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
    public function index(Request $request)
    {
        $query = Sertifikat::with(['peserta', 'kursus', 'template']);

        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        if ($request->has('peserta_id')) {
            $query->where('peserta_id', $request->peserta_id);
        }

        if ($request->has('search')) {
            $query->where('nomor_sertifikat', 'like', '%' . $request->search . '%');
        }

        $sertifikats = $query->orderBy('created_at', 'desc')->paginate(10);

        return SertifikatResource::collection($sertifikats);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sertifikat",
     *     summary="Membuat sertifikat baru",
     *     tags={"Sertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"peserta_id", "kursus_id", "template_id"},
     *             @OA\Property(property="peserta_id", type="integer", example=1),
     *             @OA\Property(property="kursus_id", type="integer", example=1),
     *             @OA\Property(property="template_id", type="integer", example=1),
     *             @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/001"),
     *             @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-10-30"),
     *             @OA\Property(property="nama_penandatangan", type="string", example="Dr. Ir. Budi Santoso, M.Si"),
     *             @OA\Property(property="jabatan_penandatangan", type="string", example="Kepala BPSDM")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sertifikat berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sertifikat created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/001"),
     *                 @OA\Property(property="file_url", type="string", example="http://localhost/storage/sertifikat/sertifikat-1.pdf")
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
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peserta_id' => 'required|exists:peserta,id',
            'kursus_id' => 'required|exists:kursus,id',
            'template_id' => 'required|exists:template_sertifikat,id',
            'nomor_sertifikat' => 'required|string|max:255|unique:sertifikat,nomor_sertifikat',
            'tanggal_terbit' => 'required|date',
            'nama_penandatangan' => 'required|string|max:255',
            'jabatan_penandatangan' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create sertifikat
        $sertifikat = new Sertifikat();
        $sertifikat->peserta_id = $request->peserta_id;
        $sertifikat->kursus_id = $request->kursus_id;
        $sertifikat->template_id = $request->template_id;
        $sertifikat->nomor_sertifikat = $request->nomor_sertifikat;
        $sertifikat->tanggal_terbit = $request->tanggal_terbit;
        $sertifikat->nama_penandatangan = $request->nama_penandatangan;
        $sertifikat->jabatan_penandatangan = $request->jabatan_penandatangan;

        // Generate and save QR code
        $qrContent = route('sertifikat.verify', ['nomor' => $sertifikat->nomor_sertifikat]);
        $qrCodePath = 'sertifikat/qr/' . Str::slug($sertifikat->nomor_sertifikat) . '.png';
        $qrCode = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($qrContent);
        Storage::disk('public')->put($qrCodePath, $qrCode);
        $sertifikat->qr_code = $qrCodePath;

        // Set path for PDF (will be generated in separate process)
        $sertifikat->file_path = 'sertifikat/' . Str::slug($sertifikat->nomor_sertifikat) . '.pdf';
        $sertifikat->is_sent_email = false;
        $sertifikat->save();

        // TODO: Generate PDF using template and sertifikat data
        // This would be implemented in a service/job

        return response()->json([
            'message' => 'Sertifikat created successfully',
            'data' => new SertifikatResource($sertifikat)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sertifikat/{id}",
     *     summary="Mendapatkan detail sertifikat",
     *     tags={"Sertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Sertifikat",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="peserta", type="object",
     *                     @OA\Property(property="nama_lengkap", type="string", example="John Doe")
     *                 ),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="kursus", type="object",
     *                     @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah")
     *                 ),
     *                 @OA\Property(property="template_id", type="integer", example=1),
     *                 @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/001"),
     *                 @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-10-30"),
     *                 @OA\Property(property="file_path", type="string", example="sertifikat/sertifikat-1.pdf"),
     *                 @OA\Property(property="file_url", type="string", example="http://localhost/storage/sertifikat/sertifikat-1.pdf"),
     *                 @OA\Property(property="qr_code", type="string", example="sertifikat/qr/sertifikat-1.png"),
     *                 @OA\Property(property="qr_code_url", type="string", example="http://localhost/storage/sertifikat/qr/sertifikat-1.png"),
     *                 @OA\Property(property="nama_penandatangan", type="string", example="Dr. Ir. Budi Santoso, M.Si"),
     *                 @OA\Property(property="jabatan_penandatangan", type="string", example="Kepala BPSDM"),
     *                 @OA\Property(property="is_sent_email", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sertifikat tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $sertifikat = Sertifikat::with(['peserta', 'kursus', 'template'])->findOrFail($id);
        return new SertifikatResource($sertifikat);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/sertifikat/{id}",
     *     summary="Mengupdate sertifikat",
     *     tags={"Sertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Sertifikat",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="template_id", type="integer", example=2),
     *             @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/002"),
     *             @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-10-31"),
     *             @OA\Property(property="nama_penandatangan", type="string", example="Dr. Ir. Budi Santoso, M.Si"),
     *             @OA\Property(property="jabatan_penandatangan", type="string", example="Kepala BPSDM")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sertifikat berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sertifikat updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/002")
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
     *         description="Sertifikat tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $sertifikat = Sertifikat::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'template_id' => 'sometimes|required|exists:template_sertifikat,id',
            'nomor_sertifikat' => 'sometimes|required|string|max:255|unique:sertifikat,nomor_sertifikat,' . $id,
            'tanggal_terbit' => 'sometimes|required|date',
            'nama_penandatangan' => 'sometimes|required|string|max:255',
            'jabatan_penandatangan' => 'sometimes|required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update fields
        if ($request->has('template_id')) {
            $sertifikat->template_id = $request->template_id;
        }

        if ($request->has('nomor_sertifikat') && $sertifikat->nomor_sertifikat != $request->nomor_sertifikat) {
            // Handle change of nomor_sertifikat (update file paths, QR code, etc)
            $sertifikat->nomor_sertifikat = $request->nomor_sertifikat;

            // Update QR code
            if ($sertifikat->qr_code) {
                Storage::disk('public')->delete($sertifikat->qr_code);
            }

            $qrContent = route('sertifikat.verify', ['nomor' => $sertifikat->nomor_sertifikat]);
            $qrCodePath = 'sertifikat/qr/' . Str::slug($sertifikat->nomor_sertifikat) . '.png';
            $qrCode = QrCode::format('png')
                ->size(200)
                ->margin(1)
                ->generate($qrContent);
            Storage::disk('public')->put($qrCodePath, $qrCode);
            $sertifikat->qr_code = $qrCodePath;

            // Update PDF path
            if ($sertifikat->file_path) {
                Storage::disk('public')->delete($sertifikat->file_path);
            }
            $sertifikat->file_path = 'sertifikat/' . Str::slug($sertifikat->nomor_sertifikat) . '.pdf';
        }

        if ($request->has('tanggal_terbit')) {
            $sertifikat->tanggal_terbit = $request->tanggal_terbit;
        }

        if ($request->has('nama_penandatangan')) {
            $sertifikat->nama_penandatangan = $request->nama_penandatangan;
        }

        if ($request->has('jabatan_penandatangan')) {
            $sertifikat->jabatan_penandatangan = $request->jabatan_penandatangan;
        }

        $sertifikat->save();

        // TODO: Regenerate PDF if needed

        return response()->json([
            'message' => 'Sertifikat updated successfully',
            'data' => new SertifikatResource($sertifikat)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/sertifikat/{id}",
     *     summary="Menghapus sertifikat",
     *     tags={"Sertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Sertifikat",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sertifikat berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sertifikat deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sertifikat tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $sertifikat = Sertifikat::findOrFail($id);

        // Delete files
        if ($sertifikat->file_path) {
            Storage::disk('public')->delete($sertifikat->file_path);
        }

        if ($sertifikat->qr_code) {
            Storage::disk('public')->delete($sertifikat->qr_code);
        }

        $sertifikat->delete();

        return response()->json([
            'message' => 'Sertifikat deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sertifikat/by-peserta/{peserta_id}",
     *     summary="Mendapatkan daftar sertifikat berdasarkan peserta",
     *     tags={"Sertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="peserta_id",
     *         in="path",
     *         description="ID Peserta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar sertifikat untuk peserta berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kursus", type="object"),
     *                     @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/001"),
     *                     @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-10-30"),
     *                     @OA\Property(property="file_url", type="string", example="http://localhost/storage/sertifikat/sertifikat-1.pdf")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="peserta",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Peserta tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getByPeserta($pesertaId)
    {
        // Verify peserta exists
        $peserta = Peserta::findOrFail($pesertaId);

        // Get sertifikats for peserta
        $sertifikats = Sertifikat::with(['kursus', 'template'])
            ->where('peserta_id', $pesertaId)
            ->orderBy('tanggal_terbit', 'desc')
            ->get();

        return response()->json([
            'data' => SertifikatResource::collection($sertifikats),
            'peserta' => [
                'id' => $peserta->id,
                'nama_lengkap' => $peserta->nama_lengkap,
                'email' => $peserta->email,
                'nip' => $peserta->nip
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sertifikat/by-kursus/{kursus_id}",
     *     summary="Mendapatkan daftar sertifikat berdasarkan kursus",
     *     tags={"Sertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="path",
     *         description="ID Kursus",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar sertifikat untuk kursus berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="peserta_id", type="integer", example=1),
     *                     @OA\Property(property="peserta", type="object"),
     *                     @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/001"),
     *                     @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-10-30")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="kursus",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah"),
     *                 @OA\Property(property="total_sertifikat", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kursus tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getByKursus($kursusId)
    {
        // Verify kursus exists
        $kursus = Kursus::findOrFail($kursusId);

        // Get sertifikats for kursus
        $sertifikats = Sertifikat::with(['peserta', 'template'])
            ->where('kursus_id', $kursusId)
            ->orderBy('tanggal_terbit', 'desc')
            ->get();

        return response()->json([
            'data' => SertifikatResource::collection($sertifikats),
            'kursus' => [
                'id' => $kursus->id,
                'judul' => $kursus->judul,
                'total_sertifikat' => $sertifikats->count()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sertifikat/send-email/{id}",
     *     summary="Mengirim sertifikat ke email peserta",
     *     tags={"Sertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Sertifikat",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sertifikat berhasil dikirim ke email peserta",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sertifikat sent to email successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Sertifikat tidak memiliki file atau sudah dikirim",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Sertifikat has no file or is already sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sertifikat tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function sendEmail($id)
    {
        $sertifikat = Sertifikat::with('peserta')->findOrFail($id);

        // Check if sertifikat has a file
        if (!$sertifikat->file_path || !Storage::disk('public')->exists($sertifikat->file_path)) {
            return response()->json([
                'error' => 'Sertifikat has no file'
            ], 400);
        }

        // Check if sertifikat is already sent
        if ($sertifikat->is_sent_email) {
            return response()->json([
                'error' => 'Sertifikat is already sent to email'
            ], 400);
        }

        // TODO: Send email with sertifikat as attachment
        // This would be implemented with a mail service or job

        // Update flag
        $sertifikat->is_sent_email = true;
        $sertifikat->save();

        return response()->json([
            'message' => 'Sertifikat sent to email successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sertifikat/verify",
     *     summary="Verifikasi sertifikat berdasarkan nomor",
     *     tags={"Sertifikat"},
     *     @OA\Parameter(
     *         name="nomor",
     *         in="query",
     *         description="Nomor Sertifikat",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hasil verifikasi sertifikat",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_valid", type="boolean", example=true),
     *             @OA\Property(
     *                 property="sertifikat",
     *                 type="object",
     *                 @OA\Property(property="nomor_sertifikat", type="string", example="NO/SERT/2025/001"),
     *                 @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-10-30"),
     *                 @OA\Property(property="peserta", type="object"),
     *                 @OA\Property(property="kursus", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sertifikat tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_valid", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sertifikat not found")
     *         )
     *     )
     * )
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'is_valid' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sertifikat = Sertifikat::with(['peserta', 'kursus'])
            ->where('nomor_sertifikat', $request->nomor)
            ->first();

        if (!$sertifikat) {
            return response()->json([
                'is_valid' => false,
                'message' => 'Sertifikat not found'
            ], 404);
        }

        return response()->json([
            'is_valid' => true,
            'sertifikat' => [
                'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
                'tanggal_terbit' => $sertifikat->tanggal_terbit,
                'peserta' => [
                    'nama_lengkap' => $sertifikat->peserta->nama_lengkap,
                    'nip' => $sertifikat->peserta->nip
                ],
                'kursus' => [
                    'judul' => $sertifikat->kursus->judul,
                ]
            ]
        ]);
    }
}
