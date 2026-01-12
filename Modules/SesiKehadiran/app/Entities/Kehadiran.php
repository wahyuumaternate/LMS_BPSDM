<?php

namespace Modules\SesiKehadiran\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Peserta\Entities\Peserta;

class Kehadiran extends Model
{
    use HasFactory;

    protected $table = 'kehadiran';

    protected $fillable = ['sesi_id', 'peserta_id', 'waktu_checkin', 'waktu_checkout', 'status', 'durasi_menit', 'lokasi_checkin', 'lokasi_checkout', 'keterangan'];

    protected $casts = [
        'waktu_checkin' => 'datetime',
        'waktu_checkout' => 'datetime',
        'durasi_menit' => 'integer',
    ];

    /**
     * Relasi ke SesiKehadiran
     */
    public function sesi()
    {
        return $this->belongsTo(SesiKehadiran::class, 'sesi_id');
    }

    /**
     * Relasi ke Peserta
     */
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    /**
     * Scope untuk status hadir
     */
    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    /**
     * Scope untuk status terlambat
     */
    public function scopeTerlambat($query)
    {
        return $query->where('status', 'terlambat');
    }

    /**
     * Scope untuk status izin
     */
    public function scopeIzin($query)
    {
        return $query->where('status', 'izin');
    }

    /**
     * Scope untuk status sakit
     */
    public function scopeSakit($query)
    {
        return $query->where('status', 'sakit');
    }

    /**
     * Scope untuk status tidak hadir
     */
    public function scopeTidakHadir($query)
    {
        return $query->where('status', 'tidak_hadir');
    }

    /**
     * Hitung durasi kehadiran otomatis
     */
    public function hitungDurasi()
    {
        if ($this->waktu_checkin && $this->waktu_checkout) {
            $checkin = Carbon::parse($this->waktu_checkin)->timezone('Asia/Jayapura');
            $checkout = Carbon::parse($this->waktu_checkout)->timezone('Asia/Jayapura');
            $this->durasi_menit = $checkin->diffInMinutes($checkout);
            $this->save();
        }
    }

    /**
     * Cek apakah terlambat
     * TIDAK ADA BATASAN TERLAMBAT - Selama check-in sebelum waktu selesai = HADIR
     */
    public function isTerlambat()
    {
        // TIDAK ADA KONSEP TERLAMBAT
        // Selama check-in dalam periode sesi (sebelum waktu selesai) = HADIR
        return false;
    }

    /**
     * Cek apakah check-in masih dalam periode sesi yang valid
     */
    public function isCheckinValid()
    {
        if (!$this->waktu_checkin || !$this->sesi) {
            return false;
        }

        // Parse tanggal sesi
        $tanggalSesi = Carbon::parse($this->sesi->tanggal, 'Asia/Jayapura');

        // Parse waktu mulai
        $waktuMulaiParts = explode(':', $this->sesi->waktu_mulai);
        $waktuMulaiSesi = Carbon::create($tanggalSesi->year, $tanggalSesi->month, $tanggalSesi->day, (int) $waktuMulaiParts[0], (int) $waktuMulaiParts[1], (int) ($waktuMulaiParts[2] ?? 0), 'Asia/Jayapura');

        // Parse waktu selesai + durasi berlaku
        $waktuSelesaiParts = explode(':', $this->sesi->waktu_selesai);
        $waktuSelesaiSesi = Carbon::create($tanggalSesi->year, $tanggalSesi->month, $tanggalSesi->day, (int) $waktuSelesaiParts[0], (int) $waktuSelesaiParts[1], (int) ($waktuSelesaiParts[2] ?? 0), 'Asia/Jayapura')->addMinutes($this->sesi->durasi_berlaku_menit ?? 0);

        $waktuCheckin = Carbon::parse($this->waktu_checkin)->timezone('Asia/Jayapura');

        // Valid jika check-in antara waktu mulai dan waktu selesai
        return $waktuCheckin->between($waktuMulaiSesi, $waktuSelesaiSesi);
    }

    /**
     * Get selisih menit dari waktu mulai (untuk informasi saja)
     */
    public function getSelisihMenit()
    {
        if (!$this->waktu_checkin || !$this->sesi) {
            return 0;
        }

        $tanggalSesi = Carbon::parse($this->sesi->tanggal, 'Asia/Jayapura');
        $waktuMulaiParts = explode(':', $this->sesi->waktu_mulai);

        $waktuMulaiSesi = Carbon::create($tanggalSesi->year, $tanggalSesi->month, $tanggalSesi->day, (int) $waktuMulaiParts[0], (int) $waktuMulaiParts[1], (int) ($waktuMulaiParts[2] ?? 0), 'Asia/Jayapura');

        $waktuCheckin = Carbon::parse($this->waktu_checkin)->timezone('Asia/Jayapura');

        // Negative = lebih awal, Positive = setelah waktu mulai
        return $waktuCheckin->diffInMinutes($waktuMulaiSesi, false);
    }

    /**
     * Get formatted waktu checkin
     */
    public function getFormattedWaktuCheckin()
    {
        if (!$this->waktu_checkin) {
            return '-';
        }

        return Carbon::parse($this->waktu_checkin)->timezone('Asia/Jayapura')->format('d M Y, H:i') . ' WIT';
    }

    /**
     * Get formatted waktu checkout
     */
    public function getFormattedWaktuCheckout()
    {
        if (!$this->waktu_checkout) {
            return '-';
        }

        return Carbon::parse($this->waktu_checkout)->timezone('Asia/Jayapura')->format('d M Y, H:i') . ' WIT';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'hadir' => 'success',
            'terlambat' => 'warning',
            'izin' => 'info',
            'sakit' => 'primary',
            'tidak_hadir' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'tidak_hadir' => 'Tidak Hadir',
            default => 'Unknown',
        };
    }

    /**
     * Auto update status berdasarkan waktu check-in
     * RULE: Selama check-in dalam periode sesi = HADIR (tidak ada konsep terlambat)
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($kehadiran) {
            // Hanya auto-update status jika check-in dan status belum di-set manual
            if ($kehadiran->waktu_checkin && !$kehadiran->isDirty('status') && !in_array($kehadiran->status, ['izin', 'sakit'])) {
                // TIDAK ADA KONSEP TERLAMBAT
                // Selama check-in dalam periode sesi yang valid = HADIR
                if ($kehadiran->isCheckinValid()) {
                    $kehadiran->status = 'hadir';
                } else {
                    // Check-in di luar periode sesi
                    $kehadiran->status = 'tidak_hadir';
                }

                // Log status determination
                Log::info('Auto Status Determined (No Late):', [
                    'kehadiran_id' => $kehadiran->id,
                    'status' => $kehadiran->status,
                    'selisih_menit' => $kehadiran->getSelisihMenit(),
                    'is_valid' => $kehadiran->isCheckinValid(),
                ]);
            }
        });
    }
}
