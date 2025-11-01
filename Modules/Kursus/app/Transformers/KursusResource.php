<?php

namespace Modules\Kursus\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KursusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $thumbnailUrl = null;
        if ($this->thumbnail) {
            // Generate the URL for the thumbnail using Storage URL helper
            // This properly handles both local and production environments
            $thumbnailUrl = Storage::disk('public')->url('kursus/thumbnail/' . $this->thumbnail);
        }

        return [
            'id' => $this->id,
            'admin_instruktur_id' => $this->admin_instruktur_id,
            'instruktur' => $this->whenLoaded('adminInstruktur', function () {
                return [
                    'id' => $this->adminInstruktur->id,
                    'nama_lengkap' => $this->adminInstruktur->nama_lengkap,
                    'nama_dengan_gelar' => $this->adminInstruktur->nama_dengan_gelar,
                ];
            }),
            'kategori_id' => $this->kategori_id,
            'kategori' => $this->whenLoaded('kategori', function () {
                return [
                    'id' => $this->kategori->id,
                    'nama_kategori' => $this->kategori->nama_kategori,
                    'slug' => $this->kategori->slug,
                ];
            }),
            'kode_kursus' => $this->kode_kursus,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'tujuan_pembelajaran' => $this->tujuan_pembelajaran,
            'sasaran_peserta' => $this->sasaran_peserta,
            'durasi_jam' => $this->durasi_jam,
            'tanggal_buka_pendaftaran' => $this->tanggal_buka_pendaftaran,
            'tanggal_tutup_pendaftaran' => $this->tanggal_tutup_pendaftaran,
            'tanggal_mulai_kursus' => $this->tanggal_mulai_kursus,
            'tanggal_selesai_kursus' => $this->tanggal_selesai_kursus,
            'kuota_peserta' => $this->kuota_peserta,
            'level' => $this->level,
            'tipe' => $this->tipe,
            'status' => $this->status,
            'thumbnail' => $thumbnailUrl,
            'passing_grade' => $this->passing_grade,
            'is_pendaftaran_open' => $this->isPendaftaranOpen(),
            'jumlah_peserta' => $this->jumlahPeserta(),
            'prasyarats' => $this->whenLoaded('prasyarats', function () {
                return $this->prasyarats->map(function ($prasyarat) {
                    return [
                        'id' => $prasyarat->id,
                        'kursus_id' => $prasyarat->kursus_id,
                        'kursus_prasyarat_id' => $prasyarat->kursus_prasyarat_id,
                        'kursusPrasyarat' => $prasyarat->whenLoaded('kursusPrasyarat', function () use ($prasyarat) {
                            return [
                                'id' => $prasyarat->kursusPrasyarat->id,
                                'judul' => $prasyarat->kursusPrasyarat->judul,
                                'kode_kursus' => $prasyarat->kursusPrasyarat->kode_kursus,
                            ];
                        }),
                    ];
                });
            }),
            'enrollment' => $this->when(isset($this->enrollment), $this->enrollment),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
