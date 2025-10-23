<?php

namespace Modules\Quiz\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'teks_opsi',
        'is_jawaban_benar',
        'urutan',
    ];

    protected $casts = [
        'is_jawaban_benar' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
