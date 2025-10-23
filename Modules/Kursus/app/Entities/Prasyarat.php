<?php

namespace Modules\Kursus\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Kursus\Database\Factories\PrasyaratFactory;

class Prasyarat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): PrasyaratFactory
    // {
    //     // return PrasyaratFactory::new();
    // }
}
