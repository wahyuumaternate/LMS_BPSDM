<?php

namespace Modules\Kursus\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Kategori\Transformers\KategoriKursusResource;
use Modules\AdminInstruktur\Transformers\AdminInstrukturResource;

class KursusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'admin_instruktur_id' => $this->admin_instruktur_id,
            'instruktur' => $this->whenLoaded('adminInstruktur', function () {
                return new AdminInstrukturResource($this->adminInstruktur);
            }),
            'kategori_id' => $this->kategori_id,
            'kategori' => $this->whenLoaded('kategori', function () {
                return new KategoriKursusResource($this->kategori);
            }),
            'kode_kursus' => $this->kode_kursus,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'tujuan_pembelajaran' => $this->tujuan_pembelajaran,
            'sasaran_peserta' => $this->sasaran_peserta,
            'durasi_jam' => $this->durasi_jam,
            'tanggal_buka_pendaftaran' => $this->tanggal_buka_pendaftaran ? $this->tanggal_buka_pendaftaran->format('Y-m-d') : null,
            'tanggal_tutup_pendaftaran' => $this->tanggal_tutup_pendaftaran ? $this->tanggal_tutup_pendaftaran->format('Y-m-d') : null,
            'tanggal_mulai_kursus' => $this->tanggal_mulai_kursus ? $this->tanggal_mulai_kursus->format('Y-m-d') : null,
            'tanggal_selesai_kursus' => $this->tanggal_selesai_kursus ? $this->tanggal_selesai_kursus->format('Y-m-d') : null,
            'kuota_peserta' => $this->kuota_peserta,
            'level' => $this->level,
            'tipe' => $this->tipe,
            'status' => $this->status,
            'thumbnail' => $this->thumbnail ? url('storage/kursus/thumbnail/' . $this->thumbnail) : null,
            'passing_grade' => $this->passing_grade,
            'is_pendaftaran_open' => $this->isPendaftaranOpen(),
            'jumlah_peserta' => $this->whenCounted('pendaftaran'),
            'prasyarats' => $this->whenLoaded('prasyarats', function () {
                return PrasyaratResource::collection($this->prasyarats);
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
