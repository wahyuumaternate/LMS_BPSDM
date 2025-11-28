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
    
    /**
     * Boot method untuk auto-generate kode kursus
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($kursus) {
            if (empty($kursus->kode_kursus)) {
                $kursus->kode_kursus = static::generateKodeKursus();
            }
        });
    }
    
   
    /**
     * Generate kode kursus otomatis
     * Format: PEL-YYYY-XXXX (XXXX = random unik)
     */
    public static function generateKodeKursus()
    {
        $year = date('Y');
        $prefix = "PEL-{$year}-";

        do {
            // Generate 4 digit random (0000–9999)
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            $kode = $prefix . $randomNumber;

            // Cek apakah kode sudah ada
            $exists = static::where('kode_kursus', $kode)->exists();
        } while ($exists); // ulang sampai dapat yang unik

        return $kode;
    }

    
    protected $fillable = [
        'admin_instruktur_id',
        'jenis_kursus_id',
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

    public function jenisKursus()
    {
        return $this->belongsTo(JenisKursus::class, 'jenis_kursus_id');
    }

    public function kategori()
    {
        return $this->hasOneThrough(
            \Modules\Kategori\Entities\KategoriKursus::class,
            JenisKursus::class,
            'id',
            'id',
            'jenis_kursus_id',
            'kategori_kursus_id'
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