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
        'poin' => 'integer'
    ];

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
        $types = [
            'pilihan_ganda' => 'Pilihan Ganda',
            'essay' => 'Essay',
            'benar_salah' => 'Benar/Salah'
        ];

        return $types[$this->tipe_soal] ?? $this->tipe_soal;
    }

    /**
     * Get the badge class for soal type
     */
    public function getTypeBadgeClass()
    {
        $classes = [
            'pilihan_ganda' => 'badge-primary',
            'essay' => 'badge-info',
            'benar_salah' => 'badge-warning'
        ];

        return $classes[$this->tipe_soal] ?? 'badge-secondary';
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
