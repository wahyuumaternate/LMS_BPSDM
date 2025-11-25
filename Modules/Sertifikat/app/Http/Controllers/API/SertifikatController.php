<?php

namespace Modules\Sertifikat\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Sertifikat\Entities\Sertifikat;
use Modules\Kursus\Entities\Kursus;

/**
 * @OA\Tag(
 *     name="Sertifikat Peserta",
 *     description="API Endpoints untuk peserta melihat dan mendownload sertifikat mereka"
 * )
 */
class SertifikatController extends Controller
{
    /**
     * Constructor - Pastikan hanya peserta yang bisa akses
     */
    public function __construct()
    {
        $this->middleware('auth:peserta');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/sertifikat",
     *     summary="Mendapatkan daftar sertifikat peserta yang login",
     *     tags={"Sertifikat Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Filter berdasarkan ID Kursus"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "published", "revoked"}),
     *         description="Filter berdasarkan status sertifikat"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nomor_sertifikat", type="string", example="CERT/2025/001"),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kursus_nama", type="string", example="Kursus Laravel"),
     *                     @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-01-15"),
     *                     @OA\Property(property="tempat_terbit", type="string", example="Jakarta"),
     *                     @OA\Property(property="status", type="string", example="published"),
     *                     @OA\Property(property="verification_url", type="string", example="https://example.com/verify/CERT-2025-001", nullable=true),
     *                     @OA\Property(property="download_url", type="string", example="https://example.com/api/v1/student/sertifikat/1/download", nullable=true),
     *                     @OA\Property(property="file_available", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $user = auth('peserta')->user();

        $query = Sertifikat::with(['kursus'])
            ->where('peserta_id', $user->id);

        // Filter berdasarkan kursus
        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sertifikats = $query->orderBy('tanggal_terbit', 'desc')->paginate(10);

        return response()->json([
            'data' => $sertifikats->getCollection()->map(function ($sertifikat) {
                return $this->formatSertifikat($sertifikat);
            }),
            'meta' => [
                'current_page' => $sertifikats->currentPage(),
                'last_page' => $sertifikats->lastPage(),
                'per_page' => $sertifikats->perPage(),
                'total' => $sertifikats->total(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/sertifikat/{id}",
     *     summary="Mendapatkan detail sertifikat",
     *     tags={"Sertifikat Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Sertifikat"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nomor_sertifikat", type="string", example="CERT/2025/001"),
     *                 @OA\Property(property="kursus", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Kursus Laravel"),
     *                     @OA\Property(property="deskripsi", type="string", example="Kursus pemrograman Laravel")
     *                 ),
     *                 @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-01-15"),
     *                 @OA\Property(property="tempat_terbit", type="string", example="Jakarta"),
     *                 @OA\Property(property="status", type="string", example="published"),
     *                 @OA\Property(property="penandatangan1", type="object",
     *                     @OA\Property(property="nama", type="string", example="Dr. John Doe"),
     *                     @OA\Property(property="jabatan", type="string", example="Direktur"),
     *                     @OA\Property(property="nip", type="string", example="198501012010011001", nullable=true)
     *                 ),
     *                 @OA\Property(property="penandatangan2", type="object", nullable=true,
     *                     @OA\Property(property="nama", type="string", example="Prof. Jane Smith"),
     *                     @OA\Property(property="jabatan", type="string", example="Kepala Program"),
     *                     @OA\Property(property="nip", type="string", example="198601012011012001", nullable=true)
     *                 ),
     *                 @OA\Property(property="verification_url", type="string", example="https://example.com/verify/CERT-2025-001", nullable=true),
     *                 @OA\Property(property="download_url", type="string", example="https://example.com/api/v1/student/sertifikat/1/download", nullable=true),
     *                 @OA\Property(property="file_available", type="boolean", example=true),
     *                 @OA\Property(property="notes", type="string", example="Catatan tambahan", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Tidak dapat mengakses sertifikat peserta lain"),
     *     @OA\Response(response=404, description="Sertifikat tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show($id)
    {
        $user = auth('peserta')->user();

        $sertifikat = Sertifikat::with(['kursus'])->findOrFail($id);

        // Pastikan peserta hanya bisa melihat sertifikat miliknya sendiri
        if ($sertifikat->peserta_id !== $user->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat sertifikat ini'
            ], 403);
        }

        return response()->json([
            'data' => $this->formatSertifikatDetail($sertifikat)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/sertifikat/{id}/download",
     *     summary="Download file PDF sertifikat",
     *     tags={"Sertifikat Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Sertifikat"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File PDF sertifikat",
     *         @OA\MediaType(
     *             mediaType="application/pdf"
     *         )
     *     ),
     *     @OA\Response(response=400, description="Sertifikat belum published atau file tidak tersedia"),
     *     @OA\Response(response=403, description="Forbidden - Tidak dapat mengakses sertifikat peserta lain"),
     *     @OA\Response(response=404, description="Sertifikat tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function download($id)
    {
        $user = auth('peserta')->user();

        $sertifikat = Sertifikat::findOrFail($id);

        // Pastikan peserta hanya bisa mendownload sertifikat miliknya sendiri
        if ($sertifikat->peserta_id !== $user->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mendownload sertifikat ini'
            ], 403);
        }

        // Cek apakah sertifikat sudah published
        if ($sertifikat->status !== 'published') {
            return response()->json([
                'message' => 'Sertifikat belum tersedia untuk didownload. Status: ' . $sertifikat->status
            ], 400);
        }

        // Cek apakah file PDF tersedia
        if (!$sertifikat->file_path || !Storage::disk('public')->exists($sertifikat->file_path)) {
            return response()->json([
                'message' => 'File sertifikat tidak tersedia. Silakan hubungi administrator.'
            ], 400);
        }

        $filePath = Storage::disk('public')->path($sertifikat->file_path);
        $fileName = 'Sertifikat-' . str_replace(['/', ' '], '-', $sertifikat->nomor_sertifikat) . '.pdf';

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/sertifikat/{id}/view",
     *     summary="View/preview file PDF sertifikat di browser",
     *     tags={"Sertifikat Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Sertifikat"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File PDF sertifikat untuk preview",
     *         @OA\MediaType(
     *             mediaType="application/pdf"
     *         )
     *     ),
     *     @OA\Response(response=400, description="Sertifikat belum published atau file tidak tersedia"),
     *     @OA\Response(response=403, description="Forbidden - Tidak dapat mengakses sertifikat peserta lain"),
     *     @OA\Response(response=404, description="Sertifikat tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function view($id)
    {
        $user = auth('peserta')->user();

        $sertifikat = Sertifikat::findOrFail($id);

        // Pastikan peserta hanya bisa melihat sertifikat miliknya sendiri
        if ($sertifikat->peserta_id !== $user->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat sertifikat ini'
            ], 403);
        }

        // Cek apakah sertifikat sudah published
        if ($sertifikat->status !== 'published') {
            return response()->json([
                'message' => 'Sertifikat belum tersedia untuk dilihat. Status: ' . $sertifikat->status
            ], 400);
        }

        // Cek apakah file PDF tersedia
        if (!$sertifikat->file_path || !Storage::disk('public')->exists($sertifikat->file_path)) {
            return response()->json([
                'message' => 'File sertifikat tidak tersedia. Silakan hubungi administrator.'
            ], 400);
        }

        $filePath = Storage::disk('public')->path($sertifikat->file_path);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Sertifikat-' . str_replace(['/', ' '], '-', $sertifikat->nomor_sertifikat) . '.pdf"'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/sertifikat/kursus/{kursus_id}",
     *     summary="Mendapatkan sertifikat peserta berdasarkan kursus",
     *     tags={"Sertifikat Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Kursus"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="kursus", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="Kursus Laravel")
     *             ),
     *             @OA\Property(property="data", type="object", nullable=true,
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nomor_sertifikat", type="string", example="CERT/2025/001"),
     *                 @OA\Property(property="status", type="string", example="published")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Kursus tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getByKursus($kursusId)
    {
        $user = auth('peserta')->user();
        $kursus = Kursus::findOrFail($kursusId);

        $sertifikat = Sertifikat::where('peserta_id', $user->id)
            ->where('kursus_id', $kursusId)
            ->first();

        return response()->json([
            'kursus' => [
                'id' => $kursus->id,
                'nama' => $kursus->nama ?? $kursus->judul,
                'deskripsi' => $kursus->deskripsi,
            ],
            'data' => $sertifikat ? $this->formatSertifikatDetail($sertifikat) : null
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/sertifikat/check/{kursus_id}",
     *     summary="Cek ketersediaan sertifikat untuk kursus tertentu",
     *     tags={"Sertifikat Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Kursus"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status ketersediaan sertifikat",
     *         @OA\JsonContent(
     *             @OA\Property(property="has_certificate", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="published", nullable=true),
     *             @OA\Property(property="sertifikat_id", type="integer", example=1, nullable=true),
     *             @OA\Property(property="message", type="string", example="Sertifikat tersedia")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Kursus tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function checkAvailability($kursusId)
    {
        $user = auth('peserta')->user();
        
        // Cek apakah kursus ada
        $kursus = Kursus::findOrFail($kursusId);

        $sertifikat = Sertifikat::where('peserta_id', $user->id)
            ->where('kursus_id', $kursusId)
            ->first();

        if ($sertifikat) {
            $message = 'Sertifikat tersedia';
            
            if ($sertifikat->status === 'draft') {
                $message = 'Sertifikat sedang dalam proses';
            } elseif ($sertifikat->status === 'revoked') {
                $message = 'Sertifikat telah dicabut';
            }

            return response()->json([
                'has_certificate' => true,
                'status' => $sertifikat->status,
                'sertifikat_id' => $sertifikat->id,
                'can_download' => $sertifikat->status === 'published' && $sertifikat->file_path,
                'message' => $message
            ]);
        }

        return response()->json([
            'has_certificate' => false,
            'status' => null,
            'sertifikat_id' => null,
            'can_download' => false,
            'message' => 'Sertifikat belum tersedia'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/sertifikat/summary",
     *     summary="Mendapatkan ringkasan sertifikat peserta",
     *     tags={"Sertifikat Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Ringkasan sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="summary", type="object",
     *                 @OA\Property(property="total_sertifikat", type="integer", example=5),
     *                 @OA\Property(property="total_published", type="integer", example=4),
     *                 @OA\Property(property="total_draft", type="integer", example=1),
     *                 @OA\Property(property="total_revoked", type="integer", example=0)
     *             ),
     *             @OA\Property(property="recent_certificates", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nomor_sertifikat", type="string", example="CERT/2025/001"),
     *                     @OA\Property(property="kursus_nama", type="string", example="Kursus Laravel"),
     *                     @OA\Property(property="tanggal_terbit", type="string", format="date", example="2025-01-15")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function summary()
    {
        $user = auth('peserta')->user();

        $sertifikats = Sertifikat::where('peserta_id', $user->id)->get();

        $summary = [
            'total_sertifikat' => $sertifikats->count(),
            'total_published' => $sertifikats->where('status', 'published')->count(),
            'total_draft' => $sertifikats->where('status', 'draft')->count(),
            'total_revoked' => $sertifikats->where('status', 'revoked')->count(),
        ];

        // Get 5 most recent published certificates
        $recentCertificates = Sertifikat::with('kursus')
            ->where('peserta_id', $user->id)
            ->where('status', 'published')
            ->orderBy('tanggal_terbit', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($sertifikat) {
                $fileAvailable = $sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path);
                $baseUrl = url('/api/v1/student/sertifikat');
                
                return [
                    'id' => $sertifikat->id,
                    'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
                    'kursus_nama' => $sertifikat->kursus->nama ?? $sertifikat->kursus->judul ?? 'N/A',
                    'tanggal_terbit' => $sertifikat->tanggal_terbit,
                    'download_url' => $fileAvailable ? "{$baseUrl}/{$sertifikat->id}/download" : null,
                ];
            });

        return response()->json([
            'summary' => $summary,
            'recent_certificates' => $recentCertificates
        ]);
    }

    /**
     * Format data sertifikat untuk response list
     */
    private function formatSertifikat(Sertifikat $sertifikat): array
    {
        $fileAvailable = $sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path);
        
        // Generate URLs manually to avoid route naming issues
        $baseUrl = url('/api/v1/student/sertifikat');
        
        return [
            'id' => $sertifikat->id,
            'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
            'kursus_id' => $sertifikat->kursus_id,
            'kursus_nama' => $sertifikat->kursus->nama ?? $sertifikat->kursus->judul ?? 'N/A',
            'tanggal_terbit' => $sertifikat->tanggal_terbit,
            'tempat_terbit' => $sertifikat->tempat_terbit,
            'status' => $sertifikat->status,
            'verification_url' => $sertifikat->status === 'published' ? $sertifikat->verification_url : null,
            'download_url' => ($sertifikat->status === 'published' && $fileAvailable) 
                ? "{$baseUrl}/{$sertifikat->id}/download"
                : null,
            'view_url' => ($sertifikat->status === 'published' && $fileAvailable) 
                ? "{$baseUrl}/{$sertifikat->id}/view"
                : null,
            'file_available' => $fileAvailable,
        ];
    }

    /**
     * Format data sertifikat detail untuk response
     */
    private function formatSertifikatDetail(Sertifikat $sertifikat): array
    {
        $fileAvailable = $sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path);
        
        // Generate URLs manually to avoid route naming issues
        $baseUrl = url('/api/v1/student/sertifikat');
        
        return [
            'id' => $sertifikat->id,
            'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
            'kursus' => [
                'id' => $sertifikat->kursus->id,
                'nama' => $sertifikat->kursus->nama ?? $sertifikat->kursus->judul ?? 'N/A',
                'deskripsi' => $sertifikat->kursus->deskripsi ?? null,
            ],
            'tanggal_terbit' => $sertifikat->tanggal_terbit,
            'tempat_terbit' => $sertifikat->tempat_terbit,
            'status' => $sertifikat->status,
            'penandatangan1' => [
                'nama' => $sertifikat->nama_penandatangan1,
                'jabatan' => $sertifikat->jabatan_penandatangan1,
                'nip' => $sertifikat->nip_penandatangan1,
            ],
            'penandatangan2' => $sertifikat->nama_penandatangan2 ? [
                'nama' => $sertifikat->nama_penandatangan2,
                'jabatan' => $sertifikat->jabatan_penandatangan2,
                'nip' => $sertifikat->nip_penandatangan2,
            ] : null,
            'verification_url' => $sertifikat->status === 'published' ? $sertifikat->verification_url : null,
            'download_url' => ($sertifikat->status === 'published' && $fileAvailable) 
                ? "{$baseUrl}/{$sertifikat->id}/download"
                : null,
            'view_url' => ($sertifikat->status === 'published' && $fileAvailable) 
                ? "{$baseUrl}/{$sertifikat->id}/view"
                : null,
            'file_available' => $fileAvailable,
            'notes' => $sertifikat->notes,
            'template_name' => $sertifikat->template_name,
        ];
    }
}