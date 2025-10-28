<?php

namespace Modules\Tugas\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Modul\Entities\Modul;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

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
        'tanggal_mulai' => 'date',
        'tanggal_deadline' => 'date',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Relasi ke Modul
    public function modul()
    {
        return $this->belongsTo(\Modules\Modul\Entities\Modul::class, 'modul_id');
    }

    // Relasi ke Submissions
    public function submissions()
    {
        return $this->hasMany(TugasSubmission::class, 'tugas_id');
    }

    // Check if deadline has passed
    public function isOverdue()
    {
        return $this->tanggal_deadline && now()->gt($this->tanggal_deadline);
    }

    // Get file URL
    public function getFileUrl()
    {
        return $this->file_tugas ? asset('storage/' . $this->file_tugas) : null;
    }
}
