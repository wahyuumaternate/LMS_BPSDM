<?php

namespace Modules\AdminInstruktur\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminInstrukturResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'nama_lengkap' => $this->nama_lengkap,
            'nama_dengan_gelar' => $this->nama_lengkap_dengan_gelar,
            'nip' => $this->nip,
            'gelar_depan' => $this->gelar_depan,
            'gelar_belakang' => $this->gelar_belakang,
            'bidang_keahlian' => $this->bidang_keahlian,
            'no_telepon' => $this->no_telepon,
            'alamat' => $this->alamat,
            'foto_profil' => $this->foto_profil ? url('storage/profile/foto/' . $this->foto_profil) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
