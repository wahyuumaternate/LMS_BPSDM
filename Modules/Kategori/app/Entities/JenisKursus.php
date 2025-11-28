<?php

namespace Modules\Kategori\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JenisKursus extends Model
{
    protected $table = 'jenis_kursus';

    protected $fillable = [
        'kategori_kursus_id',
        'kode_jenis',
        'nama_jenis',
        'slug',
        'deskripsi',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    public function kategoriKursus(): BelongsTo
    {
        return $this->belongsTo(KategoriKursus::class, 'kategori_kursus_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_kursus_id', $kategoriId);
    }
}