<?php

namespace Modules\Ujian\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoalUjian extends Model
{
    use HasFactory;

    protected $table = 'soal_ujians';

    protected $fillable = [
        'ujian_id',
        'pertanyaan',
        'tipe_soal',
        'pilihan_a',
        'pilihan_b',
        'pilihan_c',
        'pilihan_d',
        'jawaban_benar',
        'poin',
        'pembahasan',
        'tingkat_kesulitan'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'poin' => 'integer',
        'tipe_soal' => 'string'
    ];

    /**
     * Boot method untuk selalu set tipe_soal ke pilihan_ganda
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($soal) {
            $soal->tipe_soal = 'pilihan_ganda';
        });

        static::updating(function ($soal) {
            $soal->tipe_soal = 'pilihan_ganda';
        });
    }

    /**
     * Get the ujian that owns the soal.
     */
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    /**
     * Get the formatted soal type
     */
    public function getFormattedType()
    {
        return 'Pilihan Ganda';
    }

    /**
     * Get the badge class for soal type
     */
    public function getTypeBadgeClass()
    {
        return 'badge-primary';
    }

    /**
     * Get the badge class for difficulty level
     */
    public function getDifficultyBadgeClass()
    {
        $classes = [
            'mudah' => 'badge-success',
            'sedang' => 'badge-warning',
            'sulit' => 'badge-danger'
        ];

        return $classes[$this->tingkat_kesulitan] ?? 'badge-secondary';
    }

    /**
     * Get formatted difficulty level
     */
    public function getFormattedDifficulty()
    {
        $levels = [
            'mudah' => 'Mudah',
            'sedang' => 'Sedang',
            'sulit' => 'Sulit'
        ];

        return $levels[$this->tingkat_kesulitan] ?? $this->tingkat_kesulitan;
    }
}
