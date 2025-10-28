<?php

namespace Modules\Quiz\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizOptionResource extends JsonResource
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
            'question_id' => $this->question_id,
            'teks_opsi' => $this->teks_opsi,

            // PENTING: is_jawaban_benar hanya ditampilkan untuk admin/instructor
            'is_jawaban_benar' => $this->when(
                $request->user() && $request->user()->hasRole(['admin', 'instructor']),
                $this->is_jawaban_benar
            ),

            'urutan' => $this->urutan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relasi (optional, hanya dimuat jika ada)
            'question' => $this->whenLoaded('question'),
        ];
    }
}
