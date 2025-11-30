<?php

namespace Modules\Blog\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class KategoriBerita extends Model
{
    use HasFactory;

    protected $table = 'kategori_berita';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'icon',
        'color',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from nama_kategori
        static::creating(function ($kategori) {
            if (empty($kategori->slug)) {
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });

        static::updating(function ($kategori) {
            if ($kategori->isDirty('nama_kategori') && empty($kategori->slug)) {
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });
    }

    /**
     * Relationships
     */
    
    // One kategori has many berita
    public function berita()
    {
        return $this->hasMany(Berita::class, 'kategori_berita_id');
    }

    // Get published berita only
    public function beritaPublished()
    {
        return $this->hasMany(Berita::class, 'kategori_berita_id')
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scopes
     */
    
    // Get only active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Order by urutan
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('nama_kategori', 'asc');
    }

    /**
     * Accessors & Mutators
     */
    
    // Get berita count
    public function getBeritaCountAttribute()
    {
        return $this->berita()->count();
    }

    // Get published berita count
    public function getBeritaPublishedCountAttribute()
    {
        return $this->beritaPublished()->count();
    }

    // Get badge color class
    public function getBadgeColorClassAttribute()
    {
        $colors = [
            'primary' => 'bg-primary',
            'secondary' => 'bg-secondary',
            'success' => 'bg-success',
            'danger' => 'bg-danger',
            'warning' => 'bg-warning',
            'info' => 'bg-info',
            'dark' => 'bg-dark',
        ];

        return $colors[$this->color] ?? 'bg-primary';
    }

    // Get status badge
    public function getStatusBadgeAttribute()
    {
        if ($this->is_active) {
            return '<span class="badge bg-success">Aktif</span>';
        }
        return '<span class="badge bg-secondary">Nonaktif</span>';
    }

    /**
     * Helper Methods
     */
    
    // Toggle active status
    public function toggleActive()
    {
        $this->is_active = !$this->is_active;
        $this->save();
        return $this;
    }
}