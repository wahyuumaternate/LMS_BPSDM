<?php

namespace Modules\Kursus\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Peserta\Transformers\PesertaResource;

class PendaftaranKursusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'peserta_id' => $this->peserta_id,
            'peserta' => $this->whenLoaded('peserta', function () {
                return new PesertaResource($this->peserta);
            }),
            'kursus_id' => $this->kursus_id,
            'kursus' => $this->whenLoaded('kursus', function () {
                return [
                    'id' => $this->kursus->id,
                    'judul' => $this->kursus->judul,
                    'kode_kursus' => $this->kursus->kode_kursus
                ];
            }),
            'tanggal_daftar' => $this->tanggal_daftar ? $this->tanggal_daftar->format('Y-m-d') : null,
            'status' => $this->status,
            'alasan_ditolak' => $this->alasan_ditolak,
            'nilai_akhir' => $this->nilai_akhir,
            'predikat' => $this->predikat,
            'tanggal_disetujui' => $this->tanggal_disetujui ? $this->tanggal_disetujui->format('Y-m-d H:i:s') : null,
            'tanggal_selesai' => $this->tanggal_selesai ? $this->tanggal_selesai->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
