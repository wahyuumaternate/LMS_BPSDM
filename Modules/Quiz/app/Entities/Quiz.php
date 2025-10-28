<?php

namespace Modules\Quiz\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Modul\Entities\Modul;

class Quiz extends Model
{
    use HasFactory;

    protected $table = 'quizzes';

    protected $fillable = [
        'modul_id',
        'judul_quiz',          // ✅ DIPERBAIKI dari 'judul'
        'deskripsi',
        'durasi_menit',
        'bobot_nilai',         // ✅ DITAMBAHKAN
        'passing_grade',       // ✅ DIPERBAIKI dari 'nilai_lulus'
        'jumlah_soal',
        'random_soal',         // ✅ DIPERBAIKI dari 'is_random_question'
        'tampilkan_hasil',     // ✅ DIPERBAIKI dari 'is_show_result'
        'max_attempt',         // ✅ DIPERBAIKI dari 'batas_percobaan'
        'is_published',        // OPSIONAL - bisa dihapus jika tidak dipakai
        'published_at',        // OPSIONAL - bisa dihapus jika tidak dipakai
    ];

    protected $casts = [
        'random_soal' => 'boolean',
        'tampilkan_hasil' => 'boolean',
        'bobot_nilai' => 'decimal:2',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Relasi ke Modul
    public function modul()
    {
        return $this->belongsTo(Modul::class);
    }

    // Relasi ke Soal Quiz (gunakan nama 'soalQuiz' sesuai controller)
    public function soalQuiz()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    // Alias untuk 'questions'
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    // Relasi ke Quiz Results
    public function results()
    {
        return $this->hasMany(QuizResult::class, 'quiz_id');
    }

    // Scope untuk published quiz
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // Method untuk mendapatkan soal (random atau tidak)
    public function getQuestions($randomize = null)
    {
        $randomize = $randomize ?? $this->random_soal;
        $query = $this->soalQuiz();

        if ($randomize) {
            return $query->inRandomOrder()->limit($this->jumlah_soal)->get();
        }

        return $query->limit($this->jumlah_soal)->get();
    }
}
