<?php

namespace Modules\Ujian\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Peserta\Entities\Peserta;

class UjianResult extends Model
{
    use HasFactory;

    protected $table = 'ujian_results';

    protected $fillable = [
        'ujian_id',
        'peserta_id',
        'jawaban',
        'nilai',
        'is_passed',
        'waktu_mulai',
        'waktu_selesai',
        'tanggal_dinilai'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'jawaban' => 'array',
        'nilai' => 'decimal:2',
        'is_passed' => 'boolean',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'tanggal_dinilai' => 'datetime'
    ];

    /**
     * Get the ujian associated with the result.
     */
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    /**
     * Get the peserta associated with the result.
     */
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    /**
     * Check if the result is pending (started but not completed)
     */
    public function isPending()
    {
        return $this->waktu_mulai && !$this->waktu_selesai;
    }

    /**
     * Get the formatted duration taken
     */
    public function getDurationTaken()
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) {
            return '';
        }

        $duration = $this->waktu_mulai->diffInSeconds($this->waktu_selesai);

        $hours = floor($duration / 3600);
        $minutes = floor(($duration - ($hours * 3600)) / 60);
        $seconds = $duration - ($hours * 3600) - ($minutes * 60);

        $result = '';

        if ($hours > 0) {
            $result .= $hours . ' jam ';
        }

        if ($minutes > 0 || ($hours == 0 && $seconds == 0)) {
            $result .= $minutes . ' menit ';
        }

        if ($seconds > 0 || ($hours == 0 && $minutes == 0)) {
            $result .= $seconds . ' detik';
        }

        return trim($result);
    }

    /**
     * Get the correct answers count
     */
    public function getCorrectAnswersCount()
    {
        if (!is_array($this->jawaban)) {
            return 0;
        }

        $correct = 0;
        foreach ($this->jawaban as $jawaban) {
            if (isset($jawaban['benar']) && $jawaban['benar']) {
                $correct++;
            }
        }

        return $correct;
    }

    /**
     * Get the incorrect answers count
     */
    public function getIncorrectAnswersCount()
    {
        if (!is_array($this->jawaban) || empty($this->jawaban)) {
            return 0;
        }

        $ujian = $this->ujian;
        if (!$ujian) {
            return 0;
        }

        $soalCount = $ujian->jumlah_soal;
        $correct = $this->getCorrectAnswersCount();

        return $soalCount - $correct;
    }

    /**
     * Get the status badge class
     */
    public function getStatusBadgeClass()
    {
        if (!$this->waktu_selesai) {
            return 'badge-warning'; // In progress
        }

        return $this->is_passed ? 'badge-success' : 'badge-danger';
    }

    /**
     * Get the status text
     */
    public function getStatusText()
    {
        if (!$this->waktu_selesai) {
            return 'Sedang Dikerjakan';
        }

        return $this->is_passed ? 'Lulus' : 'Tidak Lulus';
    }
}
