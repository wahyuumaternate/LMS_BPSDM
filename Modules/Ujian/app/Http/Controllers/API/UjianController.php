<?php

namespace Modules\Ujian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Ujian\Entities\Ujian;
use Modules\Ujian\Entities\SoalUjian;
use Modules\Ujian\Entities\UjianResult;
use Modules\Ujian\Transformers\UjianResource;
use Modules\Ujian\Transformers\UjianResultResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Ujian Peserta",
 *     description="API Endpoints untuk peserta mengerjakan ujian"
 * )
 */
class UjianController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/ujian",
     *     summary="Mendapatkan daftar ujian yang tersedia untuk peserta",
     *     tags={"Ujian Peserta"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter berdasarkan ID kursus",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status (available, upcoming, completed, expired)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"available", "upcoming", "completed", "expired"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar ujian berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="judul_ujian", type="string", example="UTS Pemrograman Web"),
     *                     @OA\Property(property="deskripsi", type="string", example="Ujian Tengah Semester"),
     *                     @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-12-15 08:00:00"),
     *                     @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-12-15 10:00:00"),
     *                     @OA\Property(property="durasi_menit", type="integer", example=90),
     *                     @OA\Property(property="jumlah_soal", type="integer", example=30),
     *                     @OA\Property(property="passing_grade", type="integer", example=70),
     *                     @OA\Property(property="bobot_nilai", type="number", format="float", example=30.5),
     *                     @OA\Property(property="random_soal", type="boolean", example=true),
     *                     @OA\Property(property="tampilkan_hasil", type="boolean", example=true),
     *                     @OA\Property(property="status", type="string", example="available"),
     *                     @OA\Property(property="is_taken", type="boolean", example=false),
     *                     @OA\Property(property="is_completed", type="boolean", example=false),
     *                     @OA\Property(
     *                         property="kursus",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="nama_kursus", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai peserta'
            ], 403);
        }

        $query = Ujian::with(['kursus']);

        // Filter berdasarkan kursus_id
        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        $ujians = $query->orderBy('waktu_mulai', 'desc')->get();

        // Filter dan tambahkan informasi status untuk setiap ujian
        $now = Carbon::now();
        $ujians = $ujians->map(function ($ujian) use ($peserta, $now, $request) {
            // Cek status ujian
            $status = 'available';
            if ($ujian->waktu_mulai && $now->lt(Carbon::parse($ujian->waktu_mulai))) {
                $status = 'upcoming';
            } elseif ($ujian->waktu_selesai && $now->gt(Carbon::parse($ujian->waktu_selesai))) {
                $status = 'expired';
            }

            // Cek apakah peserta sudah mengambil ujian
            $result = UjianResult::where('ujian_id', $ujian->id)
                ->where('peserta_id', $peserta->id)
                ->first();

            $ujian->status = $status;
            $ujian->is_taken = $result ? true : false;
            $ujian->is_completed = $result && $result->waktu_selesai ? true : false;

            if ($result && $result->waktu_selesai) {
                $ujian->status = 'completed';
            }

            return $ujian;
        });

        // Filter berdasarkan status jika diminta
        if ($request->has('status')) {
            $ujians = $ujians->filter(function ($ujian) use ($request) {
                return $ujian->status === $request->status;
            })->values();
        }

        return UjianResource::collection($ujians);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ujian/{id}",
     *     summary="Mendapatkan detail ujian",
     *     tags={"Ujian Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID ujian",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail ujian berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="judul_ujian", type="string"),
     *                 @OA\Property(property="deskripsi", type="string"),
     *                 @OA\Property(property="aturan_ujian", type="string"),
     *                 @OA\Property(property="durasi_menit", type="integer"),
     *                 @OA\Property(property="jumlah_soal", type="integer"),
     *                 @OA\Property(property="passing_grade", type="integer"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="is_taken", type="boolean"),
     *                 @OA\Property(property="is_completed", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Ujian tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show($id)
    {
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai peserta'
            ], 403);
        }

        $ujian = Ujian::with(['kursus'])->findOrFail($id);

        // Cek status ujian
        $now = Carbon::now();
        $status = 'available';
        if ($ujian->waktu_mulai && $now->lt(Carbon::parse($ujian->waktu_mulai))) {
            $status = 'upcoming';
        } elseif ($ujian->waktu_selesai && $now->gt(Carbon::parse($ujian->waktu_selesai))) {
            $status = 'expired';
        }

        // Cek apakah peserta sudah mengambil ujian
        $result = UjianResult::where('ujian_id', $ujian->id)
            ->where('peserta_id', $peserta->id)
            ->first();

        $ujian->status = $status;
        $ujian->is_taken = $result ? true : false;
        $ujian->is_completed = $result && $result->waktu_selesai ? true : false;

        if ($result && $result->waktu_selesai) {
            $ujian->status = 'completed';
        }

        return new UjianResource($ujian);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ujian/{id}/mulai",
     *     summary="Memulai mengerjakan ujian",
     *     tags={"Ujian Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID ujian",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ujian berhasil dimulai",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ujian berhasil dimulai"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="ujian_result_id", type="integer"),
     *                 @OA\Property(property="waktu_mulai", type="string", format="date-time"),
     *                 @OA\Property(property="waktu_selesai", type="string", format="date-time"),
     *                 @OA\Property(property="sisa_waktu_detik", type="integer"),
     *                 @OA\Property(
     *                     property="soal",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="pertanyaan", type="string"),
     *                         @OA\Property(property="tipe_soal", type="string"),
     *                         @OA\Property(property="pilihan_a", type="string"),
     *                         @OA\Property(property="pilihan_b", type="string"),
     *                         @OA\Property(property="pilihan_c", type="string"),
     *                         @OA\Property(property="pilihan_d", type="string"),
     *                         @OA\Property(property="poin", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request - Ujian tidak tersedia atau sudah dikerjakan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function mulaiUjian($id)
    {
        $ujian = Ujian::with('kursus')->findOrFail($id);
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai peserta'
            ], 403);
        }

        // Check if exam is available
        $now = Carbon::now();
        if ($ujian->waktu_mulai && $now->lt(Carbon::parse($ujian->waktu_mulai))) {
            return response()->json([
                'message' => 'Ujian belum dimulai',
                'waktu_mulai' => $ujian->waktu_mulai
            ], 400);
        }

        if ($ujian->waktu_selesai && $now->gt(Carbon::parse($ujian->waktu_selesai))) {
            return response()->json([
                'message' => 'Ujian sudah berakhir',
                'waktu_selesai' => $ujian->waktu_selesai
            ], 400);
        }

        // Check if user has already taken this exam
        $existingResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $peserta->id)
            ->first();

        if ($existingResult && $existingResult->waktu_selesai) {
            return response()->json([
                'message' => 'Anda sudah mengerjakan ujian ini',
                'result_id' => $existingResult->id
            ], 400);
        }

        // Get questions
        $soalQuery = SoalUjian::where('ujian_id', $id);

        if ($ujian->random_soal) {
            $soalQuery->inRandomOrder();
        } else {
            $soalQuery->orderBy('id', 'asc');
        }

        $soalUjians = $soalQuery->get();

        if ($soalUjians->isEmpty()) {
            return response()->json([
                'message' => 'Ujian belum memiliki soal'
            ], 400);
        }

        // Create or update exam result
        if (!$existingResult) {
            $ujianResult = new UjianResult();
            $ujianResult->ujian_id = $id;
            $ujianResult->peserta_id = $peserta->id;
            $ujianResult->waktu_mulai = $now;
            $ujianResult->save();
        } else {
            $ujianResult = $existingResult;
            // If already started but not finished
            if (!$ujianResult->waktu_selesai) {
                // Check if time is still available
                $startTime = Carbon::parse($ujianResult->waktu_mulai);
                $endTime = $startTime->copy()->addMinutes($ujian->durasi_menit);

                if ($now->gt($endTime)) {
                    return response()->json([
                        'message' => 'Waktu ujian telah habis'
                    ], 400);
                }
            }
        }

        // Prepare timer information
        $waktuMulai = Carbon::parse($ujianResult->waktu_mulai);
        $waktuSelesai = $waktuMulai->copy()->addMinutes($ujian->durasi_menit);
        $sisa = $now->diffInSeconds($waktuSelesai, false);

        if ($sisa <= 0) {
            return response()->json([
                'message' => 'Waktu ujian telah habis'
            ], 400);
        }

        // Format soal (hide jawaban_benar dan pembahasan)
        $soalFormatted = $soalUjians->map(function ($soal) {
            return [
                'id' => $soal->id,
                'pertanyaan' => $soal->pertanyaan,
                'tipe_soal' => $soal->tipe_soal,
                'pilihan_a' => $soal->pilihan_a,
                'pilihan_b' => $soal->pilihan_b,
                'pilihan_c' => $soal->pilihan_c,
                'pilihan_d' => $soal->pilihan_d,
                'poin' => $soal->poin,
                'tingkat_kesulitan' => $soal->tingkat_kesulitan
            ];
        });

        return response()->json([
            'message' => 'Ujian berhasil dimulai',
            'data' => [
                'ujian_result_id' => $ujianResult->id,
                'ujian' => [
                    'id' => $ujian->id,
                    'judul_ujian' => $ujian->judul_ujian,
                    'deskripsi' => $ujian->deskripsi,
                    'aturan_ujian' => $ujian->aturan_ujian,
                    'durasi_menit' => $ujian->durasi_menit
                ],
                'waktu_mulai' => $waktuMulai->toDateTimeString(),
                'waktu_selesai' => $waktuSelesai->toDateTimeString(),
                'sisa_waktu_detik' => $sisa,
                'jumlah_soal' => $soalFormatted->count(),
                'soal' => $soalFormatted
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ujian/{id}/submit",
     *     summary="Submit jawaban ujian",
     *     tags={"Ujian Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID ujian",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"jawaban"},
     *             @OA\Property(
     *                 property="jawaban",
     *                 type="object",
     *                 description="Object dengan key berupa soal_id dan value berupa jawaban (A/B/C/D)",
     *                 example={"1": "A", "2": "B", "3": "C", "4": "D"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jawaban berhasil disubmit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ujian berhasil diselesaikan"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="ujian_result_id", type="integer"),
     *                 @OA\Property(property="nilai", type="number", format="float"),
     *                 @OA\Property(property="is_passed", type="boolean"),
     *                 @OA\Property(property="passing_grade", type="integer"),
     *                 @OA\Property(property="waktu_selesai", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function submitUjian(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jawaban' => 'required|array',
            'jawaban.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $ujian = Ujian::findOrFail($id);
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai peserta'
            ], 403);
        }

        // Get current result
        $ujianResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $peserta->id)
            ->first();

        if (!$ujianResult) {
            return response()->json([
                'message' => 'Anda belum memulai ujian ini'
            ], 400);
        }

        if ($ujianResult->waktu_selesai) {
            return response()->json([
                'message' => 'Anda sudah menyelesaikan ujian ini'
            ], 400);
        }

        // Check if time is still available
        $now = Carbon::now();
        $waktuMulai = Carbon::parse($ujianResult->waktu_mulai);
        $waktuSelesai = $waktuMulai->copy()->addMinutes($ujian->durasi_menit);

        if ($now->gt($waktuSelesai)) {
            return response()->json([
                'message' => 'Waktu ujian telah habis'
            ], 400);
        }

        // Collect answers
        $jawabanPeserta = $request->input('jawaban');
        $jawaban = [];
        $nilai = 0;
        $totalPoin = 0;

        // Get all questions
        $soalUjians = SoalUjian::where('ujian_id', $id)->get();

        foreach ($soalUjians as $soal) {
            $soalId = (string)$soal->id;
            $jawabanUser = isset($jawabanPeserta[$soalId]) ? $jawabanPeserta[$soalId] : '';

            $jawaban[$soal->id] = [
                'jawaban' => $jawabanUser,
                'benar' => false,
                'poin' => 0
            ];

            // Calculate points for multiple choice
            if ($soal->tipe_soal === 'pilihan_ganda') {
                if (strtoupper($jawabanUser) == strtoupper($soal->jawaban_benar)) {
                    $jawaban[$soal->id]['benar'] = true;
                    $jawaban[$soal->id]['poin'] = $soal->poin;
                    $nilai += $soal->poin;
                }
            }

            $totalPoin += $soal->poin;
        }

        // Calculate percentage score
        $nilaiPersen = 0;
        if ($totalPoin > 0) {
            $nilaiPersen = ($nilai / $totalPoin) * 100;
        }

        // Update result
        $ujianResult->jawaban = json_encode($jawaban);
        $ujianResult->nilai = $nilaiPersen;
        $ujianResult->is_passed = $nilaiPersen >= $ujian->passing_grade;
        $ujianResult->waktu_selesai = $now;
        $ujianResult->tanggal_dinilai = $now;
        $ujianResult->save();

        return response()->json([
            'message' => 'Ujian berhasil diselesaikan',
            'data' => [
                'ujian_result_id' => $ujianResult->id,
                'nilai' => round($nilaiPersen, 2),
                'nilai_mentah' => $nilai,
                'total_poin' => $totalPoin,
                'is_passed' => $ujianResult->is_passed,
                'passing_grade' => $ujian->passing_grade,
                'waktu_mulai' => $ujianResult->waktu_mulai,
                'waktu_selesai' => $ujianResult->waktu_selesai,
                'durasi_pengerjaan_menit' => $waktuMulai->diffInMinutes($now)
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ujian/{id}/hasil",
     *     summary="Mendapatkan hasil ujian peserta",
     *     tags={"Ujian Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID ujian",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hasil ujian berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="ujian_result_id", type="integer"),
     *                 @OA\Property(property="ujian", type="object"),
     *                 @OA\Property(property="nilai", type="number"),
     *                 @OA\Property(property="is_passed", type="boolean"),
     *                 @OA\Property(property="waktu_mulai", type="string"),
     *                 @OA\Property(property="waktu_selesai", type="string"),
     *                 @OA\Property(property="detail_jawaban", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Hasil ujian tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function hasil($id)
    {
        $ujian = Ujian::findOrFail($id);
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai peserta'
            ], 403);
        }

        $ujianResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $peserta->id)
            ->first();

        if (!$ujianResult) {
            return response()->json([
                'message' => 'Anda belum mengerjakan ujian ini'
            ], 404);
        }

        if (!$ujianResult->waktu_selesai) {
            return response()->json([
                'message' => 'Anda belum menyelesaikan ujian ini'
            ], 400);
        }

        $jawaban = json_decode($ujianResult->jawaban, true);
        $soalUjians = SoalUjian::where('ujian_id', $id)->get();

        // Format detail jawaban
        $detailJawaban = [];
        if ($ujian->tampilkan_hasil) {
            foreach ($soalUjians as $soal) {
                $soalId = (string)$soal->id;
                $jawabanData = isset($jawaban[$soalId]) ? $jawaban[$soalId] : null;

                $detailJawaban[] = [
                    'soal_id' => $soal->id,
                    'pertanyaan' => $soal->pertanyaan,
                    'tipe_soal' => $soal->tipe_soal,
                    'pilihan_a' => $soal->pilihan_a,
                    'pilihan_b' => $soal->pilihan_b,
                    'pilihan_c' => $soal->pilihan_c,
                    'pilihan_d' => $soal->pilihan_d,
                    'jawaban_peserta' => $jawabanData['jawaban'] ?? null,
                    'jawaban_benar' => $soal->jawaban_benar,
                    'is_correct' => $jawabanData['benar'] ?? false,
                    'poin_didapat' => $jawabanData['poin'] ?? 0,
                    'poin_maksimal' => $soal->poin,
                    'pembahasan' => $soal->pembahasan
                ];
            }
        }

        return response()->json([
            'data' => [
                'ujian_result_id' => $ujianResult->id,
                'ujian' => [
                    'id' => $ujian->id,
                    'judul_ujian' => $ujian->judul_ujian,
                    'deskripsi' => $ujian->deskripsi,
                    'passing_grade' => $ujian->passing_grade,
                    'bobot_nilai' => $ujian->bobot_nilai,
                    'tampilkan_hasil' => $ujian->tampilkan_hasil
                ],
                'nilai' => round($ujianResult->nilai, 2),
                'is_passed' => $ujianResult->is_passed,
                'waktu_mulai' => $ujianResult->waktu_mulai,
                'waktu_selesai' => $ujianResult->waktu_selesai,
                'durasi_pengerjaan_menit' => Carbon::parse($ujianResult->waktu_mulai)
                    ->diffInMinutes(Carbon::parse($ujianResult->waktu_selesai)),
                'tanggal_dinilai' => $ujianResult->tanggal_dinilai,
                'detail_jawaban' => $detailJawaban
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ujian/my-results",
     *     summary="Mendapatkan semua hasil ujian peserta",
     *     tags={"Ujian Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter berdasarkan ID kursus",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar hasil ujian berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="ujian", type="object"),
     *                     @OA\Property(property="nilai", type="number"),
     *                     @OA\Property(property="is_passed", type="boolean"),
     *                     @OA\Property(property="waktu_selesai", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function myResults(Request $request)
    {
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai peserta'
            ], 403);
        }

        $query = UjianResult::with(['ujian.kursus'])
            ->where('peserta_id', $peserta->id)
            ->whereNotNull('waktu_selesai');

        // Filter berdasarkan kursus_id
        if ($request->has('kursus_id')) {
            $query->whereHas('ujian', function ($q) use ($request) {
                $q->where('kursus_id', $request->kursus_id);
            });
        }

        $results = $query->orderBy('created_at', 'desc')->get();

        return UjianResultResource::collection($results);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ujian/{id}/status",
     *     summary="Cek status ujian peserta (sudah dikerjakan atau belum)",
     *     tags={"Ujian Peserta"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID ujian",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status ujian berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="ujian_id", type="integer"),
     *                 @OA\Property(property="is_taken", type="boolean"),
     *                 @OA\Property(property="is_completed", type="boolean"),
     *                 @OA\Property(property="is_in_progress", type="boolean"),
     *                 @OA\Property(property="can_start", type="boolean"),
     *                 @OA\Property(property="sisa_waktu_detik", type="integer", nullable=true),
     *                 @OA\Property(property="result", type="object", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Ujian tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function status($id)
    {
        $ujian = Ujian::findOrFail($id);
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai peserta'
            ], 403);
        }

        $result = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $peserta->id)
            ->first();

        $now = Carbon::now();
        $canStart = true;
        $statusMessage = 'Ujian tersedia';

        // Check exam availability
        if ($ujian->waktu_mulai && $now->lt(Carbon::parse($ujian->waktu_mulai))) {
            $canStart = false;
            $statusMessage = 'Ujian belum dimulai';
        } elseif ($ujian->waktu_selesai && $now->gt(Carbon::parse($ujian->waktu_selesai))) {
            $canStart = false;
            $statusMessage = 'Ujian sudah berakhir';
        }

        $isInProgress = false;
        $sisaWaktu = null;

        if ($result) {
            if ($result->waktu_selesai) {
                $canStart = false;
                $statusMessage = 'Ujian sudah diselesaikan';
            } else {
                // Check remaining time
                $waktuMulai = Carbon::parse($result->waktu_mulai);
                $waktuSelesai = $waktuMulai->copy()->addMinutes($ujian->durasi_menit);
                $sisaWaktu = $now->diffInSeconds($waktuSelesai, false);

                if ($sisaWaktu > 0) {
                    $isInProgress = true;
                    $statusMessage = 'Ujian sedang berlangsung';
                } else {
                    $canStart = false;
                    $statusMessage = 'Waktu ujian telah habis';
                }
            }
        }

        return response()->json([
            'data' => [
                'ujian_id' => $ujian->id,
                'is_taken' => $result ? true : false,
                'is_completed' => $result && $result->waktu_selesai ? true : false,
                'is_in_progress' => $isInProgress,
                'can_start' => $canStart,
                'status_message' => $statusMessage,
                'sisa_waktu_detik' => $sisaWaktu,
                'result' => $result && $result->waktu_selesai ? [
                    'id' => $result->id,
                    'nilai' => round($result->nilai, 2),
                    'is_passed' => $result->is_passed,
                    'waktu_selesai' => $result->waktu_selesai
                ] : null
            ]
        ]);
    }
}
