<?php

namespace Modules\Quiz\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    use HasFactory;

    protected $table = 'quiz_options';

    protected $fillable = [
        'question_id',
        'teks_opsi',
        'is_jawaban_benar',
        'urutan',
    ];

    protected $casts = [
        'is_jawaban_benar' => 'boolean',
        'urutan' => 'integer',
    ];

    // Relasi ke Question
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
