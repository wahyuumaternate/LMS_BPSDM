<?php

namespace Modules\Ujian\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UjianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kursus_id' => $this->kursus_id,
            'judul_ujian' => $this->judul_ujian,
            'deskripsi' => $this->deskripsi,
            'aturan_ujian' => $this->aturan_ujian,
            'waktu_mulai' => $this->waktu_mulai ? Carbon::parse($this->waktu_mulai)->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s') : null,
            'waktu_selesai' => $this->waktu_selesai ? Carbon::parse($this->waktu_selesai)->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s') : null,
            'durasi_menit' => $this->durasi_menit,
            'bobot_nilai' => $this->bobot_nilai,
            'passing_grade' => $this->passing_grade,
            'jumlah_soal' => $this->jumlah_soal,
            'random_soal' => (bool) $this->random_soal,
            'tampilkan_hasil' => (bool) $this->tampilkan_hasil,

            // Additional computed fields
            'status' => $this->when(isset($this->status), $this->status),
            'is_taken' => $this->when(isset($this->is_taken), $this->is_taken),
            'is_completed' => $this->when(isset($this->is_completed), $this->is_completed),

            // Relationships
            'kursus' => $this->whenLoaded('kursus', function () {
                return [
                    'id' => $this->kursus->id,
                    'nama_kursus' => $this->kursus->nama_kursus,
                    'kode_kursus' => $this->kursus->kode_kursus ?? null,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s') : null,
        ];
    }
}
