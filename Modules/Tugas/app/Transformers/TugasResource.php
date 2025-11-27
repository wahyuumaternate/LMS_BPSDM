<?php

namespace Modules\Tugas\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TugasResource extends JsonResource
{
    /**
     * Convert date to WIT timezone (Asia/Jayapura)
     * 
     * @param mixed $date
     * @return string|null
     */
    private function toWIT($date)
    {
        return $date
            ? Carbon::parse($date)->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s')
            : null;
    }

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
            
            // ⬇️ Tanggal dalam format WIT (Asia/Jayapura)
            'tanggal_mulai' => $this->toWIT($this->tanggal_mulai),
            'tanggal_deadline' => $this->toWIT($this->tanggal_deadline),
            
            'nilai_maksimal' => $this->nilai_maksimal,
            'bobot_nilai' => $this->bobot_nilai,
            'is_published' => $this->is_published,
            
            // ⬇️ published_at dalam format WIT
            'published_at' => $this->toWIT($this->published_at),
            
            'is_overdue' => $this->isOverdue(),
            
            // ⬇️ created_at & updated_at dalam format WIT
            'created_at' => $this->toWIT($this->created_at),
            'updated_at' => $this->toWIT($this->updated_at),

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