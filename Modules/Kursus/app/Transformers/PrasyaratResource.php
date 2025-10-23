<?php

namespace Modules\Kursus\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PrasyaratResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kursus_id' => $this->kursus_id,
            'kursus' => $this->whenLoaded('kursus', function () {
                return [
                    'id' => $this->kursus->id,
                    'judul' => $this->kursus->judul,
                    'kode_kursus' => $this->kursus->kode_kursus
                ];
            }),
            'kursus_prasyarat_id' => $this->kursus_prasyarat_id,
            'kursus_prasyarat' => $this->whenLoaded('kursusPrasyarat', function () {
                return [
                    'id' => $this->kursusPrasyarat->id,
                    'judul' => $this->kursusPrasyarat->judul,
                    'kode_kursus' => $this->kursusPrasyarat->kode_kursus
                ];
            }),
            'deskripsi' => $this->deskripsi,
            'is_wajib' => $this->is_wajib,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
