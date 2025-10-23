<?php

namespace Modules\Kategori\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class KategoriKursusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nama_kategori' => $this->nama_kategori,
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi,
            'icon' => $this->icon,
            'urutan' => $this->urutan,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'jumlah_kursus' => $this->whenCounted('kursus'),
            'kursus' => $this->when($request->has('include_kursus'), function () {
                return $this->kursus;
            }),
        ];
    }
}
