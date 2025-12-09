<?php

namespace Modules\Materi\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Modul\Transformers\ModulResource;

class MateriResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'modul_id' => $this->modul_id,
            'modul' => $this->whenLoaded('modul', function () {
                return [
                    'id' => $this->modul->id,
                    'nama_modul' => $this->modul->nama_modul,
                    'kursus_id' => $this->modul->kursus_id,
                ];
            }),
            'judul_materi' => $this->judul_materi,
            'urutan' => $this->urutan,
            'tipe_konten' => $this->tipe_konten,
            // 'file_path' => $this->whenNotNull($this->file_path, function () {
            //     return $this->tipe_konten === 'link' ? $this->file_path : url('storage/materi/' . $this->file_path);
            // }),
            'deskripsi' => $this->deskripsi,
            'durasi_menit' => $this->durasi_menit,
            'ukuran_file' => $this->ukuran_file,
            'is_wajib' => $this->is_wajib,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at ? $this->published_at->format('Y-m-d H:i:s') : null,
            'is_video' => $this->is_video,
            'is_pdf' => $this->is_pdf,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
