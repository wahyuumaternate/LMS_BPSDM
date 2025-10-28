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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke Peserta
    public function pesertas()
    {
        return $this->hasMany(\Modules\Peserta\Entities\Peserta::class, 'opd_id');
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('kode_opd', 'like', "%{$search}%")
                ->orWhere('nama_opd', 'like', "%{$search}%")
                ->orWhere('nama_kepala', 'like', "%{$search}%");
        });
    }
}
