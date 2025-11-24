<?php

namespace Modules\Sertifikat\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sertifikat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sertifikat';

    protected $fillable = [
        'peserta_id',
        'kursus_id',
        'nomor_sertifikat',
        'tanggal_terbit',
        'tempat_terbit',
        'nama_penandatangan1',
        'jabatan_penandatangan1',
        'nip_penandatangan1',
        'tanda_tangan1_path',
        'nama_penandatangan2',
        'jabatan_penandatangan2',
        'nip_penandatangan2',
        'tanda_tangan2_path',
        'file_path',
        'qr_code_path',
        'verification_url',
        'is_sent_email',
        'sent_email_at',
        'template_name',
        'notes',
        'status',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'is_sent_email' => 'boolean',
        'sent_email_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate nomor_sertifikat if not set
        static::creating(function ($model) {
            if (empty($model->nomor_sertifikat)) {
                $model->nomor_sertifikat = static::generateNomorSertifikat();
            }
            
            // Set default status if not set
            if (empty($model->status)) {
                $model->status = 'published';
            }
            
            // Set default template_name if not set
            if (empty($model->template_name)) {
                $model->template_name = 'default';
            }
        });
    }

    /**
     * Generate nomor sertifikat
     */
    public static function generateNomorSertifikat()
    {
        $config = config('sertifikat.nomor_format');
        $year = date('Y');
        
        // Get last number for this year
        $lastCertificate = static::withTrashed()
            ->where('nomor_sertifikat', 'LIKE', "%{$config['prefix']}{$config['separator']}{$year}%")
            ->orderBy('id', 'desc')
            ->first();
        
        $counter = 1;
        if ($lastCertificate) {
            // Extract counter from last certificate number
            preg_match('/(\d+)$/', $lastCertificate->nomor_sertifikat, $matches);
            $counter = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        }
        
        $counterStr = str_pad($counter, $config['counter_length'], '0', STR_PAD_LEFT);
        
        return $config['prefix'] . $config['separator'] . $year . $config['separator'] . $counterStr;
    }

    /**
     * Relasi ke Peserta
     */
    public function peserta()
    {
        return $this->belongsTo(\Modules\Peserta\Entities\Peserta::class);
    }

    /**
     * Relasi ke Kursus
     */
    public function kursus()
    {
        return $this->belongsTo(\Modules\Kursus\Entities\Kursus::class);
    }

    /**
     * Get full verification URL
     */
    public function getFullVerificationUrlAttribute()
    {
        return config('sertifikat.verification.base_url') . $this->nomor_sertifikat;
    }

    /**
     * Get formatted certificate number
     */
    public function getFormattedNomorAttribute()
    {
        return str_replace('/', ' / ', $this->nomor_sertifikat);
    }

    /**
     * Get formatted date
     */
    public function getFormattedTanggalAttribute()
    {
        setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian');
        return $this->tempat_terbit . ', ' . $this->tanggal_terbit->isoFormat('D MMMM Y');
    }

    /**
     * Scope: Published certificates
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Check if certificate is revoked
     */
    public function isRevoked()
    {
        return $this->status === 'revoked';
    }
}