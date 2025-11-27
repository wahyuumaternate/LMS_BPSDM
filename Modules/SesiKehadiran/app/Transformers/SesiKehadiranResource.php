<?php

namespace Modules\SesiKehadiran\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class SesiKehadiranResource extends JsonResource
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
            
            // ⬇️ Tanggal & waktu sesi dalam format WIT
            'tanggal' => $this->toWIT($this->tanggal),
            'waktu_mulai' => $this->toWIT($this->waktu_mulai),
            'waktu_selesai' => $this->toWIT($this->waktu_selesai),
            
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
            
            // ⬇️ created_at & updated_at dalam format WIT
            'created_at' => $this->toWIT($this->created_at),
            'updated_at' => $this->toWIT($this->updated_at)
        ];
    }
}