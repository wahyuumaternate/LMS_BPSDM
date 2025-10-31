<?php

namespace Modules\Quiz\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizForStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Resource ini untuk peserta yang sedang START quiz
     * Tidak menampilkan jawaban yang benar dan pembahasan
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Mendapatkan data dari relasi options jika dimuat
        $pilihan_a = $pilihan_b = $pilihan_c = $pilihan_d = null;

        if ($this->relationLoaded('options') && $this->options->count() > 0) {
            // Sortir opsi berdasarkan urutan
            $sortedOptions = $this->options->sortBy('urutan');

            // Dapatkan 4 opsi pertama untuk A, B, C, D
            $options_array = $sortedOptions->values()->all();

            if (isset($options_array[0])) {
                $pilihan_a = $options_array[0]->teks_opsi;
            }

            if (isset($options_array[1])) {
                $pilihan_b = $options_array[1]->teks_opsi;
            }

            if (isset($options_array[2])) {
                $pilihan_c = $options_array[2]->teks_opsi;
            }

            if (isset($options_array[3])) {
                $pilihan_d = $options_array[3]->teks_opsi;
            }
        } else {
            // Fallback ke data langsung dari model jika tersedia
            // (untuk backward compatibility dengan model non-relasional)
            $pilihan_a = $this->pilihan_a ?? null;
            $pilihan_b = $this->pilihan_b ?? null;
            $pilihan_c = $this->pilihan_c ?? null;
            $pilihan_d = $this->pilihan_d ?? null;
        }

        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'pertanyaan' => $this->pertanyaan,
            'pilihan_a' => $pilihan_a,
            'pilihan_b' => $pilihan_b,
            'pilihan_c' => $pilihan_c,
            'pilihan_d' => $pilihan_d,
            'poin' => $this->poin,

            // Struktur options untuk format yang lebih fleksibel (opsional)
            'options' => $this->when($this->relationLoaded('options'), function () {
                return $this->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'teks_opsi' => $option->teks_opsi,
                        'urutan' => $option->urutan,
                        // is_jawaban_benar TIDAK disertakan
                    ];
                })->sortBy('urutan')->values();
            }),

            // TIDAK menampilkan:
            // - jawaban_benar
            // - pembahasan
            // - is_jawaban_benar di masing-masing option
        ];
    }
}
