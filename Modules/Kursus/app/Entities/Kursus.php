<?php

namespace Modules\Kursus\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Entities\AdminInstruktur;
use Modules\Master\Entities\KategoriKursus;
use Modules\Materi\Entities\Modul;

class Kursus extends Model
{
    protected $table = 'kursus';
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
        'passing_grade'
    ];
    protected $casts = [
        'tanggal_buka_pendaftaran' => 'date',
        'tanggal_tutup_pendaftaran' => 'date',
        'tanggal_mulai_kursus' => 'date',
        'tanggal_selesai_kursus' => 'date',
        'passing_grade' => 'float'
    ];
    public function instruktur()
    {
        return $this->belongsTo(AdminInstruktur::class, 'admin_instruktur_id');
    }
    public function kategori()
    {
        return $this->belongsTo(KategoriKursus::class, 'kategori_id');
    }
    public function modul()
    {
        return $this->hasMany(Modul::class, 'kursus_id');
    }
    public function pendaftaran()
    {
        return $this->hasMany(PendaftaranKursus::class, 'kursus_id');
    }
    public function prasyarat()
    {
        return $this->hasMany(Prasyarat::class, 'kursus_id');
    }
    public function getJumlahPesertaAttribute()
    {
        return $this->pendaftaran()
            ->whereIn('status', ['disetujui', 'aktif', 'selesai'])
            ->count();
    }
    public function getProgressPersenAttribute()
    {
        if (!$this->tanggal_mulai_kursus || !$this->tanggal_selesai_kursus) {
            return 0;
        }
        $now = now();
        $start = $this->tanggal_mulai_kursus;
        $end = $this->tanggal_selesai_kursus;
        if ($now < $start) {
            return 0;
        }
        if ($now > $end) {
            return 100;
        }
        $totalDays = $start->diffInDays($end) + 1;
        $passedDays = $start->diffInDays($now) + 1;
        return min(100, round(($passedDays / $totalDays) * 100));
    }
}
