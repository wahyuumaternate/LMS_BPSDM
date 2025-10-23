<?php

namespace Modules\Master\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Entities\Peserta;

class OPD extends Model
{
    protected $table = 'opd';
    protected $fillable = [
        'kode_opd',
        'nama_opd',
        'alamat',
        'no_telepon',
        'email',
        'nama_kepala'
    ];
    public function peserta()
    {
        return $this->hasMany(Peserta::class, 'opd_id');
    }
}
