<?php

namespace Modules\Kategori\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KategoriKursus extends Model
{
    use HasFactory;

    protected $table = 'kategori_kursus';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'icon',
        'urutan',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kategori) {
            if (empty($kategori->slug)) {
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });

        static::updating(function ($kategori) {
            if ($kategori->isDirty('nama_kategori') && !$kategori->isDirty('slug')) {
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });
    }

    public function kursus()
    {
        return $this->hasMany(\Modules\Kursus\Entities\Kursus::class, 'kategori_id');
    }
}
