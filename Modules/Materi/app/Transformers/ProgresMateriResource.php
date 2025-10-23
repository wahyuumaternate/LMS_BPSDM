<?php

namespace Modules\Materi\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgresMateriResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'peserta_id' => $this->peserta_id,
            'peserta' => $this->whenLoaded('peserta', function () {
                return [
                    'id' => $this->peserta->id,
                    'nama_lengkap' => $this->peserta->nama_lengkap,
                    'nip' => $this->peserta->nip
                ];
            }),
            'materi_id' => $this->materi_id,
            'materi' => $this->whenLoaded('materi', function () {
                return [
                    'id' => $this->materi->id,
                    'judul_materi' => $this->materi->judul_materi,
                    'tipe_konten' => $this->materi->tipe_konten
                ];
            }),
            'is_selesai' => $this->is_selesai,
            'progress_persen' => $this->progress_persen,
            'tanggal_mulai' => $this->tanggal_mulai ? $this->tanggal_mulai->format('Y-m-d H:i:s') : null,
            'tanggal_selesai' => $this->tanggal_selesai ? $this->tanggal_selesai->format('Y-m-d H:i:s') : null,
            'durasi_belajar_menit' => $this->durasi_belajar_menit,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
