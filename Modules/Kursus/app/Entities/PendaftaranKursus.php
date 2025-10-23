<?php

namespace Modules\Kursus\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Kursus\Database\Factories\PendaftaranKursusFactory;

class PendaftaranKursus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): PendaftaranKursusFactory
    // {
    //     // return PendaftaranKursusFactory::new();
    // }
}
