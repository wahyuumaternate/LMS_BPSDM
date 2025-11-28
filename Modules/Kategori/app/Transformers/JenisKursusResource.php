<?php

namespace Modules\Kategori\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class JenisKursusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kategori_kursus_id' => $this->kategori_kursus_id,
            'kategori_kursus' => $this->whenLoaded('kategoriKursus', function () {
                return [
                    'id' => $this->kategoriKursus->id,
                    'nama_kategori' => $this->kategoriKursus->nama_kategori,
                    'slug' => $this->kategoriKursus->slug,
                ];
            }),
            'kode_jenis' => $this->kode_jenis,
            'nama_jenis' => $this->nama_jenis,
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi,
            'is_active' => $this->is_active,
            'urutan' => $this->urutan,
            'kursus_count' => $this->when(isset($this->kursus_count), $this->kursus_count),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}