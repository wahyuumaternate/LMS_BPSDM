<?php

namespace Modules\Tugas\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;
use Modules\AdminInstruktur\Entities\AdminInstruktur;

class TugasSubmission extends Model
{
    use HasFactory;

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

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function penilai()
    {
        return $this->belongsTo(AdminInstruktur::class, 'admin_instruktur_id');
    }

    public function isSubmitted()
    {
        return in_array($this->status, ['submitted', 'graded', 'returned']);
    }

    public function isGraded()
    {
        return in_array($this->status, ['graded', 'returned']);
    }

    public function isLate()
    {
        if (!$this->tanggal_submit || !$this->tugas->tanggal_deadline) {
            return false;
        }

        return $this->tanggal_submit->format('Y-m-d') > $this->tugas->tanggal_deadline->format('Y-m-d');
    }
}
