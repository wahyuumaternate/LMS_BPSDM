<?php

namespace Modules\Sertifikat\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class SertifikatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'peserta_id' => $this->peserta_id,
            'peserta' => $this->whenLoaded('peserta', function () {
                return [
                    'id' => $this->peserta->id,
                    'nama_lengkap' => $this->peserta->nama_lengkap,
                    'email' => $this->peserta->email,
                    'nip' => $this->peserta->nip
                ];
            }),
            'kursus_id' => $this->kursus_id,
            'kursus' => $this->whenLoaded('kursus', function () {
                return [
                    'id' => $this->kursus->id,
                    'judul' => $this->kursus->judul
                ];
            }),
            'template_id' => $this->template_id,
            'template' => $this->whenLoaded('template', function () {
                return [
                    'id' => $this->template->id,
                    'nama_template' => $this->template->nama_template
                ];
            }),
            'nomor_sertifikat' => $this->nomor_sertifikat,
            'tanggal_terbit' => $this->tanggal_terbit,
            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'qr_code' => $this->qr_code,
            'qr_code_url' => $this->qr_code_url,
            'signature_digital' => $this->signature_digital,
            'nama_penandatangan' => $this->nama_penandatangan,
            'jabatan_penandatangan' => $this->jabatan_penandatangan,
            'is_sent_email' => $this->is_sent_email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
