<?php

namespace Modules\Quiz\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class QuizResultResource extends JsonResource
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
            'peserta_id' => $this->peserta_id,
            'attempt' => $this->attempt,
            'nilai' => $this->nilai,
            'jumlah_benar' => $this->jumlah_benar,
            'jumlah_salah' => $this->jumlah_salah,
            'total_tidak_jawab' => $this->total_tidak_jawab,
            'is_passed' => $this->is_passed,

            // Jawaban hanya ditampilkan untuk pemilik hasil atau admin
            'jawaban' => $this->when(
                $request->user() && (
                    $request->user()->id == $this->peserta_id ||
                    $request->user()->hasRole(['admin', 'instructor'])
                ),
                $this->jawaban
            ),

            'durasi_pengerjaan_menit' => $this->durasi_pengerjaan_menit,
            'waktu_mulai' => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relasi (optional, hanya dimuat jika ada)
            'quiz' => $this->whenLoaded('quiz', function () {
                return new QuizResource($this->quiz);
            }),
            'peserta' => $this->whenLoaded('peserta'),
        ];
    }
}
