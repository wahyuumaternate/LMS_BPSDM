<?php

namespace Modules\Materi\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Kursus\Entities\Kursus;
use Modules\Evaluasi\Entities\PenilaianTugas;
use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    use HasApiTokens, SoftDeletes;
    protected $table = 'admin_instruktur';
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'nama_lengkap',
        'nip',
        'gelar_depan',
        'gelar_belakang',
        'bidang_keahlian',
        'no_telepon',
        'alamat',
        'foto_profil'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function kursus()
    {
        return $this->hasMany(Kursus::class, 'admin_instruktur_id');
    }
    public function penilaianTugas()
    {
        return $this->hasMany(PenilaianTugas::class, 'admin_instruktur_id');
    }
}
