<?php

namespace Modules\Tugas\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;
use Modules\AdminInstruktur\Entities\AdminInstruktur;

class TugasSubmission extends Model
{
    use HasFactory;

    protected $table = 'tugas_submissions';

    protected $fillable = [
        'tugas_id',
        'peserta_id',
        'admin_instruktur_id',
        'catatan_peserta',
        'file_jawaban',
        'catatan_penilai',
        'nilai',
        'tanggal_submit',
        'tanggal_dinilai',
        'status',
    ];

    protected $casts = [
        'tanggal_submit' => 'datetime',
        'tanggal_dinilai' => 'datetime',
    ];

    // Relasi ke Tugas
    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    // Relasi ke Peserta
    public function peserta()
    {
        return $this->belongsTo(\Modules\Peserta\Entities\Peserta::class, 'peserta_id');
    }

    // Relasi ke Penilai (Admin/Instruktur)
    public function penilai()
    {
        return $this->belongsTo(\Modules\AdminInstruktur\Entities\AdminInstruktur::class, 'admin_instruktur_id');
    }

    // Get file URL
    public function getFileUrl()
    {
        return $this->file_jawaban ? asset('storage/' . $this->file_jawaban) : null;
    }

    // Check if late submission
    public function isLate()
    {
        return $this->status === 'late';
    }

    // Check if graded
    public function isGraded()
    {
        return $this->status === 'graded';
    }
}
