<?php

namespace Modules\Modul\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Materi\Transformers\MateriResource;
use Illuminate\Support\Facades\Auth;

class ModulResource extends JsonResource
{
    public function toArray($request)
    {
        // Check if user is authenticated
        $isAuthenticated = Auth::guard('sanctum')->check();

        return [
            'id' => $this->id,
            'kursus_id' => $this->kursus_id,
            'kursus' => $this->whenLoaded('kursus', function () {
                return [
                    'id' => $this->kursus->id,
                    'judul' => $this->kursus->judul,
                    'kode_kursus' => $this->kursus->kode_kursus,
                ];
            }),
            'nama_modul' => $this->nama_modul,
            'urutan' => $this->urutan,
            'deskripsi' => $this->deskripsi,
            'is_published' => $this->is_published,
            'total_durasi' => $this->total_durasi,
            'jumlah_materi' => $this->jumlah_materi,

            // Only show materis if user is authenticated AND materis are loaded
            'materis' => $this->when($isAuthenticated && $this->relationLoaded('materis'), function () {
                return MateriResource::collection($this->materis);
            }),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
