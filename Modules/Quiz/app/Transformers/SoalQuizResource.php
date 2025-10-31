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
        // Mendapatkan data dari relasi options jika dimuat
        $pilihan_a = $pilihan_b = $pilihan_c = $pilihan_d = null;
        $jawaban_benar = null;

        if ($this->relationLoaded('options') && $this->options->count() > 0) {
            // Sortir opsi berdasarkan urutan
            $sortedOptions = $this->options->sortBy('urutan');

            // Dapatkan 4 opsi pertama untuk A, B, C, D
            $options_array = $sortedOptions->values()->all();

            if (isset($options_array[0])) {
                $pilihan_a = $options_array[0]->teks_opsi;
                if ($options_array[0]->is_jawaban_benar) {
                    $jawaban_benar = 'a';
                }
            }

            if (isset($options_array[1])) {
                $pilihan_b = $options_array[1]->teks_opsi;
                if ($options_array[1]->is_jawaban_benar) {
                    $jawaban_benar = 'b';
                }
            }

            if (isset($options_array[2])) {
                $pilihan_c = $options_array[2]->teks_opsi;
                if ($options_array[2]->is_jawaban_benar) {
                    $jawaban_benar = 'c';
                }
            }

            if (isset($options_array[3])) {
                $pilihan_d = $options_array[3]->teks_opsi;
                if ($options_array[3]->is_jawaban_benar) {
                    $jawaban_benar = 'd';
                }
            }
        } else {
            // Fallback ke data langsung dari model jika tersedia
            // (untuk backward compatibility dengan model non-relasional)
            $pilihan_a = $this->pilihan_a ?? null;
            $pilihan_b = $this->pilihan_b ?? null;
            $pilihan_c = $this->pilihan_c ?? null;
            $pilihan_d = $this->pilihan_d ?? null;
            $jawaban_benar = $this->jawaban_benar ?? null;
        }

        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'pertanyaan' => $this->pertanyaan,
            'pilihan_a' => $pilihan_a,
            'pilihan_b' => $pilihan_b,
            'pilihan_c' => $pilihan_c,
            'pilihan_d' => $pilihan_d,

            // PENTING: jawaban_benar hanya ditampilkan untuk admin/instructor
            // Untuk peserta yang sedang mengerjakan quiz, ini harus di-hide
            'jawaban_benar' => $this->when(
                $request->user() && $request->user()->hasRole(['admin', 'instructor']),
                $jawaban_benar
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
