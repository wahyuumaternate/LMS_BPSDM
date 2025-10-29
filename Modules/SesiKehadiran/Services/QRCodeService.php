<?php

namespace Modules\SesiKehadiran\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Modules\SesiKehadiran\Entities\SesiKehadiran;

class QRCodeService
{
    /**
     * Path penyimpanan QR Code
     * 
     * @var string
     */
    protected $storagePath = 'qrcodes';

    /**
     * Disk yang digunakan untuk menyimpan QR Code
     * 
     * @var string
     */
    protected $disk = 'public';

    /**
     * Generate QR Code dasar
     * 
     * @param string $data Data untuk QR code
     * @param int $size Ukuran QR code (pixel)
     * @return mixed
     */
    public function generate($data, $size = 300)
    {
        return QrCode::size($size)->generate($data);
    }

    /**
     * Generate QR Code dengan format PNG
     * 
     * @param string $data Data untuk QR code
     * @param int $size Ukuran QR code (pixel)
     * @return mixed
     */
    public function generatePng($data, $size = 300)
    {
        return QrCode::format('png')
            ->size($size)
            ->errorCorrection('H')
            ->margin(1)
            ->generate($data);
    }

    /**
     * Generate QR Code dengan format SVG
     * 
     * @param string $data Data untuk QR code
     * @param int $size Ukuran QR code (pixel)
     * @return mixed
     */
    public function generateSvg($data, $size = 300)
    {
        return QrCode::format('svg')
            ->size($size)
            ->errorCorrection('H')
            ->margin(1)
            ->generate($data);
    }

    /**
     * Generate QR Code untuk sesi kehadiran
     * 
     * @param int $sesiId ID sesi kehadiran
     * @param string $type Tipe (checkin/checkout)
     * @return string Nama file QR code
     */
    public function generateForSesi($sesiId, $type = 'checkin')
    {
        // Dapatkan sesi kehadiran
        $sesi = SesiKehadiran::findOrFail($sesiId);

        // Generate token unik
        $token = $this->generateToken($sesiId, $type);

        // URL untuk scan QR code
        $url = route('api.kehadiran.scan', ['token' => $token, 'type' => $type]);

        // Generate QR code
        $qrCode = $this->generatePng($url);

        // Nama file QR code
        $filename = "sesi-{$sesiId}-{$type}-{$token}.png";

        // Simpan QR code
        $this->save($qrCode, $filename);

        return $filename;
    }

    /**
     * Generate token unik untuk QR code
     * 
     * @param int $sesiId ID sesi
     * @param string $type Tipe (checkin/checkout)
     * @return string
     */
    protected function generateToken($sesiId, $type)
    {
        // Generate random token
        return Str::random(32);
    }

    /**
     * Simpan QR code ke storage
     * 
     * @param mixed $qrCode Data QR code
     * @param string $filename Nama file
     * @return string Path file yang disimpan
     */
    public function save($qrCode, $filename)
    {
        $path = $this->storagePath . '/' . $filename;
        Storage::disk($this->disk)->put($path, $qrCode);

        return $path;
    }

