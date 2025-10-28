<?php

namespace Modules\Quiz\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoalQuizResource extends JsonResource
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
            'quiz_id' => $this->quiz_id,
            'pertanyaan' => $this->pertanyaan,
            'pilihan_a' => $this->pilihan_a,
            'pilihan_b' => $this->pilihan_b,
            'pilihan_c' => $this->pilihan_c,
            'pilihan_d' => $this->pilihan_d,

            // PENTING: jawaban_benar hanya ditampilkan untuk admin/instructor
            // Untuk peserta yang sedang mengerjakan quiz, ini harus di-hide
            'jawaban_benar' => $this->when(
                $request->user() && $request->user()->hasRole(['admin', 'instructor']),
                $this->jawaban_benar
            ),

            'poin' => $this->poin,
            'pembahasan' => $this->pembahasan,
            'tingkat_kesulitan' => $this->tingkat_kesulitan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relasi (optional, hanya dimuat jika ada)
            'quiz' => $this->whenLoaded('quiz'),
            'options' => $this->whenLoaded('options', function () {
                return QuizOptionResource::collection($this->options);
            }),
        ];
    }
}
