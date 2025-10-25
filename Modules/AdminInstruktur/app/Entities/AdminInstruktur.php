<?php

namespace Modules\AdminInstruktur\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AdminInstruktur extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    protected $table = 'admin_instrukturs'; // sesuaikan nama tabel
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
        'foto_profil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function kursus()
    {
        return $this->hasMany(\Modules\Kursus\Entities\Kursus::class);
    }

    public function penilaianTugas()
    {
        return $this->hasMany(\Modules\Tugas\Entities\PenilaianTugas::class);
    }

    public function getNamaLengkapDenganGelarAttribute()
    {
        $nama = $this->nama_lengkap;
        if ($this->gelar_depan) $nama = $this->gelar_depan . ' ' . $nama;
        if ($this->gelar_belakang) $nama .= ', ' . $this->gelar_belakang;
        return $nama;
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isInstruktur()
    {
        return $this->role === 'instruktur';
    }
}
