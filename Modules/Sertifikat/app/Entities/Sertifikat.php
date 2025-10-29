<?php

namespace Modules\Sertifikat\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Peserta\Entities\Peserta;
use Modules\Kursus\Entities\Kursus;

class Sertifikat extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sertifikat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'peserta_id',
        'kursus_id',
        'template_id',
        'nomor_sertifikat',
        'tanggal_terbit',
        'file_path',
        'qr_code',
        'signature_digital',
        'nama_penandatangan',
        'jabatan_penandatangan',
        'is_sent_email'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_terbit' => 'date',
        'is_sent_email' => 'boolean',
    ];

    /**
     * Get the peserta that owns the sertifikat.
     */
    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    /**
     * Get the kursus that owns the sertifikat.
     */
    public function kursus()
    {
        return $this->belongsTo(Kursus::class);
    }

    /**
     * Get the template that owns the sertifikat.
     */
    public function template()
    {
        return $this->belongsTo(TemplateSertifikat::class, 'template_id');
    }

    /**
     * Get the file URL for the sertifikat.
     *
     * @return string|null
     */
    public function getFileUrlAttribute()
    {
        if (empty($this->file_path)) {
            return null;
        }

        return url('storage/' . $this->file_path);
    }

    /**
     * Get the QR code URL for the sertifikat.
     *
     * @return string|null
     */
    public function getQrCodeUrlAttribute()
    {
        if (empty($this->qr_code)) {
            return null;
        }

        return url('storage/' . $this->qr_code);
    }
}