    /**
     * Dapatkan URL untuk QR code
     * 
     * @param string $filename Nama file QR code
     * @return string|null
     */
    public function getUrl($filename)
    {
        if (empty($filename)) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->storagePath . '/' . $filename);
    }

    /**
     * Generate QR code dengan logo
     * 
     * @param string $data Data QR code
     * @param string $logoPath Path file logo
     * @param float $mergeRatio Rasio penggabungan (0.0 - 1.0)
     * @return mixed
     */
    public function generateWithLogo($data, $logoPath, $mergeRatio = 0.3)
    {
        // Pastikan file logo ada
        if (!file_exists($logoPath)) {
            throw new \Exception("Logo file not found at: {$logoPath}");
        }

        // Untuk merger, kita harus menggunakan format PNG
        return QrCode::format('png')
            ->size(300)
            ->errorCorrection('H') // Gunakan koreksi tinggi untuk logo
            ->merge($logoPath, $mergeRatio, true)
            ->generate($data);
    }

    /**
     * Generate QR code dengan warna kustom
     * 
     * @param string $data Data QR code
     * @param array $color Warna QR code [R, G, B]
     * @param array $backgroundColor Warna latar [R, G, B]
     * @return mixed
     */
    public function generateWithColor($data, array $color, array $backgroundColor = [255, 255, 255])
    {
        return QrCode::format('png')
            ->size(300)
            ->color($color[0], $color[1], $color[2])
            ->backgroundColor($backgroundColor[0], $backgroundColor[1], $backgroundColor[2])
            ->generate($data);
    }

    /**
     * Generate QR code dengan style tertentu
     * 
     * @param string $data Data QR code
     * @param string $style Style QR code (square, dot, round)
     * @return mixed
     */
    public function generateWithStyle($data, $style = 'square')
    {
        if (!in_array($style, ['square', 'dot', 'round'])) {
            $style = 'square';
        }

        return QrCode::format('png')
            ->size(300)
            ->style($style)
            ->errorCorrection('H')
            ->generate($data);
    }

    /**
     * Generate QR code dengan gradient
     * 
     * @param string $data Data QR code
     * @param array $startColor Warna awal [R, G, B]
     * @param array $endColor Warna akhir [R, G, B]
     * @param string $type Tipe gradient (vertical, horizontal, diagonal, radial)
     * @return mixed
     */
    public function generateWithGradient($data, array $startColor, array $endColor, $type = 'vertical')
    {
        // Cek apakah tipe gradient valid
        if (!in_array($type, ['vertical', 'horizontal', 'diagonal', 'radial'])) {
            $type = 'vertical';
        }

        return QrCode::format('png')
            ->size(300)
            ->gradient(
                $startColor[0],
                $startColor[1],
                $startColor[2],
                $endColor[0],
                $endColor[1],
                $endColor[2],
                $type
            )
            ->generate($data);
    }

    /**
     * Generate QR code untuk email
     * 
     * @param string $email Alamat email
     * @param string $subject Subjek email
     * @param string $body Isi email
     * @return mixed
     */
    public function generateEmail($email, $subject = '', $body = '')
    {
        return QrCode::format('png')
            ->size(300)
            ->email($email, $subject, $body);
    }

    /**
     * Generate QR code untuk nomor telepon
     * 
     * @param string $phoneNumber Nomor telepon
     * @return mixed
     */
    public function generatePhone($phoneNumber)
    {
        return QrCode::format('png')
            ->size(300)
            ->phoneNumber($phoneNumber);
    }

    /**
     * Generate QR code untuk SMS
     * 
     * @param string $phoneNumber Nomor telepon
     * @param string $message Pesan SMS
     * @return mixed
     */
    public function generateSms($phoneNumber, $message = '')
    {
        return QrCode::format('png')
            ->size(300)
            ->SMS($phoneNumber, $message);
    }

    /**
     * Generate QR code untuk geo location
     * 
     * @param float $latitude Garis lintang
     * @param float $longitude Garis bujur
     * @param float $altitude Ketinggian (opsional)
     * @return mixed
     */
    public function generateGeo($latitude, $longitude, $altitude = null)
    {
        if ($altitude !== null) {
            return QrCode::format('png')
                ->size(300)
                ->geo($latitude, $longitude, $altitude);
        }

        return QrCode::format('png')
            ->size(300)
            ->geo($latitude, $longitude);
    }

    /**
     * Verifikasi token QR code
     * 
     * @param string $token Token dari QR code
     * @param string $type Tipe (checkin/checkout)
     * @return array Hasil verifikasi
     */
    public function verifyToken($token, $type)
    {
        // Contoh verifikasi token sederhana
        // Pada implementasi nyata, Anda perlu menyimpan token di database
        // atau menyimpan data verifikasi di tempat yang aman

        // Format token: sesi-{sesiId}-{type}-{token}.png
        $pattern = "/^sesi-(\d+)-{$type}-(.+)\.png$/";

        // Cek file-file QR code yang ada
        $files = Storage::disk($this->disk)->files($this->storagePath);

        foreach ($files as $file) {
            $filename = basename($file);

            if (preg_match($pattern, $filename, $matches) && $matches[2] === $token) {
                return [
                    'valid' => true,
                    'sesi_id' => $matches[1],
                    'type' => $type,
                    'token' => $token
                ];
            }
        }

        // Token tidak valid
        return [
            'valid' => false
        ];
    }
}
