<?php

namespace Modules\Materi\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

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

            // ✅ Pilih salah satu metode di bawah ini:

            // METODE 1: File disimpan langsung di storage/materi/ (RECOMMENDED)
            'file_path' => $this->getFilePathDirect(),

            // METODE 2: File disimpan di storage/materi/{tipe_konten}/
            // 'file_path' => $this->getFilePathWithType(),

            // METODE 3: File path sudah lengkap di database (termasuk subfolder)
            // 'file_path' => $this->getFilePathComplete(),

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

    /**
     * METODE 1: File langsung di storage/materi/
     * Database: materi-1-modul-1-1765273625.pdf
     * Fisik: storage/app/public/materi/materi-1-modul-1-1765273625.pdf
     * Output: https://domain.com/storage/materi/materi-1-modul-1-1765273625.pdf
     */
    protected function getFilePathDirect()
    {
        if (empty($this->file_path)) {
            return null;
        }

        if ($this->tipe_konten === 'link') {
            return $this->file_path;
        }
        if ($this->tipe_konten === 'video') {
            return $this->file_path;
        }

        return url('storage/materi/' . $this->file_path);
    }

    /**
     * METODE 2: File di storage/materi/{tipe_konten}/
     * Database: materi-1-modul-1-1765273625.pdf
     * Fisik: storage/app/public/materi/pdf/materi-1-modul-1-1765273625.pdf
     * Output: https://domain.com/storage/materi/pdf/materi-1-modul-1-1765273625.pdf
     */
    protected function getFilePathWithType()
    {
        if (empty($this->file_path)) {
            return null;
        }

        if ($this->tipe_konten === 'link') {
            return $this->file_path;
        }
        if ($this->tipe_konten === 'video') {
            return $this->file_path;
        }
        return url('storage/materi/' . $this->tipe_konten . '/' . $this->file_path);
    }

    /**
     * METODE 3: File path di database sudah lengkap (termasuk subfolder)
     * Database: pdf/materi-1-modul-1-1765273625.pdf
     * Fisik: storage/app/public/materi/pdf/materi-1-modul-1-1765273625.pdf
     * Output: https://domain.com/storage/materi/pdf/materi-1-modul-1-1765273625.pdf
     */
    protected function getFilePathComplete()
    {
        if (empty($this->file_path)) {
            return null;
        }

        if ($this->tipe_konten === 'link') {
            return $this->file_path;
        }

        if ($this->tipe_konten === 'video') {
            return $this->file_path;
        }
        return url('storage/materi/' . $this->file_path);
    }

    /**
     * Helper: Cek apakah URL valid
     */
    protected function isValidUrl($string)
    {
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * BONUS: Get file download URL dengan force download
     */
    protected function getDownloadUrl()
    {
        if (empty($this->file_path) || $this->tipe_konten === 'link' || $this->tipe_konten === 'video') {
            return null;
        }

        return route('materi.download', ['id' => $this->id]);
    }
}
