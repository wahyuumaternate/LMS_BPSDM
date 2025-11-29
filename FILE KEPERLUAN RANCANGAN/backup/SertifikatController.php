<?php

namespace Modules\Sertifikat\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Sertifikat\Entities\Sertifikat;
use Modules\Peserta\Entities\Peserta;
use Modules\Kursus\Entities\Kursus;
use Modules\Sertifikat\Services\SertifikatGeneratorService;

class SertifikatController extends Controller
{
    protected $pdfGenerator;

    public function __construct(SertifikatGeneratorService $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Display a listing of sertifikat
     */
    public function index(Request $request)
    {
        $query = Sertifikat::with(['peserta', 'kursus']);

        // Filter by kursus
        if ($request->has('kursus_id') && $request->kursus_id != '') {
            $query->where('kursus_id', $request->kursus_id);
        }

        // Filter by peserta
        if ($request->has('peserta_id') && $request->peserta_id != '') {
            $query->where('peserta_id', $request->peserta_id);
        }

        // Search by nomor sertifikat or nama peserta
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_sertifikat', 'like', "%{$search}%")
                  ->orWhereHas('peserta', function($q2) use ($search) {
                      $q2->where('nama_lengkap', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by tanggal terbit
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal_terbit', '>=', $request->tanggal_dari);
        }
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal_terbit', '<=', $request->tanggal_sampai);
        }

        $sertifikats = $query->latest('tanggal_terbit')->paginate(15);

        // Get data for filter dropdowns
        $kursusList = Kursus::orderBy('judul')->get();
        $pesertaList = Peserta::orderBy('nama_lengkap')->get();

        // If AJAX request, return only table partial
        if ($request->ajax()) {
            return view('sertifikat::partials.sertifikat_table', compact('sertifikats'));
        }

        return view('sertifikat::index', compact('sertifikats', 'kursusList', 'pesertaList'));
    }

    /**
     * Show the form for creating a new sertifikat
     */
    public function create()
    {
        $pesertaList = Peserta::orderBy('nama_lengkap')->get();
        $kursusList = Kursus::orderBy('judul')->get();
        
        // Get default signatories from config
        $defaultSignatories = config('sertifikat.default_signatories');

        return view('sertifikat::create', compact('pesertaList', 'kursusList', 'defaultSignatories'));
    }

    /**
     * Store a newly created sertifikat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'peserta_id' => 'required|exists:pesertas,id',
            'kursus_id' => 'required|exists:kursus,id',
            'tanggal_terbit' => 'required|date',
            'tempat_terbit' => 'required|string|max:100',
            'nama_penandatangan1' => 'required|string|max:255',
            'jabatan_penandatangan1' => 'required|string|max:255',
            'nip_penandatangan1' => 'nullable|string|max:50',
            'nama_penandatangan2' => 'nullable|string|max:255',
            'jabatan_penandatangan2' => 'nullable|string|max:255',
            'nip_penandatangan2' => 'nullable|string|max:50',
            'template_name' => 'required|string|in:default,tema_2,tema_3',
            'notes' => 'nullable|string',
            'generate_now' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Check if certificate already exists
            $exists = Sertifikat::where('peserta_id', $validated['peserta_id'])
                ->where('kursus_id', $validated['kursus_id'])
                ->exists();

            if ($exists) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Sertifikat untuk peserta dan kursus ini sudah ada!');
            }

            // Create sertifikat
            $sertifikat = Sertifikat::create([
                'peserta_id' => $validated['peserta_id'],
                'kursus_id' => $validated['kursus_id'],
                'tanggal_terbit' => $validated['tanggal_terbit'],
                'tempat_terbit' => $validated['tempat_terbit'],
                'nama_penandatangan1' => $validated['nama_penandatangan1'],
                'jabatan_penandatangan1' => $validated['jabatan_penandatangan1'],
                'nip_penandatangan1' => $validated['nip_penandatangan1'],
                'nama_penandatangan2' => $validated['nama_penandatangan2'],
                'jabatan_penandatangan2' => $validated['jabatan_penandatangan2'],
                'nip_penandatangan2' => $validated['nip_penandatangan2'],
                'template_name' => $validated['template_name'] ?? 'default',
                'notes' => $validated['notes'],
                'status' => 'published',
            ]);

            // Set verification URL (without QR code for now)
            $sertifikat->update([
                'verification_url' => route('sertifikat.verify', ['nomor' => $sertifikat->nomor_sertifikat])
            ]);

            // Generate PDF if requested
            if ($request->has('generate_now') && $request->generate_now == '1') {
                try {
                    $pdfPath = $this->pdfGenerator->generate($sertifikat);
                    $sertifikat->update(['file_path' => $pdfPath]);
                } catch (\Exception $e) {
                    \Log::error('PDF Generation failed: ' . $e->getMessage());
                    // Don't fail the transaction, just log the error
                }
            }

            DB::commit();

            return redirect()
                ->route('sertifikat.show', $sertifikat->id)
                ->with('success', 'Sertifikat berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating sertifikat: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat sertifikat: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sertifikat
     */
    public function show($id)
    {
        $sertifikat = Sertifikat::with(['peserta', 'kursus'])->findOrFail($id);
        
        // Get file URL if exists
        $fileUrl = null;
        $downloadUrl = null;
        if ($sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path)) {
            $fileUrl = Storage::disk('public')->url($sertifikat->file_path);
            $downloadUrl = route('sertifikat.download', $sertifikat->id);
        }

        // If AJAX request, return JSON
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => array_merge($sertifikat->toArray(), [
                    'file_url' => $fileUrl,
                    'download_url' => $downloadUrl,
                    'formatted_tanggal' => $sertifikat->formatted_tanggal,
                ])
            ]);
        }
        
        return view('sertifikat::show', compact('sertifikat', 'fileUrl', 'downloadUrl'));
    }

    /**
     * Show the form for editing the specified sertifikat
     */
    public function edit($id)
    {
        $sertifikat = Sertifikat::with(['peserta', 'kursus'])->findOrFail($id);
        $pesertaList = Peserta::orderBy('nama_lengkap')->get();
        $kursusList = Kursus::orderBy('judul')->get();

        return view('sertifikat::edit', compact('sertifikat', 'pesertaList', 'kursusList'));
    }

    /**
     * Update the specified sertifikat
     */
    public function update(Request $request, $id)
    {
        $sertifikat = Sertifikat::findOrFail($id);

        $validated = $request->validate([
            'tanggal_terbit' => 'required|date',
            'tempat_terbit' => 'required|string|max:100',
            'nama_penandatangan1' => 'required|string|max:255',
            'jabatan_penandatangan1' => 'required|string|max:255',
            'nip_penandatangan1' => 'nullable|string|max:50',
            'nama_penandatangan2' => 'nullable|string|max:255',
            'jabatan_penandatangan2' => 'nullable|string|max:255',
            'nip_penandatangan2' => 'nullable|string|max:50',
            'template_name' => 'required|string|in:default,tema_2,tema_3',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,published,revoked',
            'regenerate_pdf' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $sertifikat->update($validated);

            // Regenerate PDF if requested
            if ($request->has('regenerate_pdf') && $request->regenerate_pdf == '1') {
                // Delete old PDF
                if ($sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path)) {
                    Storage::disk('public')->delete($sertifikat->file_path);
                }

                try {
                    // Generate new PDF
                    $pdfPath = $this->pdfGenerator->generate($sertifikat);
                    $sertifikat->update(['file_path' => $pdfPath]);
                } catch (\Exception $e) {
                    \Log::error('PDF Regeneration failed: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()
                ->route('sertifikat.show', $sertifikat->id)
                ->with('success', 'Sertifikat berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating sertifikat: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate sertifikat: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified sertifikat
     */
    public function destroy($id)
    {
        $sertifikat = Sertifikat::findOrFail($id);

        DB::beginTransaction();
        try {
            // Delete PDF file
            if ($sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path)) {
                Storage::disk('public')->delete($sertifikat->file_path);
            }

            // Delete QR code (if exists in future)
            if ($sertifikat->qr_code_path && Storage::disk('public')->exists($sertifikat->qr_code_path)) {
                Storage::disk('public')->delete($sertifikat->qr_code_path);
            }

            // Delete sertifikat
            $sertifikat->delete();

            DB::commit();

            // If AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sertifikat berhasil dihapus!'
                ]);
            }

            return redirect()
                ->route('sertifikat.index')
                ->with('success', 'Sertifikat berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting sertifikat: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus sertifikat: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus sertifikat: ' . $e->getMessage());
        }
    }

   /**
     * Preview sertifikat PDF (FIXED VERSION)
     */
    public function preview($id)
    {
        try {
            // Load dengan eager loading
            $sertifikat = Sertifikat::with(['peserta.opd', 'kursus.modul.materis'])
                ->findOrFail($id);

            // Validasi data wajib
            if (!$sertifikat->peserta) {
                throw new \Exception('Data peserta tidak ditemukan. Pastikan peserta sudah terdaftar.');
            }

            if (!$sertifikat->kursus) {
                throw new \Exception('Data kursus tidak ditemukan. Pastikan kursus sudah terdaftar.');
            }

            // Validasi nama peserta
            if (empty($sertifikat->peserta->nama_lengkap)) {
                throw new \Exception('Nama peserta tidak boleh kosong.');
            }

            // Validasi judul kursus
            if (empty($sertifikat->kursus->judul) && empty($sertifikat->kursus->nama_kursus)) {
                throw new \Exception('Judul kursus tidak boleh kosong.');
            }

            // Increase memory limit untuk generate PDF
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', '120');

            // Log untuk debugging
            Log::info('Generating PDF preview for sertifikat: ' . $sertifikat->id);

            // Generate PDF
            $pdf = $this->pdfGenerator->generatePDF($sertifikat);

            // Stream PDF dengan nama file yang aman
            $filename = 'preview-' . str_replace(['/', ' '], '-', $sertifikat->nomor_sertifikat) . '.pdf';
            
            return $pdf->stream($filename);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Sertifikat not found: ' . $id);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sertifikat tidak ditemukan'
                ], 404);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Sertifikat tidak ditemukan');
                
        } catch (\Exception $e) {
            Log::error('PDF Preview failed for ID: ' . $id);
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            // Jika debug mode aktif, tampilkan error detail
            if (config('app.debug')) {
                return response()->view('sertifikat::errors.pdf-debug', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'id' => $id,
                    'sertifikat' => $sertifikat ?? null
                ], 500);
            }
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat preview PDF: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Gagal membuat preview PDF: ' . $e->getMessage());
        }
    }


    /**
     * Download sertifikat PDF
     */
    public function download($id)
    {
        $sertifikat = Sertifikat::with(['peserta', 'kursus.modul.materis'])->findOrFail($id);

        // Check if PDF exists
        if (!$sertifikat->file_path || !Storage::disk('public')->exists($sertifikat->file_path)) {
            try {
                // Generate PDF if not exists
                $pdfPath = $this->pdfGenerator->generate($sertifikat);
                $sertifikat->update(['file_path' => $pdfPath]);
            } catch (\Exception $e) {
                \Log::error('PDF Generation for download failed: ' . $e->getMessage());
                \Log::error($e->getTraceAsString());
                
                return redirect()
                    ->back()
                    ->with('error', 'Gagal generate PDF untuk download: ' . $e->getMessage());
            }
        }

        $filePath = Storage::disk('public')->path($sertifikat->file_path);
        $fileName = 'Sertifikat-' . Str::slug($sertifikat->nomor_sertifikat) . '.pdf';

        return response()->download($filePath, $fileName);
    }

    /**
     * Send sertifikat to email
     */
    public function sendEmail($id)
    {
        $sertifikat = Sertifikat::with(['peserta', 'kursus'])->findOrFail($id);

        // Validate that peserta has email
        if (!$sertifikat->peserta->email) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Peserta tidak memiliki alamat email!'
                ], 400);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Peserta tidak memiliki alamat email!');
        }

        // Check if PDF exists
        if (!$sertifikat->file_path || !Storage::disk('public')->exists($sertifikat->file_path)) {
            try {
                // Generate PDF if not exists
                $pdfPath = $this->pdfGenerator->generate($sertifikat);
                $sertifikat->update(['file_path' => $pdfPath]);
            } catch (\Exception $e) {
                \Log::error('PDF Generation for email failed: ' . $e->getMessage());
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal generate PDF untuk email'
                    ], 500);
                }
                
                return redirect()
                    ->back()
                    ->with('error', 'Gagal generate PDF untuk email');
            }
        }

        try {
            // TODO: Send email with PDF attachment
            // Mail::to($sertifikat->peserta->email)->send(new SertifikatMail($sertifikat));

            // Update email status
            $sertifikat->update([
                'is_sent_email' => true,
                'sent_email_at' => now()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sertifikat berhasil dikirim ke email peserta!'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Sertifikat berhasil dikirim ke email peserta!');

        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF for sertifikat
     */
    public function generatePdf($id)
    {
        $sertifikat = Sertifikat::with(['peserta', 'kursus.modul.materis'])->findOrFail($id);

        try {
            // Delete old PDF if exists
            if ($sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path)) {
                Storage::disk('public')->delete($sertifikat->file_path);
            }

            // Generate new PDF
            $pdfPath = $this->pdfGenerator->generate($sertifikat);
            $sertifikat->update(['file_path' => $pdfPath]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PDF Sertifikat berhasil di-generate!'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'PDF Sertifikat berhasil di-generate!');

        } catch (\Exception $e) {
            \Log::error('PDF Generation failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate PDF: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Bulk generate sertifikat for a kursus
     */
    public function bulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'kursus_id' => 'required|exists:kursus,id',
            'peserta_ids' => 'required|array',
            'peserta_ids.*' => 'exists:pesertas,id',
            'tanggal_terbit' => 'required|date',
            'tempat_terbit' => 'required|string|max:100',
            'nama_penandatangan1' => 'required|string|max:255',
            'jabatan_penandatangan1' => 'required|string|max:255',
            'nip_penandatangan1' => 'nullable|string|max:50',
            'nama_penandatangan2' => 'nullable|string|max:255',
            'jabatan_penandatangan2' => 'nullable|string|max:255',
            'nip_penandatangan2' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $created = 0;
            $errors = [];

            foreach ($validated['peserta_ids'] as $pesertaId) {
                try {
                    // Check if sertifikat already exists
                    $exists = Sertifikat::where('peserta_id', $pesertaId)
                        ->where('kursus_id', $validated['kursus_id'])
                        ->exists();

                    if ($exists) {
                        $peserta = Peserta::find($pesertaId);
                        $errors[] = "Sertifikat untuk {$peserta->nama_lengkap} sudah ada";
                        continue;
                    }

                    // Create sertifikat
                    $sertifikat = Sertifikat::create([
                        'peserta_id' => $pesertaId,
                        'kursus_id' => $validated['kursus_id'],
                        'tanggal_terbit' => $validated['tanggal_terbit'],
                        'tempat_terbit' => $validated['tempat_terbit'],
                        'nama_penandatangan1' => $validated['nama_penandatangan1'],
                        'jabatan_penandatangan1' => $validated['jabatan_penandatangan1'],
                        'nip_penandatangan1' => $validated['nip_penandatangan1'] ?? null,
                        'nama_penandatangan2' => $validated['nama_penandatangan2'] ?? null,
                        'jabatan_penandatangan2' => $validated['jabatan_penandatangan2'] ?? null,
                        'nip_penandatangan2' => $validated['nip_penandatangan2'] ?? null,
                        'template_name' => 'default',
                        'status' => 'published',
                    ]);

                    // Set verification URL
                    $sertifikat->update([
                        'verification_url' => route('sertifikat.verify', ['nomor' => $sertifikat->nomor_sertifikat])
                    ]);

                    // Generate PDF
                    try {
                        $pdfPath = $this->pdfGenerator->generate($sertifikat);
                        $sertifikat->update(['file_path' => $pdfPath]);
                    } catch (\Exception $e) {
                        \Log::error("PDF Generation failed for Sertifikat ID {$sertifikat->id}: " . $e->getMessage());
                        // Continue even if PDF generation fails
                    }

                    $created++;

                } catch (\Exception $e) {
                    $peserta = Peserta::find($pesertaId);
                    $errors[] = "Error untuk {$peserta->nama_lengkap}: " . $e->getMessage();
                    \Log::error("Bulk generate error for peserta {$pesertaId}: " . $e->getMessage());
                }
            }

            DB::commit();

            $message = "Berhasil membuat {$created} sertifikat";
            if (count($errors) > 0) {
                $message .= ". Dengan " . count($errors) . " error.";
            }

            return redirect()
                ->route('sertifikat.index', ['kursus_id' => $validated['kursus_id']])
                ->with('success', $message)
                ->with('errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk generate failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat sertifikat: ' . $e->getMessage());
        }
    }

    /**
     * Show bulk generate form
     */
    public function bulkGenerateForm(Request $request)
    {
        $kursusId = $request->get('kursus_id');
        
        if (!$kursusId) {
            $kursusList = Kursus::orderBy('judul')->get();
            return view('sertifikat::bulk-select-kursus', compact('kursusList'));
        }

        $kursus = Kursus::findOrFail($kursusId);
        
        // Get pesertas who are enrolled in this kursus but don't have sertifikat yet
        $availablePesertas = Peserta::whereHas('pendaftaranKursus', function($q) use ($kursusId) {
                $q->where('kursus_id', $kursusId);
            })
            ->whereDoesntHave('sertifikats', function($q) use ($kursusId) {
                $q->where('kursus_id', $kursusId);
            })
            ->orderBy('nama_lengkap')
            ->get();

        $defaultSignatories = config('sertifikat.default_signatories');

        return view('sertifikat::bulk-generate', compact('kursus', 'availablePesertas', 'defaultSignatories'));
    }

    /**
     * Revoke sertifikat
     */
    public function revoke($id)
    {
        $sertifikat = Sertifikat::findOrFail($id);

        try {
            $sertifikat->update(['status' => 'revoked']);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sertifikat berhasil dicabut!'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Sertifikat berhasil dicabut!');

        } catch (\Exception $e) {
            \Log::error('Error revoking sertifikat: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mencabut sertifikat'
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Gagal mencabut sertifikat');
        }
    }

    /**
     * Restore revoked sertifikat
     */
    public function restore($id)
    {
        $sertifikat = Sertifikat::findOrFail($id);

        try {
            $sertifikat->update(['status' => 'published']);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sertifikat berhasil dipulihkan!'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Sertifikat berhasil dipulihkan!');

        } catch (\Exception $e) {
            \Log::error('Error restoring sertifikat: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memulihkan sertifikat'
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Gagal memulihkan sertifikat');
        }
    }

    /**
     * Get sertifikat by peserta
     */
    public function getByPeserta($pesertaId)
    {
        $peserta = Peserta::findOrFail($pesertaId);
        $sertifikats = Sertifikat::with(['kursus'])
            ->where('peserta_id', $pesertaId)
            ->orderBy('tanggal_terbit', 'desc')
            ->get();

        return view('sertifikat::by-peserta', compact('peserta', 'sertifikats'));
    }

    /**
     * Get sertifikat by kursus
     */
    public function getByKursus($kursusId)
    {
        $kursus = Kursus::findOrFail($kursusId);
        $sertifikats = Sertifikat::with(['peserta'])
            ->where('kursus_id', $kursusId)
            ->orderBy('tanggal_terbit', 'desc')
            ->paginate(20);

        return view('sertifikat::by-kursus', compact('kursus', 'sertifikats'));
    }
}