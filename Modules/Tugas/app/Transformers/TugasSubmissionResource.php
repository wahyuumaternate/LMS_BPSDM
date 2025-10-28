<?php

namespace Modules\Tugas\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TugasSubmissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tugas_id' => $this->tugas_id,
            'peserta_id' => $this->peserta_id,
            'admin_instruktur_id' => $this->admin_instruktur_id,
            'catatan_peserta' => $this->catatan_peserta,
            'file_jawaban' => $this->file_jawaban,
            'file_jawaban_url' => $this->getFileUrl(),
            'catatan_penilai' => $this->catatan_penilai,
            'nilai' => $this->nilai,
            'tanggal_submit' => $this->tanggal_submit,
            'tanggal_dinilai' => $this->tanggal_dinilai,
            'status' => $this->status,
            'is_late' => $this->isLate(),
            'is_graded' => $this->isGraded(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relasi
            'tugas' => $this->whenLoaded('tugas'),
            'peserta' => $this->whenLoaded('peserta'),
            'penilai' => $this->whenLoaded('penilai'),
        ];
    }
}
