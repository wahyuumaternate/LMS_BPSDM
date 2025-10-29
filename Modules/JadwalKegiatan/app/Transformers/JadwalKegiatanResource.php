<?php

namespace Modules\JadwalKegiatan\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class JadwalKegiatanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $tipeOptions = [
            'online' => 'Online',
            'offline' => 'Offline',
            'hybrid' => 'Hybrid'
        ];

        return [
            'id' => $this->id,
            'kursus_id' => $this->kursus_id,
            'kursus' => $this->whenLoaded('kursus', function () {
                return [
                    'id' => $this->kursus->id,
                    'judul' => $this->kursus->judul
                ];
            }),
            'nama_kegiatan' => $this->nama_kegiatan,
            'waktu_mulai_kegiatan' => $this->waktu_mulai_kegiatan,
            'waktu_selesai_kegiatan' => $this->waktu_selesai_kegiatan,
            'lokasi' => $this->lokasi,
            'tipe' => $this->tipe,
            'tipe_text' => $tipeOptions[$this->tipe] ?? $this->tipe,
            'link_meeting' => $this->link_meeting,
            'keterangan' => $this->keterangan,
            'durasi_menit' => $this->durasi_menit,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
