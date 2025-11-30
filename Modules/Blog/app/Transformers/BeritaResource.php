<?php

namespace Modules\Blog\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BeritaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kategori_berita_id' => $this->kategori_berita_id,
            'kategori' => $this->when($this->relationLoaded('kategori'), [
                'id' => $this->kategori?->id,
                'nama_kategori' => $this->kategori?->nama_kategori,
                'slug' => $this->kategori?->slug,
            ]),
            'penulis' => $this->when($this->penulis, [
                'id' => $this->penulis?->id,
                'nama_lengkap' => $this->penulis?->nama_lengkap,
            ]),
            'judul' => $this->judul,
            'slug' => $this->slug,
            'ringkasan' => $this->ringkasan,
            'konten' => $this->konten,
            'excerpt' => $this->excerpt,
            'gambar_utama' => $this->gambar_utama,
            'gambar_utama_url' => $this->gambar_utama_url,
            'sumber_gambar' => $this->sumber_gambar,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'view_count' => $this->view_count,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'reading_time' => $this->reading_time,
            
            // SEO fields (only for detail view)
            'meta_title' => $this->when($request->route()->getName() === 'api.berita.show' || $request->route()->parameter('id'), $this->meta_title),
            'meta_description' => $this->when($request->route()->getName() === 'api.berita.show' || $request->route()->parameter('id'), $this->meta_description),
            'meta_keywords' => $this->when($request->route()->getName() === 'api.berita.show' || $request->route()->parameter('id'), $this->meta_keywords),
            
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}