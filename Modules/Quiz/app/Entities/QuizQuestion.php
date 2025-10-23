<?php

namespace Modules\Quiz\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'pertanyaan',
        'tipe',
        'bobot_nilai',
        'penjelasan',
        'urutan',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('urutan');
    }

    public function getCorrectOption()
    {
        if ($this->tipe === 'benar_salah' || $this->tipe === 'pilihan_ganda') {
            return $this->options()->where('is_jawaban_benar', true)->first();
        }
        
        return null;
    }

    public function getCorrectAnswer()
    {
        $correctOption = $this->getCorrectOption();
        return $correctOption ? $correctOption->id : null;
    }

    public function isAnswerCorrect($answer)
    {
        // Untuk tipe benar_salah dan pilihan_ganda
        if ($this->tipe === 'benar_salah' || $this->tipe === 'pilihan_ganda') {
            $correctOption = $this->getCorrectOption();
            return $correctOption && $correctOption->id == $answer;
        }
        
        // Untuk tipe isian (harus diimplementasikan logikanya)
        if ($this->tipe === 'isian') {
            // Logika untuk mengecek jawaban isian
            // ...
        }
        
        return false;
    }
}
