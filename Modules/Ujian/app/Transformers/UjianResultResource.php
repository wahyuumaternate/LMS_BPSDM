<?php

namespace Modules\Ujian\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UjianResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $waktuMulai = $this->waktu_mulai ? Carbon::parse($this->waktu_mulai) : null;
        $waktuSelesai = $this->waktu_selesai ? Carbon::parse($this->waktu_selesai) : null;
        
        return [
            'id' => $this->id,
            'ujian_id' => $this->ujian_id,
            'peserta_id' => $this->peserta_id,
            'nilai' => $this->nilai ? round($this->nilai, 2) : 0,
            'is_passed' => (bool) $this->is_passed,
            'waktu_mulai' => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'tanggal_dinilai' => $this->tanggal_dinilai,
            
            // Computed fields
            'durasi_pengerjaan_menit' => $waktuMulai && $waktuSelesai 
                ? $waktuMulai->diffInMinutes($waktuSelesai) 
                : null,
            
            // Relationships
            'ujian' => $this->whenLoaded('ujian', function () {
                return [
                    'id' => $this->ujian->id,
                    'judul_ujian' => $this->ujian->judul_ujian,
                    'deskripsi' => $this->ujian->deskripsi,
                    'passing_grade' => $this->ujian->passing_grade,
                    'bobot_nilai' => $this->ujian->bobot_nilai,
                    'kursus' => $this->ujian->kursus ? [
                        'id' => $this->ujian->kursus->id,
                        'nama_kursus' => $this->ujian->kursus->nama_kursus,
                    ] : null,
                ];
            }),
            
            'peserta' => $this->whenLoaded('peserta', function () {
                return [
                    'id' => $this->peserta->id,
                    'nama_lengkap' => $this->peserta->nama_lengkap ?? null,
                    'email' => $this->peserta->email ?? null,
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
