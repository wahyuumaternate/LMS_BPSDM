<?php

namespace Modules\SesiKehadiran\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Kursus\Entities\Kursus;

class SesiKehadiran extends Model
{
    use HasFactory;

    protected $table = 'sesi_kehadiran';

    protected $fillable = [
        'kursus_id',
        'pertemuan_ke',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_berlaku_menit',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'durasi_berlaku_menit' => 'integer',
        'pertemuan_ke' => 'integer',
    ];

    /**
     * Relasi ke Kursus
     */
    public function kursus()
    {
        return $this->belongsTo(Kursus::class, 'kursus_id');
    }

    /**
     * Relasi ke Kehadiran
     */
    public function kehadirans()
    {
        return $this->hasMany(Kehadiran::class, 'sesi_id');
    }

    /**
     * Scope untuk sesi yang scheduled
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope untuk sesi yang ongoing
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    /**
     * Scope untuk sesi yang completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope untuk sesi yang cancelled
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Cek apakah sesi masih berlaku untuk check-in
     */
    public function isBerlakuCheckIn()
    {
        $now = now()->timezone(config('app.timezone', 'Asia/Jayapura'));

        $tanggalStr = $this->tanggal instanceof \Carbon\Carbon
            ? $this->tanggal->format('Y-m-d')
            : \Carbon\Carbon::parse($this->tanggal)->format('Y-m-d');

        $waktuMulai = \Carbon\Carbon::parse($tanggalStr . ' ' . $this->waktu_mulai, config('app.timezone', 'Asia/Jayapura'));
        $batasWaktu = $waktuMulai->copy()->addMinutes($this->durasi_berlaku_menit);

        return $now->between($waktuMulai, $batasWaktu) && $this->status === 'ongoing';
    }

    /**
     * Hitung total peserta hadir
     */
    public function totalHadir()
    {
        return $this->kehadirans()
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();
    }

    /**
     * Hitung total peserta tidak hadir
     */
    public function totalTidakHadir()
    {
        return $this->kehadirans()
            ->where('status', 'tidak_hadir')
            ->count();
    }

    /**
     * Hitung persentase kehadiran
     */
    public function persentaseKehadiran()
    {
        $total = $this->kehadirans()->count();
        if ($total === 0) {
            return 0;
        }

        $hadir = $this->totalHadir();
        return round(($hadir / $total) * 100, 2);
    }

    /**
     * Boot method untuk auto-update status
     */
    protected static function boot()
    {
        parent::boot();

        // Auto update status saat model di-load/retrieved
        static::retrieved(function ($sesi) {
            $sesi->updateStatusOtomatis();
        });

        // Auto update status sebelum disimpan
        static::saving(function ($sesi) {
            if (!$sesi->isDirty('status') || $sesi->status === 'scheduled') {
                $sesi->setStatusBerdasarkanWaktu();
            }
        });
    }

    /**
     * Update status otomatis berdasarkan waktu
     */
    public function updateStatusOtomatis()
    {
        $statusLama = $this->status;
        $this->setStatusBerdasarkanWaktu();

        // Simpan jika status berubah dan bukan cancelled
        if ($statusLama !== $this->status && $this->status !== 'cancelled') {
            $this->saveQuietly(); // Save tanpa trigger event
        }
    }

    /**
     * Set status berdasarkan waktu saat ini
     */
    protected function setStatusBerdasarkanWaktu()
    {
        // Jangan ubah jika sudah cancelled
        if ($this->status === 'cancelled') {
            return;
        }

        $now = now();

        // Format tanggal dengan benar
        $tanggalStr = $this->tanggal instanceof \Carbon\Carbon
            ? $this->tanggal->format('Y-m-d')
            : \Carbon\Carbon::parse($this->tanggal)->format('Y-m-d');

        $waktuMulai = \Carbon\Carbon::parse($tanggalStr . ' ' . $this->waktu_mulai);
        $waktuSelesai = \Carbon\Carbon::parse($tanggalStr . ' ' . $this->waktu_selesai);

        // Jika sudah lewat waktu selesai → completed
        if ($now->greaterThan($waktuSelesai)) {
            $this->status = 'completed';
        }
        // Jika sedang berlangsung (antara waktu mulai dan selesai) → ongoing
        elseif ($now->between($waktuMulai, $waktuSelesai)) {
            $this->status = 'ongoing';
        }
        // Jika belum dimulai → scheduled
        elseif ($now->lessThan($waktuMulai)) {
            $this->status = 'scheduled';
        }
    }

    /**
     * Method untuk force update status manual
     */
    public function forceUpdateStatus()
    {
        $this->setStatusBerdasarkanWaktu();
        return $this->save();
    }

    /**
     * Method helper untuk debugging waktu
     */
    public function getInfoWaktu()
    {
        $now = now()->timezone(config('app.timezone', 'Asia/Jayapura'));

        $tanggalStr = $this->tanggal instanceof \Carbon\Carbon
            ? $this->tanggal->format('Y-m-d')
            : \Carbon\Carbon::parse($this->tanggal)->format('Y-m-d');

        $waktuMulai = \Carbon\Carbon::parse($tanggalStr . ' ' . $this->waktu_mulai, config('app.timezone', 'Asia/Jayapura'));
        $waktuSelesai = \Carbon\Carbon::parse($tanggalStr . ' ' . $this->waktu_selesai, config('app.timezone', 'Asia/Jayapura'));

        return [
            'timezone' => config('app.timezone', 'Asia/Jayapura'),
            'waktu_sekarang' => $now->format('Y-m-d H:i:s T'),
            'waktu_mulai' => $waktuMulai->format('Y-m-d H:i:s T'),
            'waktu_selesai' => $waktuSelesai->format('Y-m-d H:i:s T'),
            'status' => $this->status,
            'belum_mulai' => $now->lessThan($waktuMulai),
            'sedang_berlangsung' => $now->between($waktuMulai, $waktuSelesai),
            'sudah_selesai' => $now->greaterThan($waktuSelesai),
        ];
    }
}
