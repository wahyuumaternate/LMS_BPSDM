<?php

namespace Modules\Sertifikat\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TemplateSertifikatResource extends JsonResource
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
            'nama_template' => $this->nama_template,
            'design_template' => $this->design_template,
            'path_background' => $this->path_background,
            'background_url' => $this->path_background ? url('storage/' . $this->path_background) : null,
            'signature_config' => $this->signature_config,
            'signature_config_array' => $this->signature_config_array,
            'logo_bpsdm_path' => $this->logo_bpsdm_path,
            'logo_bpsdm_url' => $this->logo_bpsdm_path ? url('storage/' . $this->logo_bpsdm_path) : null,
            'logo_pemda_path' => $this->logo_pemda_path,
            'logo_pemda_url' => $this->logo_pemda_path ? url('storage/' . $this->logo_pemda_path) : null,
            'footer_text' => $this->footer_text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
