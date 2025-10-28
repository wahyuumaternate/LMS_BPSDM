<?php

namespace Modules\Tugas\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TugasResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'modul_id' => $this->modul_id,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'petunjuk' => $this->petunjuk,
            'file_tugas' => $this->file_tugas,
            'file_tugas_url' => $this->getFileUrl(),
            'tanggal_mulai' => $this->tanggal_mulai?->format('Y-m-d'),
            'tanggal_deadline' => $this->tanggal_deadline?->format('Y-m-d'),
            'nilai_maksimal' => $this->nilai_maksimal,
            'bobot_nilai' => $this->bobot_nilai,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'is_overdue' => $this->isOverdue(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relasi
            'modul' => $this->whenLoaded('modul'),
            'submissions' => $this->whenLoaded('submissions', function () {
                return TugasSubmissionResource::collection($this->submissions);
            }),
            'total_submissions' => $this->when(
                $this->relationLoaded('submissions'),
                $this->submissions->count()
            ),
        ];
    }
}
