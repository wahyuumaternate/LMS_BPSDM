<?php

namespace Modules\Quiz\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_id',
        'pertanyaan',
        'pilihan_a',           // ✅ DITAMBAHKAN
        'pilihan_b',           // ✅ DITAMBAHKAN
        'pilihan_c',           // ✅ DITAMBAHKAN
        'pilihan_d',           // ✅ DITAMBAHKAN
        'jawaban_benar',       // ✅ DITAMBAHKAN
        'poin',                // ✅ DIPERBAIKI dari 'bobot_nilai'
        'pembahasan',          // ✅ DIPERBAIKI dari 'penjelasan'
        'tingkat_kesulitan',   // ✅ DITAMBAHKAN
        // 'tipe',             // ❌ DIHAPUS - tidak ada di migration fix
        // 'urutan',           // ❌ DIHAPUS - tidak ada di migration fix
    ];

    protected $casts = [
        'poin' => 'integer',
    ];

    // Relasi ke Quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    // Relasi ke Quiz Options (untuk sistem yang lebih fleksibel)
    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('urutan');
    }

    // Method untuk mengecek jawaban benar (sesuai dengan controller)
    public function isAnswerCorrect($jawaban)
    {
        // Untuk sistem pilihan_a-d (controller menggunakan ini)
        return strtolower(trim($this->jawaban_benar)) === strtolower(trim($jawaban));
    }

    // Method untuk mendapatkan jawaban yang benar
    public function getCorrectAnswer()
    {
        return $this->jawaban_benar;
    }

    // Method untuk mendapatkan opsi yang benar (jika menggunakan QuizOption)
    public function getCorrectOption()
    {
        return $this->options()->where('is_jawaban_benar', true)->first();
    }
}
