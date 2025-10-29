<?php

namespace Modules\SesiKehadiran\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class SesiKehadiranResource extends JsonResource
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
            'scheduled' => 'Terjadwal',
            'ongoing' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        return [
            'id' => $this->id,
            'kursus_id' => $this->kursus_id,
            'kursus' => $this->whenLoaded('kursus', function () {
                return [
                    'id' => $this->kursus->id,
                    'judul' => $this->kursus->judul
                ];
            }),
            'pertemuan_ke' => $this->pertemuan_ke,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'waktu_mulai' => $this->waktu_mulai->format('H:i:s'),
            'waktu_selesai' => $this->waktu_selesai->format('H:i:s'),
            'qr_code_checkin_url' => $this->when($this->qr_code_checkin, function () {
                return url('storage/qrcodes/' . $this->qr_code_checkin);
            }),
            'qr_code_checkout_url' => $this->when($this->qr_code_checkout, function () {
                return url('storage/qrcodes/' . $this->qr_code_checkout);
            }),
            'durasi_berlaku_menit' => $this->durasi_berlaku_menit,
            'status' => $this->status,
            'status_text' => $statusOptions[$this->status] ?? $this->status,
            'kehadiran' => $this->whenLoaded('kehadiran', function () {
                return KehadiranResource::collection($this->kehadiran);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
