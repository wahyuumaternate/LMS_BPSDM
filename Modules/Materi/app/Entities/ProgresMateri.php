<?php

namespace Modules\Materi\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;

class ProgresMateri extends Model
{
    use HasFactory;

    protected $fillable = [
        'peserta_id',
        'materi_id',
        'is_selesai',
        'progress_persen',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi_belajar_menit',
    ];

    protected $casts = [
        'is_selesai' => 'boolean',
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    public function scopeSelesai($query)
    {
        return $query->where('is_selesai', true);
    }

    public function scopeBelumSelesai($query)
    {
        return $query->where('is_selesai', false);
    }
}
