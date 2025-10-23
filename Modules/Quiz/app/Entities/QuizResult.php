<?php

namespace Modules\Quiz\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;

class QuizResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'peserta_id',
        'skor',
        'total_benar',
        'total_salah',
        'total_tidak_jawab',
        'percobaan_ke',
        'is_lulus',
        'jawaban',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_pengerjaan_detik',
    ];

    protected $casts = [
        'is_lulus' => 'boolean',
        'jawaban' => 'array',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function calculateScore()
    {
        if (!$this->jawaban) {
            return 0;
        }

        $quiz = $this->quiz()->with('questions.options')->first();
        $questions = $quiz->questions;

        $totalBenar = 0;
        $totalSalah = 0;
        $totalTidakJawab = 0;
        $totalBobot = $questions->sum('bobot_nilai');

        foreach ($questions as $question) {
            $jawaban = $this->jawaban[$question->id] ?? null;

            if ($jawaban === null) {
                $totalTidakJawab++;
                continue;
            }

            if ($question->isAnswerCorrect($jawaban)) {
                $totalBenar++;
            } else {
                $totalSalah++;
            }
        }

        // Hitung skor
        $skor = 0;
        if ($totalBobot > 0) {
            $skor = ($totalBenar * 100) / $questions->count();
        }

        // Update data
        $this->skor = round($skor, 2);
        $this->total_benar = $totalBenar;
        $this->total_salah = $totalSalah;
        $this->total_tidak_jawab = $totalTidakJawab;
        $this->is_lulus = $this->skor >= $quiz->nilai_lulus;

        return $this->skor;
    }

    public function getDuration()
    {
        if ($this->waktu_mulai && $this->waktu_selesai) {
            return $this->waktu_selesai->diffInSeconds($this->waktu_mulai);
        }

        return 0;
    }
}
