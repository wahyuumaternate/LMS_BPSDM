<?php

namespace Modules\Kursus\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;

class PendaftaranKursus extends Model
{
    use HasFactory;

    protected $fillable = [
        'peserta_id',
        'kursus_id',
        'tanggal_daftar',
        'status',
        'alasan_ditolak',
        'nilai_akhir',
        'predikat',
        'tanggal_disetujui',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'tanggal_disetujui' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'nilai_akhir' => 'decimal:2',
    ];

    public function kursus()
    {
        return $this->belongsTo(Kursus::class);
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeBatal($query)
    {
        return $query->where('status', 'batal');
    }
}
