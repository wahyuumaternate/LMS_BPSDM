<?php

namespace Modules\SesiKehadiran\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Peserta\Entities\Peserta;

class Kehadiran extends Model
{
    use HasFactory;

    /**
     * Nama tabel terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'kehadiran';

    /**
     * Atribut yang dapat diisi.
     *
     * @var array
     */
    protected $fillable = [
        'sesi_id',
        'peserta_id',
        'waktu_checkin',
        'waktu_checkout',
        'status',
        'durasi_menit',
        'lokasi_checkin',
        'lokasi_checkout',
        'keterangan'
    ];

    /**
     * Atribut yang harus dikonversi.
     *
     * @var array
     */
    protected $casts = [
        'waktu_checkin' => 'datetime',
        'waktu_checkout' => 'datetime'
    ];

    /**
     * Relasi dengan model SesiKehadiran.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sesi()
    {
        return $this->belongsTo(SesiKehadiran::class, 'sesi_id');
    }

    /**
     * Relasi dengan model Peserta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    /**
     * Mendapatkan daftar opsi status yang tersedia.
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'tidak_hadir' => 'Tidak Hadir'
        ];
    }

    /**
     * Menghitung dan memperbarui durasi kehadiran.
     *
     * @return int|null
     */
    public function hitungDanUpdateDurasi()
    {
        if ($this->waktu_checkin && $this->waktu_checkout) {
            $this->durasi_menit = $this->waktu_checkout->diffInMinutes($this->waktu_checkin);
            $this->save();
            return $this->durasi_menit;
        }

        return null;
    }

    /**
     * Memeriksa apakah peserta sudah check-in.
     *
     * @return bool
     */
    public function isCheckedIn()
    {
        return $this->waktu_checkin !== null;
    }

    /**
     * Memeriksa apakah peserta sudah check-out.
     *
     * @return bool
     */
    public function isCheckedOut()
    {
        return $this->waktu_checkout !== null;
    }

    /**
     * Memeriksa apakah peserta terlambat.
     *
     * @return bool
     */
    public function isTerlambat()
    {
        if (!$this->waktu_checkin || !$this->sesi) {
            return false;
        }

        $tanggal = $this->sesi->tanggal->format('Y-m-d');
        $waktuMulai = $tanggal . ' ' . $this->sesi->waktu_mulai->format('H:i:s');
        $startTime = \Carbon\Carbon::parse($waktuMulai);

        // Peserta dianggap terlambat jika check-in > 15 menit setelah waktu mulai
        $toleranceTime = $startTime->copy()->addMinutes(15);

        return $this->waktu_checkin->gt($toleranceTime);
    }

    /**
     * Scope query untuk mendapatkan kehadiran yang tercatat.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHadir($query)
    {
        return $query->whereIn('status', ['hadir', 'terlambat']);
    }

    /**
     * Scope query untuk mendapatkan ketidakhadiran.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTidakHadir($query)
    {
        return $query->whereIn('status', ['izin', 'sakit', 'tidak_hadir']);
    }

    /**
     * Boot function untuk model.
     * 
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Otomatis menentukan status berdasarkan waktu check-in jika status belum ditentukan
        static::creating(function ($kehadiran) {
            if (empty($kehadiran->status) && $kehadiran->waktu_checkin) {
                $sesi = SesiKehadiran::find($kehadiran->sesi_id);
                if ($sesi) {
                    $tanggal = $sesi->tanggal->format('Y-m-d');
                    $waktuMulai = $tanggal . ' ' . $sesi->waktu_mulai->format('H:i:s');
                    $startTime = \Carbon\Carbon::parse($waktuMulai);

                    // Toleransi 15 menit untuk keterlambatan
                    $toleranceTime = $startTime->copy()->addMinutes(15);

                    $kehadiran->status = $kehadiran->waktu_checkin->gt($toleranceTime)
                        ? 'terlambat'
                        : 'hadir';
                }
            }
        });

        // Hitung durasi kehadiran saat check-out
        static::saving(function ($kehadiran) {
            if ($kehadiran->waktu_checkin && $kehadiran->waktu_checkout && !$kehadiran->durasi_menit) {
                $kehadiran->durasi_menit = $kehadiran->waktu_checkout->diffInMinutes($kehadiran->waktu_checkin);
            }
        });
    }
}
