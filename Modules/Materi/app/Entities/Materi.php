<?php

namespace Modules\Materi\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Modul\Entities\Modul;

class Materi extends Model
{
    use HasFactory;

    protected $fillable = [
        'modul_id',
        'judul_materi',
        'urutan',
        'tipe_konten',
        'file_path',
        'deskripsi',
        'durasi_menit',
        'ukuran_file',
        'is_wajib',
        'published_at',
    ];

    protected $casts = [
        'is_wajib' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function modul()
    {
        return $this->belongsTo(Modul::class);
    }

    public function progresMateri()
    {
        return $this->hasMany(ProgresMateri::class);
    }

    // public function ratings()
    // {
    //     return $this->hasMany(\Modules\Kursus\Entities\RatingUlasan::class);
    // }

    public function getIsPublishedAttribute()
    {
        return !is_null($this->published_at);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function getIsVideoAttribute()
    {
        return $this->tipe_konten === 'video';
    }

    public function getIsPdfAttribute()
    {
        return $this->tipe_konten === 'pdf';
    }
}
