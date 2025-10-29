<?php

namespace Modules\SesiKehadiran\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class KehadiranResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $statusOptions = [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'tidak_hadir' => 'Tidak Hadir'
        ];

        return [
            'id' => $this->id,
            'sesi_id' => $this->sesi_id,
            'sesi' => $this->whenLoaded('sesi', function () {
                return [
                    'id' => $this->sesi->id,
                    'pertemuan_ke' => $this->sesi->pertemuan_ke,
                    'tanggal' => $this->sesi->tanggal->format('Y-m-d'),
                    'waktu_mulai' => $this->sesi->waktu_mulai->format('H:i:s'),
                    'waktu_selesai' => $this->sesi->waktu_selesai->format('H:i:s'),
                    'kursus' => $this->sesi->whenLoaded('kursus', function () {
                        return [
                            'id' => $this->sesi->kursus->id,
                            'judul' => $this->sesi->kursus->judul
                        ];
                    })
                ];
            }),
            'peserta_id' => $this->peserta_id,
            'peserta' => $this->whenLoaded('peserta', function () {
                return [
                    'id' => $this->peserta->id,
                    'nama_lengkap' => $this->peserta->nama_lengkap,
                    'nip' => $this->peserta->nip
                ];
            }),
            'waktu_checkin' => $this->waktu_checkin,
            'waktu_checkout' => $this->waktu_checkout,
            'status' => $this->status,
            'status_text' => $statusOptions[$this->status] ?? $this->status,
            'durasi_menit' => $this->durasi_menit,
            'lokasi_checkin' => $this->lokasi_checkin,
            'lokasi_checkout' => $this->lokasi_checkout,
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
