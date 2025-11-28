<?php

namespace Modules\Kursus\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\Forum\Entities\Forum;
use Modules\JadwalKegiatan\Entities\JadwalKegiatan;
use Modules\Kategori\Entities\JenisKursus;
use Modules\SesiKehadiran\Entities\SesiKehadiran;
use Modules\Ujian\Entities\Ujian;

class Kursus extends Model
{
    use HasFactory;
    protected $table = 'kursus';
    protected $fillable = [
        'admin_instruktur_id',
        'jenis_kursus_id', // Ganti dari kategori_id
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

    // Ganti relasi kategori dengan jenisKursus
    public function jenisKursus()
    {
        return $this->belongsTo(JenisKursus::class, 'jenis_kursus_id');
    }

    // Akses kategori melalui jenisKursus (optional, untuk backward compatibility)
    public function kategori()
    {
        return $this->hasOneThrough(
            \Modules\Kategori\Entities\KategoriKursus::class,
            JenisKursus::class,
            'id', // Foreign key on jenis_kursus table
            'id', // Foreign key on kategori_kursus table
            'jenis_kursus_id', // Local key on kursus table
            'kategori_kursus_id' // Local key on jenis_kursus table
        );
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
    
    public function jumlahPeserta()
    {
        return $this->pendaftaran()
            ->whereIn('status', ['pending', 'disetujui', 'aktif'])
            ->count();
    }

    public function ujians()
    {
        return $this->hasMany(Ujian::class, 'kursus_id');
    }

    public function forums()
    {
        return $this->hasMany(Forum::class);
    }

    public function jadwalKegiatan()
    {
        return $this->hasMany(JadwalKegiatan::class, 'kursus_id')->orderBy('waktu_mulai_kegiatan', 'asc');
    }

    public function sesiKehadiran()
    {
        return $this->hasMany(SesiKehadiran::class, 'kursus_id')
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_mulai', 'asc');
    }
}