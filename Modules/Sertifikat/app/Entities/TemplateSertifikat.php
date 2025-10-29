<?php

namespace Modules\Sertifikat\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateSertifikat extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'template_sertifikat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama_template',
        'design_template',
        'path_background',
        'signature_config',
        'logo_bpsdm_path',
        'logo_pemda_path',
        'footer_text'
    ];

    /**
     * Get the sertifikats associated with the template.
     */
    public function sertifikats()
    {
        return $this->hasMany(Sertifikat::class, 'template_id');
    }

    /**
     * Get the signature config as an array.
     *
     * @return array
     */
    public function getSignatureConfigArrayAttribute()
    {
        if (empty($this->signature_config)) {
            return [];
        }

        return json_decode($this->signature_config, true) ?: [];
    }
}
