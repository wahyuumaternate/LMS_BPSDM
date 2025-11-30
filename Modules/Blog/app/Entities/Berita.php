<?php

namespace Modules\Blog\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Berita extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'berita';

    protected $fillable = [
        'kategori_berita_id',
        'admin_instruktur_id',
        'judul',
        'slug',
        'ringkasan',
        'konten',
        'gambar_utama',
        'sumber_gambar',
        'status',
        'is_featured',
        'view_count',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'published_at' => 'datetime',
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from judul
        static::creating(function ($berita) {
            if (empty($berita->slug)) {
                $berita->slug = Str::slug($berita->judul);
            }
            
            // Auto-generate meta_title if empty
            if (empty($berita->meta_title)) {
                $berita->meta_title = $berita->judul;
            }
        });

        static::updating(function ($berita) {
            if ($berita->isDirty('judul')) {
                $berita->slug = Str::slug($berita->judul);
                
                if (empty($berita->meta_title)) {
                    $berita->meta_title = $berita->judul;
                }
            }
        });
    }

    /**
     * Relationships
     */
    
    // Belongs to kategori
    public function kategori()
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_berita_id');
    }

    // Belongs to penulis/author (admin_instruktur)
    public function penulis()
    {
        return $this->belongsTo(\Modules\AdminInstruktur\Entities\AdminInstruktur::class, 'admin_instruktur_id');
    }

    /**
     * Scopes
     */
    
    // Get published berita only
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    // Get draft berita
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Get archived berita
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    // Get featured berita
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Filter by kategori
    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_berita_id', $kategoriId);
    }

    // Search by keyword
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('judul', 'like', "%{$keyword}%")
              ->orWhere('ringkasan', 'like', "%{$keyword}%")
              ->orWhere('konten', 'like', "%{$keyword}%");
        });
    }

    // Latest published
    public function scopeLatest($query, $limit = 10)
    {
        return $query->published()
                    ->orderBy('published_at', 'desc')
                    ->limit($limit);
    }

    // Popular (most viewed)
    public function scopePopular($query, $limit = 10)
    {
        return $query->published()
                    ->orderBy('view_count', 'desc')
                    ->limit($limit);
    }

    /**
     * Accessors & Mutators
     */
    
    // Get gambar URL
    public function getGambarUtamaUrlAttribute()
    {
        if ($this->gambar_utama) {
            return asset('storage/berita/' . $this->gambar_utama);
        }
        return asset('assets/img/default-news.jpg');
    }

    // Get excerpt (ringkasan or from konten)
    public function getExcerptAttribute()
    {
        if ($this->ringkasan) {
            return $this->ringkasan;
        }
        return Str::limit(strip_tags($this->konten), 200);
    }

    // Get formatted published date
    public function getFormattedPublishedAtAttribute()
    {
        if (!$this->published_at) {
            return '-';
        }
        return $this->published_at->format('d M Y, H:i');
    }

    // Get formatted created date
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d M Y, H:i');
    }

    // Get human readable published date
    public function getPublishedAtHumanAttribute()
    {
        if (!$this->published_at) {
            return '-';
        }
        return $this->published_at->diffForHumans();
    }

    // Get reading time
    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->konten));
        $minutes = ceil($wordCount / 200); // Average: 200 words/minute
        return $minutes . ' menit baca';
    }

    // Get status badge HTML
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'published' => '<span class="badge bg-success">Published</span>',
            'archived' => '<span class="badge bg-warning">Archived</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Get featured badge
    public function getFeaturedBadgeAttribute()
    {
        if ($this->is_featured) {
            return '<span class="badge bg-primary"><i class="bi bi-star-fill"></i> Featured</span>';
        }
        return '';
    }

    /**
     * Helper Methods
     */
    
    // Increment view count
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    // Check if published
    public function isPublished()
    {
        return $this->status === 'published' 
            && $this->published_at 
            && $this->published_at <= now();
    }

    // Check if draft
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    // Publish berita
    public function publish()
    {
        $this->status = 'published';
        if (!$this->published_at) {
            $this->published_at = now();
        }
        $this->save();
        return $this;
    }

    // Archive berita
    public function archive()
    {
        $this->status = 'archived';
        $this->save();
        return $this;
    }

    // Set as featured
    public function setFeatured($featured = true)
    {
        $this->is_featured = $featured;
        $this->save();
        return $this;
    }

    // Toggle featured
    public function toggleFeatured()
    {
        $this->is_featured = !$this->is_featured;
        $this->save();
        return $this;
    }

    // Get next berita
    public function getNext()
    {
        return static::published()
            ->where('published_at', '>', $this->published_at)
            ->orderBy('published_at', 'asc')
            ->first();
    }

    // Get previous berita
    public function getPrevious()
    {
        return static::published()
            ->where('published_at', '<', $this->published_at)
            ->orderBy('published_at', 'desc')
            ->first();
    }

    // Get related berita (same kategori)
    public function getRelated($limit = 3)
    {
        return static::published()
            ->where('kategori_berita_id', $this->kategori_berita_id)
            ->where('id', '!=', $this->id)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}