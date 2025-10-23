<?php

namespace Modules\Peserta\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\OPD\Transformers\OPDResource;

class PesertaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'opd_id' => $this->opd_id,
            'opd' => $this->whenLoaded('opd', function () {
                return new OPDResource($this->opd);
            }),
            'username' => $this->username,
            'email' => $this->email,
            'nama_lengkap' => $this->nama_lengkap,
            'nip' => $this->nip,
            'pangkat_golongan' => $this->pangkat_golongan,
            'jabatan' => $this->jabatan,
            'tanggal_lahir' => $this->tanggal_lahir ? $this->tanggal_lahir->format('Y-m-d') : null,
            'tempat_lahir' => $this->tempat_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'pendidikan_terakhir' => $this->pendidikan_terakhir,
            'status_kepegawaian' => $this->status_kepegawaian,
            'no_telepon' => $this->no_telepon,
            'alamat' => $this->alamat,
            'foto_profil' => $this->foto_profil ? url('storage/profile/foto/' . $this->foto_profil) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
