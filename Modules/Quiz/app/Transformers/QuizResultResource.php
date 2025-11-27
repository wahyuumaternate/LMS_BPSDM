<?php

namespace Modules\Quiz\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class QuizResultResource extends JsonResource
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
            
            // ⬇️ Waktu mulai & selesai quiz dalam format WIT
            'waktu_mulai' => $this->toWIT($this->waktu_mulai),
            'waktu_selesai' => $this->toWIT($this->waktu_selesai),
            
            // ⬇️ created_at & updated_at dalam format WIT
            'created_at' => $this->toWIT($this->created_at),
            'updated_at' => $this->toWIT($this->updated_at),

            // Relasi (optional, hanya dimuat jika ada)
            'quiz' => $this->whenLoaded('quiz', function () {
                return new QuizResource($this->quiz);
            }),
            'peserta' => $this->whenLoaded('peserta'),
        ];
    }
}