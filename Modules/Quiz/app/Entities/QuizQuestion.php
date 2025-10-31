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
        'poin',
        'pembahasan',
        'tingkat_kesulitan',
        'urutan',              // Tambahkan kembali 'urutan' untuk mengurutkan soal
    ];

    protected $casts = [
        'poin' => 'integer',
        'urutan' => 'integer',
    ];

    // Relasi ke Quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    // Relasi ke Quiz Options
    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('urutan');
    }

    // Method untuk mendapatkan opsi yang benar
    public function getCorrectOption()
    {
        return $this->options()->where('is_jawaban_benar', true)->first();
    }

    // Method untuk mengecek jawaban benar (untuk model relasional)
    public function isAnswerCorrect($optionId)
    {
        // Mengecek apakah option_id yang dipilih adalah jawaban yang benar
        return $this->options()->where('id', $optionId)
            ->where('is_jawaban_benar', true)
            ->exists();
    }
}
