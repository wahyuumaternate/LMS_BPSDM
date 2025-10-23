<?php

namespace Modules\Tugas\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Modul\Entities\Modul;

class Tugas extends Model
{
    use HasFactory;

    protected $fillable = [
        'modul_id',
        'judul',
        'deskripsi',
        'petunjuk',
        'file_tugas',
        'tanggal_mulai',
        'tanggal_deadline',
        'nilai_maksimal',
        'bobot_nilai',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_deadline' => 'date',
        'published_at' => 'datetime',
    ];

    public function modul()
    {
        return $this->belongsTo(Modul::class);
    }

    public function submissions()
    {
        return $this->hasMany(TugasSubmission::class);
    }

    public function isActive()
    {
        $today = now()->format('Y-m-d');
        return $this->is_published &&
            $this->tanggal_mulai <= $today &&
            $this->tanggal_deadline >= $today;
    }

    public function isExpired()
    {
        return $this->tanggal_deadline && now()->format('Y-m-d') > $this->tanggal_deadline->format('Y-m-d');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
