<?php
// Modules/Master/Entities/KategoriKursus.php
namespace Modules\Master\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Str;

class KategoriKursus extends Model
{
    protected $table = 'kategori_kursus';
    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'icon',
        'urutan'
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($kategori) {
            $kategori->slug = $kategori->slug ?: Str::slug($kategori->nama_kategori);
        });
        static::updating(function ($kategori) {
            if ($kategori->isDirty('nama_kategori') && !$kategori->isDirty('slug')) {
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });
    }
    public function kursus()
    {
        return $this->hasMany(Kursus::class, 'kategori_id');
    }
}
