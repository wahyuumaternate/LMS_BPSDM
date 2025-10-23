<?php

namespace Modules\Kursus\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\Kategori\Entities\KategoriKursus;

class Kursus extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_instruktur_id',
        'kategori_id',
        'kode_kursus',
        'judul',
        'deskripsi',
        'tujuan_pembelajaran',
        'sasaran_peserta',
        'durasi_jam',
        'tanggal_buka_pendaftaran',
        'tanggal_tutup_pendaftaran',
        'tanggal_mulai_kursus',
        'tanggal_selesai_kursus',
        'kuota_peserta',
        'level',
        'tipe',
        'status',
        'thumbnail',
        'passing_grade',
    ];

    protected $casts = [
        'tanggal_buka_pendaftaran' => 'date',
        'tanggal_tutup_pendaftaran' => 'date',
        'tanggal_mulai_kursus' => 'date',
        'tanggal_selesai_kursus' => 'date',
        'passing_grade' => 'decimal:2',
    ];

    public function adminInstruktur()
    {
        return $this->belongsTo(AdminInstruktur::class);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriKursus::class, 'kategori_id');
    }

    public function prasyarats()
    {
        return $this->hasMany(Prasyarat::class);
    }

    public function pendaftaran()
    {
        return $this->hasMany(PendaftaranKursus::class);
    }

    public function peserta()
    {
        return $this->belongsToMany(\Modules\Peserta\Entities\Peserta::class, 'pendaftaran_kursus')
            ->withPivot('status', 'tanggal_daftar', 'nilai_akhir', 'predikat')
            ->withTimestamps();
    }

    public function modul()
    {
        return $this->hasMany(\Modules\Modul\Entities\Modul::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeNonaktif($query)
    {
        return $query->where('status', 'nonaktif');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function isPendaftaranOpen()
    {
        $today = now()->format('Y-m-d');
        return $this->status === 'aktif' &&
            $this->tanggal_buka_pendaftaran <= $today &&
            $this->tanggal_tutup_pendaftaran >= $today;
    }
}
