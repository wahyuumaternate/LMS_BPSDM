<?php

namespace Modules\Materi\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Peserta\Entities\Peserta;

/**
 * @OA\Schema(
 *     schema="ProgresMateri",
 *     title="ProgresMateri",
 *     description="Progress record for participant learning materials",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="peserta_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="materi_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="is_selesai", type="boolean", example=false),
 *     @OA\Property(property="progress_persen", type="integer", example=50),
 *     @OA\Property(property="tanggal_mulai", type="string", format="date-time", example="2025-10-28T10:00:00Z"),
 *     @OA\Property(property="tanggal_selesai", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="durasi_belajar_menit", type="integer", example=30),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-28T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-28T10:30:00Z"),
 *     @OA\Property(
 *         property="materi",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="judul", type="string", example="Introduction to Programming"),
 *         @OA\Property(property="deskripsi", type="string", example="Basic programming concepts")
 *     ),
 *     @OA\Property(
 *         property="peserta",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="nama", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john.doe@example.com")
 *     )
 * )
 */
class ProgresMateri extends Model
{
    use HasFactory;

    protected $fillable = [
        'peserta_id',
        'materi_id',
        'is_selesai',
        'progress_persen',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi_belajar_menit',
    ];

    protected $casts = [
        'is_selesai' => 'boolean',
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    public function scopeSelesai($query)
    {
        return $query->where('is_selesai', true);
    }

    public function scopeBelumSelesai($query)
    {
        return $query->where('is_selesai', false);
    }
}
