<?php

namespace Modules\JadwalKegiatan\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Kursus\Entities\Kursus;

class JadwalKegiatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jadwal_kegiatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kursus_id',
        'nama_kegiatan',
        'waktu_mulai_kegiatan',
        'waktu_selesai_kegiatan',
        'lokasi',
        'tipe',
        'link_meeting',
        'keterangan'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'waktu_mulai_kegiatan' => 'datetime',
        'waktu_selesai_kegiatan' => 'datetime'
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
     * Mendapatkan opsi tipe kegiatan.
     *
     * @return array
     */
    public static function getTipeOptions()
    {
        return [
            'online' => 'Online',
            'offline' => 'Offline',
            'hybrid' => 'Hybrid'
        ];
    }

    /**
     * Mendapatkan durasi kegiatan dalam menit.
     *
     * @return int
     */
    public function getDurasiMenitAttribute()
    {
        if ($this->waktu_mulai_kegiatan && $this->waktu_selesai_kegiatan) {
            return $this->waktu_selesai_kegiatan->diffInMinutes($this->waktu_mulai_kegiatan);
        }

        return 0;
    }

    /**
     * Scope query untuk mendapatkan jadwal yang belum dimulai.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('waktu_mulai_kegiatan', '>', now());
    }

    /**
     * Scope query untuk mendapatkan jadwal yang sedang berlangsung.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOngoing($query)
    {
        return $query->where('waktu_mulai_kegiatan', '<=', now())
            ->where('waktu_selesai_kegiatan', '>=', now());
    }

    /**
     * Scope query untuk mendapatkan jadwal yang sudah selesai.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePast($query)
    {
        return $query->where('waktu_selesai_kegiatan', '<', now());
    }
}
