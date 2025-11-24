<?php

namespace Modules\Peserta\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\OPD\Entities\OPD;
use Modules\Quiz\Entities\QuizResult;
use Modules\Ujian\Entities\UjianResult;

class Peserta extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;


    protected $table = 'pesertas';
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
        'foto_profil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_lahir' => 'date',
        'password' => 'hashed',
    ];

    public function opd()
    {
        return $this->belongsTo(OPD::class);
    }

    public function pendaftaranKursus()
    {
        return $this->hasMany(\Modules\Kursus\Entities\PendaftaranKursus::class);
    }

    // Relasi ke Quiz Results
    public function quizResults()
    {
        return $this->hasMany(QuizResult::class, 'peserta_id');
    }

    // Relasi ke Ujian Results
    public function ujianResults()
    {
        return $this->hasMany(UjianResult::class, 'peserta_id');
    }

    public function progresMateri()
    {
        return $this->hasMany(\Modules\Materi\Entities\ProgresMateri::class);
    }

    /**
     * Relasi ke Sertifikat
     */
    public function sertifikats()
    {
        return $this->hasMany(\Modules\Sertifikat\Entities\Sertifikat::class, 'peserta_id');
    }

    /**
     * Get sertifikat count
     */
    public function getSertifikatCountAttribute()
    {
        return $this->sertifikats()->count();
    }
}
