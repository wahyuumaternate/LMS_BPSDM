<?php

namespace Modules\SesiKehadiran\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Kursus\Entities\Kursus;

class SesiKehadiran extends Model
{
    use HasFactory;

    /**
     * Nama tabel terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'sesi_kehadiran';

    /**
     * Atribut yang dapat diisi.
     *
     * @var array
     */
    protected $fillable = [
        'kursus_id',
        'pertemuan_ke',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'qr_code_checkin',
        'qr_code_checkout',
        'durasi_berlaku_menit',
        'status'
    ];

    /**
     * Atribut yang harus dikonversi.
     *
     * @var array
     */
    protected $casts = [
        'tanggal' => 'date',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime'
    ];

    /**
     * Relasi dengan model Kursus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kursus()
    {
        return $this->belongsTo(Kursus::class);
    }

    /**
     * Relasi dengan model Kehadiran.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'sesi_id');
    }

    /**
     * Mendapatkan daftar opsi status yang tersedia.
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return [
            'scheduled' => 'Terjadwal',
            'ongoing' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
    }

    /**
     * Mendapatkan URL QR code untuk check-in.
     *
     * @return string|null
     */
    public function getCheckinQrCodeUrl()
    {
        if (!$this->qr_code_checkin) {
            return null;
        }

        return asset('storage/qrcodes/' . $this->qr_code_checkin);
    }

    /**
     * Mendapatkan URL QR code untuk check-out.
     *
     * @return string|null
     */
    public function getCheckoutQrCodeUrl()
    {
        if (!$this->qr_code_checkout) {
            return null;
        }

        return asset('storage/qrcodes/' . $this->qr_code_checkout);
    }

    /**
     * Mengecek apakah sesi sedang aktif.
     *
     * @return bool
     */
    public function isActive()
    {
        $now = now();
        $tanggal = $this->tanggal->format('Y-m-d');
        $waktuMulai = $tanggal . ' ' . $this->waktu_mulai->format('H:i:s');
        $waktuSelesai = $tanggal . ' ' . $this->waktu_selesai->format('H:i:s');

        $startTime = \Carbon\Carbon::parse($waktuMulai);
        $endTime = \Carbon\Carbon::parse($waktuSelesai);

        return $now->between($startTime, $endTime) && $this->status !== 'cancelled';
    }

    /**
     * Mendapatkan persentase kehadiran untuk sesi ini.
     *
     * @return float
     */
    public function getKehadiranPercentage()
    {
        $totalPeserta = $this->kursus->pendaftaran()->where('status', 'aktif')->count();
        if ($totalPeserta === 0) {
            return 0;
        }

        $hadir = $this->kehadiran()->whereIn('status', ['hadir', 'terlambat'])->count();
        return ($hadir / $totalPeserta) * 100;
    }

    /**
     * Scope query untuk mendapatkan sesi yang aktif saat ini.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('tanggal', now()->format('Y-m-d'))
            ->where('waktu_mulai', '<=', now()->format('H:i:s'))
            ->where('waktu_selesai', '>=', now()->format('H:i:s'))
            ->where('status', '<>', 'cancelled');
    }

    /**
     * Scope query untuk mendapatkan sesi yang akan datang.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where(function ($q) {
            $q->where('tanggal', '>', now()->format('Y-m-d'))
                ->orWhere(function ($sq) {
                    $sq->where('tanggal', '=', now()->format('Y-m-d'))
                        ->where('waktu_mulai', '>', now()->format('H:i:s'));
                });
        })->where('status', '<>', 'cancelled');
    }
}
