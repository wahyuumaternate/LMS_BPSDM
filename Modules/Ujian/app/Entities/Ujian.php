<?php

namespace Modules\Ujian\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Kursus\Entities\Kursus;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujians';

    protected $fillable = [
        'kursus_id',
        'judul_ujian',
        'deskripsi',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'bobot_nilai',
        'passing_grade',
        'jumlah_soal',
        'random_soal',
        'tampilkan_hasil',
        'aturan_ujian'
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_menit' => 'integer',
        'bobot_nilai' => 'decimal:2',
        'passing_grade' => 'integer',
        'jumlah_soal' => 'integer',
        'random_soal' => 'boolean',
        'tampilkan_hasil' => 'boolean'
    ];

    /**
     * Get the kursus associated with the ujian.
     */
    public function kursus()
    {
        return $this->belongsTo(Kursus::class, 'kursus_id');
    }

    /**
     * Get the soal ujians for the ujian.
     */
    public function soalUjians()
    {
        return $this->hasMany(SoalUjian::class, 'ujian_id');
    }

    /**
     * Get the ujian results for the ujian.
     */
    public function ujianResults()
    {
        return $this->hasMany(UjianResult::class, 'ujian_id');
    }

    /**
     * Check if ujian is active (current time is between waktu_mulai and waktu_selesai)
     */
    public function isActive()
    {
        $now = now();

        if ($this->waktu_mulai && $now->lt($this->waktu_mulai)) {
            return false; // Belum dimulai
        }

        if ($this->waktu_selesai && $now->gt($this->waktu_selesai)) {
            return false; // Sudah selesai
        }

        return true; // Aktif
    }

    /**
     * Check if ujian has started
     */
    public function hasStarted()
    {
        return !$this->waktu_mulai || now()->gte($this->waktu_mulai);
    }

    /**
     * Check if ujian has ended
     */
    public function hasEnded()
    {
        return $this->waktu_selesai && now()->gte($this->waktu_selesai);
    }

    /**
     * Get ujian status text
     */
    public function getStatusText()
    {
        if (!$this->hasStarted()) {
            return 'Belum Dimulai';
        } elseif ($this->hasEnded()) {
            return 'Sudah Berakhir';
        } else {
            return 'Sedang Berlangsung';
        }
    }

    /**
     * Get status badge color class
     */
    public function getStatusBadgeClass()
    {
        if (!$this->hasStarted()) {
            return 'badge-warning';
        } elseif ($this->hasEnded()) {
            return 'badge-danger';
        } else {
            return 'badge-success';
        }
    }

    /**
     * Get formatted duration
     */
    public function getDurationFormatted()
    {
        $hours = floor($this->durasi_menit / 60);
        $minutes = $this->durasi_menit % 60;

        $result = '';

        if ($hours > 0) {
            $result .= $hours . ' jam ';
        }

        if ($minutes > 0 || $hours == 0) {
            $result .= $minutes . ' menit';
        }

        return trim($result);
    }

    /**
     * Check if a peserta has taken this ujian
     */
    public function isTakenByPeserta($pesertaId)
    {
        return $this->ujianResults()
            ->where('peserta_id', $pesertaId)
            ->where('waktu_selesai', '!=', null)
            ->exists();
    }
    // Tambahkan method untuk check jika hasil ini adalah simulasi
    public function isSimulation()
    {
        return $this->is_simulation;
    }
}
