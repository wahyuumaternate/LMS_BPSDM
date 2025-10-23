<?php

namespace Modules\OPD\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OPDResource extends JsonResource
{
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
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
