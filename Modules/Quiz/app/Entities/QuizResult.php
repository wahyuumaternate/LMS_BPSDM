<?php

namespace Modules\Quiz\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;

class QuizResult extends Model
{
    use HasFactory;

    protected $table = 'quiz_results';

    protected $fillable = [
        'quiz_id',
        'peserta_id',
        'attempt',                    // ✅ DIPERBAIKI dari 'percobaan_ke'
        'nilai',                      // ✅ DIPERBAIKI dari 'skor'
        'jumlah_benar',              // ✅ DIPERBAIKI dari 'total_benar'
        'jumlah_salah',              // ✅ DIPERBAIKI dari 'total_salah'
        'total_tidak_jawab',         // ✅ SUDAH BENAR
        'is_passed',                 // ✅ DIPERBAIKI dari 'is_lulus'
        'jawaban',                   // ✅ SUDAH BENAR
        'durasi_pengerjaan_menit',   // ✅ DIPERBAIKI dari 'durasi_pengerjaan_detik'
        'waktu_mulai',               // ✅ SUDAH BENAR
        'waktu_selesai',             // ✅ SUDAH BENAR
    ];

    protected $casts = [
        'is_passed' => 'boolean',
        'nilai' => 'decimal:2',
        'jumlah_benar' => 'integer',
        'jumlah_salah' => 'integer',
        'total_tidak_jawab' => 'integer',
        'attempt' => 'integer',
        'durasi_pengerjaan_menit' => 'integer',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'jawaban' => 'array',
    ];

    // Relasi ke Quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    // Relasi ke Peserta
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    // Method untuk menghitung skor
    public function calculateScore()
    {
        if (!$this->jawaban) {
            return 0;
        }

        $quiz = $this->quiz()->with('soalQuiz')->first();
        $questions = $quiz->soalQuiz;

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $totalTidakJawab = 0;

        foreach ($questions as $question) {
            $jawaban = $this->jawaban[$question->id] ?? null;

            if ($jawaban === null) {
                $totalTidakJawab++;
                continue;
            }

            if ($question->isAnswerCorrect($jawaban)) {
                $jumlahBenar++;
            } else {
                $jumlahSalah++;
            }
        }

        // Hitung nilai (0-100)
        $nilai = 0;
        if ($questions->count() > 0) {
            $nilai = ($jumlahBenar / $questions->count()) * 100;
        }

        // Update data
        $this->nilai = round($nilai, 2);
        $this->jumlah_benar = $jumlahBenar;
        $this->jumlah_salah = $jumlahSalah;
        $this->total_tidak_jawab = $totalTidakJawab;
        $this->is_passed = $this->nilai >= $quiz->passing_grade;

        return $this->nilai;
    }

    // Method untuk mendapatkan durasi (dalam menit)
    public function getDuration()
    {
        if ($this->waktu_mulai && $this->waktu_selesai) {
            return $this->waktu_selesai->diffInMinutes($this->waktu_mulai);
        }

        return 0;
    }
}
