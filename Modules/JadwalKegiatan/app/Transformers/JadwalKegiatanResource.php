<?php

namespace Modules\JadwalKegiatan\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
         $mulai = Carbon::parse($this->waktu_mulai_kegiatan)
            ->setTimezone('Asia/Jayapura')
            ->format('Y-m-d H:i:s');

        $selesai = Carbon::parse($this->waktu_selesai_kegiatan)
            ->setTimezone('Asia/Jayapura')
            ->format('Y-m-d H:i:s');


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
            'waktu_mulai_kegiatan' => $mulai,
            'waktu_selesai_kegiatan' => $selesai,
            'lokasi' => $this->lokasi,
            'tipe' => $this->tipe,
            'tipe_text' => $tipeOptions[$this->tipe] ?? $this->tipe,
            'link_meeting' => $this->link_meeting,
            'keterangan' => $this->keterangan,
            'durasi_menit' => $this->durasi_menit,
            'created_at' => $this->created_at
            ? $this->created_at->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s')
            : null,

        'updated_at' => $this->updated_at
            ? $this->updated_at->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s')
            : null,

        ];
    }
}
