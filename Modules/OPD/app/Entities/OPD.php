<?php

namespace Modules\OPD\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;

class OPD extends Model
{
    use HasFactory;

    protected $table = 'opds';
    protected $fillable = [
        'kode_opd',
        'nama_opd',
        'alamat',
        'no_telepon',
        'email',
        'nama_kepala',
    ];

    public function pesertas()
    {
        return $this->hasMany(Peserta::class);
    }
}
