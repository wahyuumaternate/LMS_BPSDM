<?php

namespace Modules\Sertifikat\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sertifikat\Entities\Sertifikat;
use Modules\Sertifikat\Entities\TemplateSertifikat;
use Modules\Kursus\Entities\Kursus;
use Modules\Kursus\Entities\PendaftaranKursus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;
use Carbon\Carbon;

class SertifikatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        // Filter by peserta (if peserta, only show their certificates)
        if ($user->tokenCan('peserta')) {
            $sertifikat = Sertifikat::with(['kursus:id,judul', 'template:id,nama_template'])
                ->where('peserta_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        // Filter by kursus (admin/instruktur)
        else if ($user->tokenCan('super_admin') || $user->tokenCan('instruktur')) {
            $query = Sertifikat::with(['peserta:id,nama_lengkap,nip', 'kursus:id,judul', 'template:id,nama_template']);
            // Filter by kursus
            if ($request->has('kursus_id')) {
                $query->where('kursus_id', $request->kursus_id);
                // If instruktur, check if they can access this course
                if ($user->tokenCan('instruktur')) {
                    $kursus = Kursus::find($request->kursus_id);
                    if (!$kursus || $kursus->admin_instruktur_id !== $user->id) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Anda tidak memiliki akses'
                        ], 403);
                    }
                }
            } else if ($user->tokenCan('instruktur')) {
                // Instruktur can only see certificates for courses they teach
                $kursusIds = Kursus::where('admin_instruktur_id', $user->id)->pluck('id');
                $query->whereIn('kursus_id', $kursusIds);
            }
            // Filter by peserta
            if ($request->has('peserta_id')) {
                $query->where('peserta_id', $request->peserta_id);
            }
            $sertifikat = $query->orderBy('created_at', 'desc')->get();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'data' => $sertifikat
        ]);
    }
    public function generate(Request $request, $kursusId)
    {
        $kursus = Kursus::findOrFail($kursusId);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:template_sertifikat,id',
            'peserta_ids' => 'nullable|array',
            'peserta_ids.*' => 'exists:peserta,id',
            'nama_penandatangan' => 'required|string',
            'jabatan_penandatangan' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        // Get template
        $template = TemplateSertifikat::findOrFail($request->template_id);
        // Get eligible peserta (completed course with passing grade)
        $query = PendaftaranKursus::where('kursus_id', $kursusId)
            ->where('status', 'selesai')
            ->whereNotNull('nilai_akhir')
            ->where('nilai_akhir', '>=', $kursus->passing_grade);
        // Filter by peserta_ids if provided
        if ($request->has('peserta_ids') && !empty($request->peserta_ids)) {
            $query->whereIn('peserta_id', $request->peserta_ids);
        }
        $pendaftaran = $query->with('peserta')->get();
        if ($pendaftaran->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada peserta yang memenuhi syarat untuk mendapatkan sertifikat'
            ], 404);
        }
        $generated = 0;
        $errors = [];
        $sertifikat = [];
        // Generate certificate for each eligible peserta
        foreach ($pendaftaran as $daftar) {
            // Check if certificate already exists
            $existingSertifikat = Sertifikat::where('peserta_id', $daftar->peserta_id)
                ->where('kursus_id', $kursusId)
                ->first();
            if ($existingSertifikat) {
                $errors[] = "Sertifikat untuk peserta {$daftar->peserta->nama_lengkap} sudah ada";
                continue;
            }
            try {
                // Generate unique certificate number
                $nomor = 'SERT/' . $kursus->kode_kursus . '/' . date('Y') . '/' . Str::random(5);
                // Generate QR code for verification
                $qrCode = Str::random(32);
                // Create certificate record
                $cert = Sertifikat::create([
                    'peserta_id' => $daftar->peserta_id,
                    'kursus_id' => $kursusId,
                    'template_id' => $template->id,
                    'nomor_sertifikat' => $nomor,
                    'tanggal_terbit' => Carbon::now(),
                    'qr_code' => $qrCode,
                    'nama_penandatangan' => $request->nama_penandatangan,
                    'jabatan_penandatangan' => $request->jabatan_penandatangan,
                    'is_sent_email' => false
                ]);
                // Generate PDF certificate (implementation depends on how you want to generate the certificate)
                // This is a placeholder - you would need to implement PDF generation based on the template
                $pdf = PDF::loadView('sertifikat::pdf_template', [
                    'sertifikat' => $cert,
                    'template' => $template,
                    'kursus' => $kursus,
                    'peserta' => $daftar->peserta
                ]);
                $filename = 'sertifikat_' . $daftar->peserta_id . '_' . $kursusId . '.pdf';
                $path = 'sertifikat/' . $filename;
                Storage::disk('public')->put($path, $pdf->output());
                $cert->file_path = $filename;
                $cert->save();
                $sertifikat[] = $cert;
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Error generating certificate for {$daftar->peserta->nama_lengkap}: " . $e->getMessage();
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => "Berhasil membuat {$generated} sertifikat" . (count($errors) > 0 ? " dengan " . count($errors) . " error" : ""),
            'data' => [
                'sertifikat' => $sertifikat,
                'errors' => $errors
            ]
        ]);
    }
    public function show($id)
    {
        $sertifikat = Sertifikat::with([
            'peserta:id,nama_lengkap,nip',
            'kursus:id,judul,admin_instruktur_id',
            'template:id,nama_template'
        ])->findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        // Check authorization
        if ($user->tokenCan('peserta')) {
            // Peserta can only view their own certificates
            if ($sertifikat->peserta_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses'
                ], 403);
            }
        } else if ($user->tokenCan('instruktur')) {
            // Instruktur can only view certificates for their courses
            if ($sertifikat->kursus->admin_instruktur_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses'
                ], 403);
            }
        } else if (!$user->tokenCan('super_admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Generate file URL
        if ($sertifikat->file_path) {
            $sertifikat->file_url = url('storage/sertifikat/' . $sertifikat->file_path);
        }
        // Generate verification URL
        $sertifikat->verification_url = route('sertifikat.verify', ['code' => $sertifikat->qr_code]);
        return response()->json([
            'status' => 'success',
            'data' => $sertifikat
        ]);
    }
    public function verify($code)
    {
        $sertifikat = Sertifikat::with([
            'peserta:id,nama_lengkap,nip',
            'kursus:id,judul'
        ])->where('qr_code', $code)->first();
        if (!$sertifikat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat tidak valid atau tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Sertifikat terverifikasi',
            'data' => [
                'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
                'nama_peserta' => $sertifikat->peserta->nama_lengkap,
                'nip' => $sertifikat->peserta->nip,
                'nama_kursus' => $sertifikat->kursus->judul,
                'tanggal_terbit' => $sertifikat->tanggal_terbit->format('d F Y'),
                'nama_penandatangan' => $sertifikat->nama_penandatangan,
                'jabatan_penandatangan' => $sertifikat->jabatan_penandatangan
            ]
        ]);
    }

    public function sendEmail($id)
    {
        $sertifikat = Sertifikat::with(['peserta', 'kursus:id,judul,admin_instruktur_id'])->findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        // Check authorization
        if (!($user->tokenCan('super_admin') || $sertifikat->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Check if peserta has email
        if (!$sertifikat->peserta->email) {
            return response()->json([
                'status' => 'error',
                'message' => 'Peserta tidak memiliki alamat email'
            ], 400);
        }
        // Check if file exists
        if (!$sertifikat->file_path || !Storage::disk('public')->exists('sertifikat/' . $sertifikat->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File sertifikat tidak ditemukan'
            ], 404);
        }
        try {
            // Send email with certificate (implementation depends on your email setup)
            Mail::to($sertifikat->peserta->email)->send(new SertifikatEmail($sertifikat));
            $sertifikat->is_sent_email = true;
            $sertifikat->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Sertifikat berhasil dikirim via email'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }
    public function download($id)
    {
        $sertifikat = Sertifikat::findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        // Check authorization
        if ($user->tokenCan('peserta')) {
            // Peserta can only download their own certificates
            if ($sertifikat->peserta_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses'
                ], 403);
            }
        } else if ($user->tokenCan('instruktur')) {
            // Instruktur can only download certificates for their courses
            $kursus = Kursus::find($sertifikat->kursus_id);
            if (!$kursus || $kursus->admin_instruktur_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses'
                ], 403);
            }
        } else if (!$user->tokenCan('super_admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Check if file exists
        if (!$sertifikat->file_path || !Storage::disk('public')->exists('sertifikat/' . $sertifikat->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File sertifikat tidak ditemukan'
            ], 404);
        }
        // Return file for download
        return Storage::disk('public')->download('sertifikat/' . $sertifikat->file_path, 'sertifikat_' . $sertifikat->nomor_sertifikat . '.pdf');
    }
}
