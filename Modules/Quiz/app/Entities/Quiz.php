<?php

namespace Modules\Quiz\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Modul\Entities\Modul;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'modul_id',
        'judul',
        'deskripsi',
        'durasi_menit',
        'jumlah_soal',
        'nilai_lulus',
        'batas_percobaan',
        'is_random_question',
        'is_show_result',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_random_question' => 'boolean',
        'is_show_result' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function modul()
    {
        return $this->belongsTo(Modul::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('urutan');
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getQuestions($randomize = null)
    {
        $randomize = $randomize ?? $this->is_random_question;
        
        $questions = $this->questions()->with('options');
        
        if ($randomize) {
            return $questions->inRandomOrder()->get();
        }
        
        return $questions->orderBy('urutan')->get();
    }
}
