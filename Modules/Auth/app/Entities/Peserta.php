<?php

namespace Modules\Auth\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Master\Entities\OPD;
use Modules\Kursus\Entities\PendaftaranKursus;

class Peserta extends Authenticatable
{
    use HasApiTokens, SoftDeletes;
    protected $table = 'peserta';
    protected $fillable = [
        'opd_id',
        'username',
        'email',
        'password',
        'nama_lengkap',
        'nip',
        'pangkat_golongan',
        'jabatan',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'pendidikan_terakhir',
        'status_kepegawaian',
        'no_telepon',
        'alamat',
        'foto_profil'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function opd()
    {
        return $this->belongsTo(OPD::class, 'opd_id');
    }
    public function pendaftaranKursus()
    {
        return $this->hasMany(PendaftaranKursus::class, 'peserta_id');
    }
}
