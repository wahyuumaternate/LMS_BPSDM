<?php

namespace Modules\Forum\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $platformOptions = [
            'telegram' => 'Telegram',
            'whatsapp' => 'WhatsApp',
            'other' => 'Lainnya'
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
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'platform' => $this->platform,
            'platform_text' => $platformOptions[$this->platform] ?? $this->platform,
            'link_grup' => $this->link_grup,
            'is_aktif' => (bool) $this->is_aktif,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
