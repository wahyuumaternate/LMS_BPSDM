<?php

namespace Modules\Forum\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Kursus\Entities\Kursus;

class Forum extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'forum';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kursus_id',
        'judul',
        'deskripsi',
        'platform',
        'link_grup',
        'is_aktif'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_aktif' => 'boolean'
    ];

    /**
     * Relasi dengan model Kursus
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kursus()
    {
        return $this->belongsTo(Kursus::class);
    }

    /**
     * Mendapatkan opsi platform
     *
     * @return array
     */
    public static function getPlatformOptions()
    {
        return [
            'telegram' => 'Telegram',
            'whatsapp' => 'WhatsApp',
            'other' => 'Lainnya'
        ];
    }

    /**
     * Scope query untuk mendapatkan forum yang aktif
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }
}
