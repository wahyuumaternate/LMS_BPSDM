<?php

namespace Modules\Sertifikat\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Modules\Sertifikat\Entities\Sertifikat;

class QRCodeService
{
    /**
     * Generate QR Code for sertifikat
     *
     * @param Sertifikat $sertifikat
     * @return string Path to QR code file
     */
    public function generate(Sertifikat $sertifikat): string
    {
        $config = config('sertifikat.qr_code');
        $storagePath = config('sertifikat.storage.paths.qrcodes');

        // Generate verification URL
        $verificationUrl = route('sertifikat.verify', ['nomor' => $sertifikat->nomor_sertifikat]);

        // Generate QR code filename
        $fileName = $this->generateFileName($sertifikat);
        $filePath = $storagePath . '/' . $fileName;

        // Generate QR code image using PNG format with GD backend
        $qrCode = QrCode::format('png')
            ->size($config['size'])
            ->margin($config['margin'])
            ->errorCorrection('H')
            ->encoding('UTF-8')
            ->generate($verificationUrl);

        // Save to storage
        $disk = $config['disk'] ?? 'public';
        Storage::disk($disk)->put($filePath, $qrCode);

        return $filePath;
    }

    /**
     * Regenerate QR Code for existing sertifikat
     *
     * @param Sertifikat $sertifikat
     * @return string Path to new QR code file
     */
    public function regenerate(Sertifikat $sertifikat): string
    {
        // Delete old QR code if exists
        if ($sertifikat->qr_code_path) {
            $this->delete($sertifikat->qr_code_path);
        }

        return $this->generate($sertifikat);
    }

    /**
     * Delete QR code file
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
     * Get QR code URL
     *
     * @param Sertifikat $sertifikat
     * @return string|null
     */
    public function getUrl(Sertifikat $sertifikat): ?string
    {
        if (!$sertifikat->qr_code_path) {
            return null;
        }

        $disk = config('sertifikat.storage.disk', 'public');
        return Storage::disk($disk)->url($sertifikat->qr_code_path);
    }

    /**
     * Get QR code full path
     *
     * @param Sertifikat $sertifikat
     * @return string|null
     */
    public function getPath(Sertifikat $sertifikat): ?string
    {
        if (!$sertifikat->qr_code_path) {
            return null;
        }

        $disk = config('sertifikat.storage.disk', 'public');
        return Storage::disk($disk)->path($sertifikat->qr_code_path);
    }

    /**
     * Check if QR code exists
     *
     * @param Sertifikat $sertifikat
     * @return bool
     */
    public function exists(Sertifikat $sertifikat): bool
    {
        if (!$sertifikat->qr_code_path) {
            return false;
        }

        $disk = config('sertifikat.storage.disk', 'public');
        return Storage::disk($disk)->exists($sertifikat->qr_code_path);
    }

    /**
     * Generate filename for QR code
     *
     * @param Sertifikat $sertifikat
     * @return string
     */
    protected function generateFileName(Sertifikat $sertifikat): string
    {
        $slug = Str::slug($sertifikat->nomor_sertifikat);
        $format = config('sertifikat.qr_code.format', 'png');

        return "qr-{$slug}.{$format}";
    }

    /**
     * Generate QR code with custom content
     *
     * @param string $content
     * @param string $fileName
     * @return string Path to QR code file
     */
    public function generateCustom(string $content, string $fileName): string
    {
        $config = config('sertifikat.qr_code');
        $storagePath = config('sertifikat.storage.paths.qrcodes');
        $filePath = $storagePath . '/' . $fileName;

        // Generate QR code
        $qrCode = QrCode::format('png')
            ->size($config['size'])
            ->margin($config['margin'])
            ->errorCorrection('H')
            ->encoding('UTF-8')
            ->generate($content);

        // Save to storage
        $disk = $config['disk'] ?? 'public';
        Storage::disk($disk)->put($filePath, $qrCode);

        return $filePath;
    }

    /**
     * Get QR code as base64 string
     *
     * @param Sertifikat $sertifikat
     * @return string|null
     */
    public function getBase64(Sertifikat $sertifikat): ?string
    {
        if (!$this->exists($sertifikat)) {
            return null;
        }

        $disk = config('sertifikat.storage.disk', 'public');
        $content = Storage::disk($disk)->get($sertifikat->qr_code_path);
        $format = config('sertifikat.qr_code.format', 'png');

        return 'data:image/' . $format . ';base64,' . base64_encode($content);
    }

    /**
     * Generate QR code as SVG (alternative method)
     *
     * @param Sertifikat $sertifikat
     * @return string Path to QR code file
     */
    public function generateSvg(Sertifikat $sertifikat): string
    {
        $config = config('sertifikat.qr_code');
        $storagePath = config('sertifikat.storage.paths.qrcodes');

        // Generate verification URL
        $verificationUrl = route('sertifikat.verify', ['nomor' => $sertifikat->nomor_sertifikat]);

        // Generate QR code filename
        $slug = Str::slug($sertifikat->nomor_sertifikat);
        $fileName = "qr-{$slug}.svg";
        $filePath = $storagePath . '/' . $fileName;

        // Generate QR code as SVG (doesn't require imagick)
        $qrCode = QrCode::format('svg')
            ->size($config['size'])
            ->margin($config['margin'])
            ->errorCorrection('H')
            ->generate($verificationUrl);

        // Save to storage
        $disk = $config['disk'] ?? 'public';
        Storage::disk($disk)->put($filePath, $qrCode);

        return $filePath;
    }
}