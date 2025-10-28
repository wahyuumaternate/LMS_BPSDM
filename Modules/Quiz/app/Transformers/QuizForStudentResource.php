<?php

namespace Modules\Quiz\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizForStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Resource ini untuk peserta yang sedang START quiz
     * Tidak menampilkan jawaban yang benar
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'pertanyaan' => $this->pertanyaan,
            'pilihan_a' => $this->pilihan_a,
            'pilihan_b' => $this->pilihan_b,
            'pilihan_c' => $this->pilihan_c,
            'pilihan_d' => $this->pilihan_d,
            'poin' => $this->poin,
            // TIDAK ADA jawaban_benar
            // TIDAK ADA pembahasan (sampai quiz selesai)
        ];
    }
}
