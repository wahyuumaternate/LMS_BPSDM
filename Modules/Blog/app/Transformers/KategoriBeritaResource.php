<?php

namespace Modules\Blog\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class KategoriBeritaResource extends JsonResource
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
            'nama_kategori' => $this->nama_kategori,
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi,
            'icon' => $this->icon,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'urutan' => $this->urutan,
            
            // Counts (when loaded)
            'berita_count' => $this->when(isset($this->berita_count), $this->berita_count),
            'berita_published_count' => $this->when(isset($this->berita_published_count), $this->berita_published_count),
            
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}