<?php

namespace Modules\Kursus\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prasyarat extends Model
{
    use HasFactory;

    protected $fillable = [
        'kursus_id',
        'kursus_prasyarat_id',
        'deskripsi',
        'is_wajib',
    ];

    protected $casts = [
        'is_wajib' => 'boolean',
    ];

    public function kursus()
    {
        return $this->belongsTo(Kursus::class, 'kursus_id');
    }

    public function kursusPrasyarat()
    {
        return $this->belongsTo(Kursus::class, 'kursus_prasyarat_id');
    }
}
