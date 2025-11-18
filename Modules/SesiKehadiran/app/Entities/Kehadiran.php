<?php

namespace Modules\SesiKehadiran\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;

class Kehadiran extends Model
{
    use HasFactory;

    protected $table = 'kehadiran';

    protected $fillable = [
        'sesi_id',
        'peserta_id',
        'waktu_checkin',
        'waktu_checkout',
        'status',
        'durasi_menit',
        'lokasi_checkin',
        'lokasi_checkout',
        'keterangan',
    ];

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
            $checkin = \Carbon\Carbon::parse($this->waktu_checkin);
            $checkout = \Carbon\Carbon::parse($this->waktu_checkout);
            $this->durasi_menit = $checkin->diffInMinutes($checkout);
            $this->save();
        }
    }

    /**
     * Cek apakah peserta terlambat
     */
    public function isTerlambat()
    {
        if (!$this->waktu_checkin || !$this->sesi) {
            return false;
        }

        $waktuMulai = \Carbon\Carbon::parse($this->sesi->tanggal . ' ' . $this->sesi->waktu_mulai);
        $waktuCheckin = \Carbon\Carbon::parse($this->waktu_checkin);

        return $waktuCheckin->greaterThan($waktuMulai);
    }

    /**
     * Auto update status berdasarkan waktu check-in
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($kehadiran) {
            // Auto set status terlambat jika check-in setelah waktu mulai
            if ($kehadiran->waktu_checkin && !$kehadiran->isDirty('status')) {
                if ($kehadiran->isTerlambat()) {
                    $kehadiran->status = 'terlambat';
                } elseif ($kehadiran->status === 'tidak_hadir') {
                    $kehadiran->status = 'hadir';
                }
            }
        });
    }
}
