<?php

namespace Modules\OPD\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OPDResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kode_opd' => $this->kode_opd,
            'nama_opd' => $this->nama_opd,
            'alamat' => $this->alamat,
            'no_telepon' => $this->no_telepon,
            'email' => $this->email,
            'nama_kepala' => $this->nama_kepala,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relasi (optional, hanya dimuat jika ada)
            'pesertas' => $this->whenLoaded('pesertas'),

            // Statistik (jika dimuat)
            'jumlah_peserta' => $this->when(
                $this->relationLoaded('pesertas'),
                $this->pesertas->count()
            ),
        ];
    }
}
