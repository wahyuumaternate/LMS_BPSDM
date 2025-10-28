<?php

namespace Modules\Quiz\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'modul_id' => $this->modul_id,
            'judul_quiz' => $this->judul_quiz,
            'deskripsi' => $this->deskripsi,
            'durasi_menit' => $this->durasi_menit,
            'bobot_nilai' => $this->bobot_nilai,
            'passing_grade' => $this->passing_grade,
            'jumlah_soal' => $this->jumlah_soal,
            'random_soal' => $this->random_soal,
            'tampilkan_hasil' => $this->tampilkan_hasil,
            'max_attempt' => $this->max_attempt,
            'is_published' => $this->when($this->is_published !== null, $this->is_published),
            'published_at' => $this->when($this->published_at !== null, $this->published_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relasi (optional, hanya dimuat jika ada)
            'modul' => $this->whenLoaded('modul'),
            'soal_quiz' => $this->whenLoaded('soalQuiz', function () {
                return SoalQuizResource::collection($this->soalQuiz);
            }),
        ];
    }
}
