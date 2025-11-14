<?php

namespace Modules\Modul\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Kursus\Entities\Kursus;
use Modules\Materi\Entities\Materi;
use Modules\Quiz\Entities\Quiz;
use Modules\Tugas\Entities\Tugas;

class Modul extends Model
{
    use HasFactory;

    protected $fillable = [
        'kursus_id',
        'nama_modul',
        'urutan',
        'deskripsi',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function kursus()
    {
        return $this->belongsTo(Kursus::class);
    }

    public function materis()
    {
        return $this->hasMany(Materi::class)->orderBy('urutan');
    }

    // public function quizzes()
    // {
    //     return $this->hasMany(\Modules\Quiz\Entities\Quiz::class);
    // }
    // Relasi ke Quiz
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'modul_id');
    }

    public function getTotalDurasiAttribute()
    {
        return $this->materis->sum('durasi_menit');
    }

    public function getJumlahMateriAttribute()
    {
        return $this->materis->count();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'modul_id');
    }
}
