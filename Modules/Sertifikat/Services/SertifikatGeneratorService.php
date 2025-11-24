<?php

namespace Modules\Sertifikat\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Sertifikat\Entities\Sertifikat;

class SertifikatGeneratorService
{
    /**
     * Generate PDF sertifikat and save to storage
     *
     * @param Sertifikat $sertifikat
     * @return string Path to PDF file
     */
    public function generate(Sertifikat $sertifikat): string
    {
        $pdf = $this->generatePDF($sertifikat);

        // Generate filename and path
        $fileName = $this->generateFileName($sertifikat);
        $storagePath = config('sertifikat.storage.paths.certificates', 'certificates/pdf');
        $filePath = $storagePath . '/' . $fileName;

        // Save PDF to storage
        $disk = config('sertifikat.storage.disk', 'public');
        Storage::disk($disk)->put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * Generate PDF object (without saving)
     *
     * @param Sertifikat $sertifikat
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePDF(Sertifikat $sertifikat)
    {
        // Load template configuration
        $templateName = $sertifikat->template_name ?? config('sertifikat.default_template', 'modern');
        $templateConfig = config("sertifikat.templates.{$templateName}", config('sertifikat.templates.modern'));

        // Prepare data for template
        $data = $this->prepareTemplateData($sertifikat, $templateConfig);

        // Load view and generate PDF
        $viewName = "sertifikat::templates.{$templateName}";
        
        // Check if view exists, fallback to modern template
        if (!view()->exists($viewName)) {
            $viewName = "sertifikat::templates.modern";
        }

        $pdf = Pdf::loadView($viewName, $data);

        // Set PDF options based on template config
        if (isset($templateConfig['page'])) {
            $pdf->setPaper(
                $templateConfig['page']['format'] ?? 'A4',
                $templateConfig['page']['orientation'] ?? 'landscape'
            );
        }

        // Set DPI for better quality
        $pdf->setOption('dpi', 96);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isPhpEnabled', true);

        return $pdf;
    }

    /**
     * Prepare data for PDF template
     *
     * @param Sertifikat $sertifikat
     * @param array $templateConfig
     * @return array
     */
    protected function prepareTemplateData(Sertifikat $sertifikat, array $templateConfig): array
    {
        // Load relationships
       $sertifikat->load([
        'peserta.opd', 
        'kursus.modul.materis' // PENTING: Tambahkan ini!
    ]);

        // Get logo paths - support both module and public storage
        $logoBpsdmPath = $this->getAssetPath($templateConfig['logo_bpsdm'] ?? null);
        $logoPermdaPath = $this->getAssetPath($templateConfig['logo_pemda'] ?? null);
        $backgroundPath = $this->getAssetPath($templateConfig['background'] ?? null);

        // Get QR code path if enabled
        $qrCodePath = null;
        if (config('sertifikat.qr_code.enabled', true) && $sertifikat->qr_code_path) {
            $disk = config('sertifikat.storage.disk', 'public');
            $qrCodePath = Storage::disk($disk)->path($sertifikat->qr_code_path);
        }

        // Get signature paths
        $disk = config('sertifikat.storage.disk', 'public');
        $ttd1Path = $sertifikat->tanda_tangan1_path 
            ? Storage::disk($disk)->path($sertifikat->tanda_tangan1_path) 
            : null;
        $ttd2Path = $sertifikat->tanda_tangan2_path 
            ? Storage::disk($disk)->path($sertifikat->tanda_tangan2_path) 
            : null;

        // Format tanggal terbit
        $tanggalTerbit = $sertifikat->tanggal_terbit;
        $tanggalFormatted = $tanggalTerbit->locale('id')->isoFormat('dddd, D MMMM YYYY');
        
        // Extract location and date
        $location = $sertifikat->tempat_terbit ?? 'Ternate';
        $dateOnly = $tanggalTerbit->locale('id')->isoFormat('D MMMM YYYY');
        
        return [
            'sertifikat' => $sertifikat,
            'peserta' => $sertifikat->peserta,
            'kursus' => $sertifikat->kursus,
            
            // Template config
            'config' => $templateConfig,
            
            // Assets
            'qr_code' => $qrCodePath,
            'logo_bpsdm' => $logoBpsdmPath,
            'logo_pemda' => $logoPermdaPath,
            'background' => $backgroundPath,
            'tanda_tangan1' => $ttd1Path,
            'tanda_tangan2' => $ttd2Path,
            
            // Formatted data
            'nomor_sertifikat' => $this->formatNomorSertifikat($sertifikat->nomor_sertifikat),
            'tanggal_terbit' => ucfirst($location) . ', ' . $dateOnly,
            'tanggal_terbit_full' => $tanggalFormatted,
            'nama_peserta' => strtoupper($sertifikat->peserta->nama_lengkap),
            'detail_peserta' => $this->formatDetailPeserta($sertifikat->peserta),
            
            // Signatories
            'penandatangan1' => [
                'nama' => $sertifikat->nama_penandatangan1,
                'jabatan' => $sertifikat->jabatan_penandatangan1,
                'nip' => $sertifikat->nip_penandatangan1,
                'signature' => $ttd1Path,
            ],
            'penandatangan2' => [
                'nama' => $sertifikat->nama_penandatangan2 ?? '',
                'jabatan' => $sertifikat->jabatan_penandatangan2 ?? '',
                'nip' => $sertifikat->nip_penandatangan2 ?? '',
                'signature' => $ttd2Path,
            ],
            
            // Additional info
            'footer_text' => $templateConfig['footer_text'] ?? '',
            'verification_url' => $sertifikat->verification_url ?? $this->generateVerificationUrl($sertifikat),
        ];
    }

    /**
     * Format nomor sertifikat (SERT/2025/00012 -> SERT / 2025 / 00012)
     *
     * @param string $nomor
     * @return string
     */
    protected function formatNomorSertifikat(string $nomor): string
    {
        $parts = explode('/', $nomor);
        return implode(' / ', $parts);
    }

    /**
     * Generate verification URL
     *
     * @param Sertifikat $sertifikat
     * @return string
     */
    protected function generateVerificationUrl(Sertifikat $sertifikat): string
    {
        // Support both old and new config structure
        $baseUrl = config('sertifikat.verification.base_url') 
                   ?? config('sertifikat.verification_url') 
                   ?? url('/verify-certificate/');
        
        return rtrim($baseUrl, '/') . '/' . $sertifikat->nomor_sertifikat;
    }

    /**
     * Format detail peserta (NIP, etc)
     *
     * @param \Modules\Peserta\Entities\Peserta $peserta
     * @return string|null
     */
    protected function formatDetailPeserta($peserta): ?string
    {
        if ($peserta->nip) {
            return "NIP. {$peserta->nip}";
        }

        return null;
    }

    /**
     * Get asset path (convert module notation to real path)
     *
     * @param string|null $assetPath
     * @return string|null
     */
    protected function getAssetPath(?string $assetPath): ?string
    {
        if (!$assetPath) {
            return null;
        }

        // Check if it's module notation (e.g., Sertifikat::logos/bpsdm.png)
        if (Str::contains($assetPath, '::')) {
            [$module, $path] = explode('::', $assetPath);
            $modulePath = module_path($module, 'Resources/assets/' . $path);
            
            if (file_exists($modulePath)) {
                return $modulePath;
            }
        }

        // Check if it's storage path (e.g., templates/logos/logo.png)
        $disk = config('sertifikat.storage.disk', 'public');
        if (Storage::disk($disk)->exists($assetPath)) {
            return Storage::disk($disk)->path($assetPath);
        }

        // Check if it's absolute path
        if (file_exists($assetPath)) {
            return $assetPath;
        }

        // Check if it's in public path
        $publicPath = public_path($assetPath);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        // Return null if file not found
        return null;
    }

    /**
     * Generate filename for PDF
     *
     * @param Sertifikat $sertifikat
     * @return string
     */
    protected function generateFileName(Sertifikat $sertifikat): string
    {
        $slug = Str::slug($sertifikat->nomor_sertifikat);
        $timestamp = now()->timestamp;

        return "sertifikat-{$slug}-{$timestamp}.pdf";
    }

    /**
     * Regenerate PDF for existing sertifikat
     *
     * @param Sertifikat $sertifikat
     * @return string Path to new PDF file
     */
    public function regenerate(Sertifikat $sertifikat): string
    {
        // Delete old PDF if exists
        if ($sertifikat->file_path) {
            $this->delete($sertifikat->file_path);
        }

        return $this->generate($sertifikat);
    }

    /**
     * Delete PDF file
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        $disk = config('sertifikat.storage.disk', 'public');

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Get PDF URL
     *
     * @param Sertifikat $sertifikat
     * @return string|null
     */
    public function getUrl(Sertifikat $sertifikat): ?string
    {
        if (!$sertifikat->file_path) {
            return null;
        }

        $disk = config('sertifikat.storage.disk', 'public');
        return Storage::disk($disk)->url($sertifikat->file_path);
    }

    /**
     * Get PDF full path
     *
     * @param Sertifikat $sertifikat
     * @return string|null
     */
    public function getPath(Sertifikat $sertifikat): ?string
    {
        if (!$sertifikat->file_path) {
            return null;
        }

        $disk = config('sertifikat.storage.disk', 'public');
        return Storage::disk($disk)->path($sertifikat->file_path);
    }

    /**
     * Check if PDF exists
     *
     * @param Sertifikat $sertifikat
     * @return bool
     */
    public function exists(Sertifikat $sertifikat): bool
    {
        if (!$sertifikat->file_path) {
            return false;
        }

        $disk = config('sertifikat.storage.disk', 'public');
        return Storage::disk($disk)->exists($sertifikat->file_path);
    }

    /**
     * Bulk generate PDFs for multiple sertifikat
     *
     * @param array $sertifikatIds
     * @return array ['success' => [], 'failed' => []]
     */
    public function bulkGenerate(array $sertifikatIds): array
    {
        $success = [];
        $failed = [];

        foreach ($sertifikatIds as $id) {
            try {
                $sertifikat = Sertifikat::findOrFail($id);
                $path = $this->generate($sertifikat);
                
                $sertifikat->update(['file_path' => $path]);
                $success[] = $id;
                
            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => $success,
            'failed' => $failed
        ];
    }

    /**
     * Generate preview HTML (for debugging)
     *
     * @param Sertifikat $sertifikat
     * @return string
     */
    public function generatePreviewHtml(Sertifikat $sertifikat): string
    {
        $templateName = $sertifikat->template_name ?? config('sertifikat.default_template', 'modern');
        $templateConfig = config("sertifikat.templates.{$templateName}", config('sertifikat.templates.modern'));
        $data = $this->prepareTemplateData($sertifikat, $templateConfig);

        $viewName = "sertifikat::templates.{$templateName}";
        
        if (!view()->exists($viewName)) {
            $viewName = "sertifikat::templates.modern";
        }

        return view($viewName, $data)->render();
    }

    /**
     * Download PDF directly (stream)
     *
     * @param Sertifikat $sertifikat
     * @return \Illuminate\Http\Response
     */
    public function download(Sertifikat $sertifikat)
    {
        $pdf = $this->generatePDF($sertifikat);
        $fileName = $this->generateFileName($sertifikat);
        
        return $pdf->download($fileName);
    }

    /**
     * Stream PDF in browser
     *
     * @param Sertifikat $sertifikat
     * @return \Illuminate\Http\Response
     */
    public function stream(Sertifikat $sertifikat)
    {
        $pdf = $this->generatePDF($sertifikat);
        $fileName = $this->generateFileName($sertifikat);
        
        return $pdf->stream($fileName);
    }
}