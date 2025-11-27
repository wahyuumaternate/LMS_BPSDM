<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Entities\QuizResult;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Student Quiz",
 *     description="API Endpoints untuk peserta mengikuti Quiz"
 * )
 */
class StudentQuizController extends Controller
{

     /**
     * Convert date to WIT timezone (Asia/Jayapura)
     * 
     * @param mixed $date
     * @return string|null
     */
    private function toWIT($date)
    {
        return $date
            ? Carbon::parse($date)->setTimezone('Asia/Jayapura')->format('Y-m-d H:i:s')
            : null;
    }

    /**
     * Constructor - Pastikan hanya peserta yang bisa akses
     */
    public function __construct()
    {
        $this->middleware('auth:peserta');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/quizzes",
     *     summary="Mendapatkan daftar quiz yang tersedia",
     *     tags={"Student Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="modul_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Filter quiz berdasarkan modul"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar quiz berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="modul_id", type="integer", example=1),
     *                     @OA\Property(property="judul_quiz", type="string", example="Quiz Laravel Basics"),
     *                     @OA\Property(property="deskripsi", type="string", example="Quiz tentang dasar Laravel"),
     *                     @OA\Property(property="durasi_menit", type="integer", example=60),
     *                     @OA\Property(property="jumlah_soal", type="integer", example=10),
     *                     @OA\Property(property="passing_grade", type="number", format="float", example=70.0),
     *                     @OA\Property(property="max_attempt", type="integer", example=3),
     *                     @OA\Property(property="my_attempt_count", type="integer", example=1),
     *                     @OA\Property(property="remaining_attempts", type="integer", example=2, nullable=true),
     *                     @OA\Property(property="my_best_score", type="number", format="float", example=85.5, nullable=true),
     *                     @OA\Property(property="is_passed", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['modul']);

        if ($request->has('modul_id')) {
            $query->where('modul_id', $request->modul_id);
        }

        $quizzes = $query->get();
        $user = auth('peserta')->user();

        return response()->json([
            'data' => $quizzes->map(function ($quiz) use ($user) {
                // Hitung berapa kali user sudah attempt
                $attemptCount = QuizResult::where('quiz_id', $quiz->id)
                    ->where('peserta_id', $user->id)
                    ->count();

                // Get best score
                $bestAttempt = QuizResult::where('quiz_id', $quiz->id)
                    ->where('peserta_id', $user->id)
                    ->orderBy('nilai', 'desc')
                    ->first();

                return [
                    'id' => $quiz->id,
                    'modul_id' => $quiz->modul_id,
                    'judul_quiz' => $quiz->judul_quiz,
                    'deskripsi' => $quiz->deskripsi,
                    'durasi_menit' => $quiz->durasi_menit,
                    'jumlah_soal' => $quiz->jumlah_soal,
                    'passing_grade' => $quiz->passing_grade,
                    'max_attempt' => $quiz->max_attempt,
                    'my_attempt_count' => $attemptCount,
                    'remaining_attempts' => $quiz->max_attempt > 0 ? max(0, $quiz->max_attempt - $attemptCount) : null,
                    'my_best_score' => $bestAttempt ? $bestAttempt->nilai : null,
                    'is_passed' => $bestAttempt ? $bestAttempt->is_passed : false,
                ];
            })
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/quizzes/{id}",
     *     summary="Mendapatkan detail quiz",
     *     tags={"Student Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Quiz"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail quiz berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="modul_name", type="string", example="Laravel Fundamentals"),
     *                 @OA\Property(property="judul_quiz", type="string", example="Quiz Laravel Basics"),
     *                 @OA\Property(property="deskripsi", type="string", example="Quiz tentang dasar Laravel"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=60),
     *                 @OA\Property(property="bobot_nilai", type="number", format="float", example=20.0),
     *                 @OA\Property(property="passing_grade", type="number", format="float", example=70.0),
     *                 @OA\Property(property="jumlah_soal", type="integer", example=10),
     *                 @OA\Property(property="max_attempt", type="integer", example=3),
     *                 @OA\Property(property="tampilkan_hasil", type="boolean", example=true),
     *                 @OA\Property(property="my_attempt_count", type="integer", example=1),
     *                 @OA\Property(property="remaining_attempts", type="integer", example=2, nullable=true),
     *                 @OA\Property(property="my_best_score", type="number", format="float", example=85.5, nullable=true),
     *                 @OA\Property(property="is_passed", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Quiz not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show($id)
    {
        $quiz = Quiz::with(['modul'])->findOrFail($id);
        $user = auth('peserta')->user();

        $attemptCount = QuizResult::where('quiz_id', $quiz->id)
            ->where('peserta_id', $user->id)
            ->count();

        $bestAttempt = QuizResult::where('quiz_id', $quiz->id)
            ->where('peserta_id', $user->id)
            ->orderBy('nilai', 'desc')
            ->first();

        return response()->json([
            'data' => [
                'id' => $quiz->id,
                'modul_id' => $quiz->modul_id,
                'modul_name' => $quiz->modul->nama_modul ?? null,
                'judul_quiz' => $quiz->judul_quiz,
                'deskripsi' => $quiz->deskripsi,
                'durasi_menit' => $quiz->durasi_menit,
                'bobot_nilai' => $quiz->bobot_nilai,
                'passing_grade' => $quiz->passing_grade,
                'jumlah_soal' => $quiz->jumlah_soal,
                'max_attempt' => $quiz->max_attempt,
                'tampilkan_hasil' => $quiz->tampilkan_hasil,
                'my_attempt_count' => $attemptCount,
                'remaining_attempts' => $quiz->max_attempt > 0 ? max(0, $quiz->max_attempt - $attemptCount) : null,
                'my_best_score' => $bestAttempt ? $bestAttempt->nilai : null,
                'is_passed' => $bestAttempt ? $bestAttempt->is_passed : false,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/student/quizzes/{id}/start",
     *     summary="Memulai quiz - Mendapatkan soal dan pilihan jawaban",
     *     tags={"Student Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Quiz"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz berhasil dimulai",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz started successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="quiz", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul_quiz", type="string", example="Quiz Laravel Basics"),
     *                     @OA\Property(property="deskripsi", type="string", example="Quiz tentang dasar Laravel"),
     *                     @OA\Property(property="durasi_menit", type="integer", example=60),
     *                     @OA\Property(property="jumlah_soal", type="integer", example=10),
     *                     @OA\Property(property="passing_grade", type="number", format="float", example=70.0)
     *                 ),
     *                 @OA\Property(property="questions", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="pertanyaan", type="string", example="Apa itu Laravel?"),
     *                         @OA\Property(property="poin", type="integer", example=10),
     *                         @OA\Property(property="tingkat_kesulitan", type="string", example="mudah"),
     *                         @OA\Property(property="options", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="teks_opsi", type="string", example="Framework PHP"),
     *                                 @OA\Property(property="urutan", type="integer", example=1)
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                 @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T11:00:00.000000Z", nullable=true),
     *                 @OA\Property(property="attempt_number", type="integer", example=1),
     *                 @OA\Property(property="remaining_attempts", type="integer", example=2, nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Maximum attempts reached",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have reached the maximum number of attempts"),
     *             @OA\Property(property="max_attempt", type="integer", example=3),
     *             @OA\Property(property="your_attempts", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Quiz not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function startQuiz($id)
    {
        $quiz = Quiz::with(['soalQuiz.options'])->findOrFail($id);
        $user = auth('peserta')->user();

        // Cek jumlah attempt yang sudah dilakukan
        $attemptCount = QuizResult::where('quiz_id', $id)
            ->where('peserta_id', $user->id)
            ->count();

        // Cek apakah sudah melebihi max attempt
        if ($quiz->max_attempt > 0 && $attemptCount >= $quiz->max_attempt) {
            return response()->json([
                'message' => 'You have reached the maximum number of attempts',
                'max_attempt' => $quiz->max_attempt,
                'your_attempts' => $attemptCount
            ], 403);
        }

        // Ambil soal
        $questions = $quiz->soalQuiz;

        // Jika random_soal = true, acak soal
        if ($quiz->random_soal) {
            $questions = $questions->shuffle();
        }

        // Format soal dengan pilihan jawaban (tanpa menampilkan jawaban yang benar)
        $formattedQuestions = $questions->map(function ($question) use ($quiz) {
            $options = $question->options;
            
            // Jika random_soal = true, acak juga pilihan jawaban
            if ($quiz->random_soal) {
                $options = $options->shuffle();
            }

            return [
                'id' => $question->id,
                'pertanyaan' => $question->pertanyaan,
                'poin' => $question->poin,
                'tingkat_kesulitan' => $question->tingkat_kesulitan,
                'options' => $options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'teks_opsi' => $option->teks_opsi,
                        'urutan' => $option->urutan,
                    ];
                })->values()
            ];
        })->values();

        $waktuMulai = Carbon::now();
        $waktuSelesai = $quiz->durasi_menit ? $waktuMulai->copy()->addMinutes($quiz->durasi_menit) : null;

        return response()->json([
            'message' => 'Quiz started successfully',
            'data' => [
                'quiz' => [
                    'id' => $quiz->id,
                    'judul_quiz' => $quiz->judul_quiz,
                    'deskripsi' => $quiz->deskripsi,
                    'durasi_menit' => $quiz->durasi_menit,
                    'jumlah_soal' => $quiz->jumlah_soal,
                    'passing_grade' => $quiz->passing_grade,
                ],
                'questions' => $formattedQuestions,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'attempt_number' => $attemptCount + 1,
                'remaining_attempts' => $quiz->max_attempt > 0 ? $quiz->max_attempt - $attemptCount : null,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/student/quizzes/submit",
     *     summary="Submit jawaban quiz",
     *     tags={"Student Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quiz_id", "answers", "waktu_mulai"},
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00Z"),
     *             @OA\Property(
     *                 property="answers",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="question_id", type="integer", example=1),
     *                     @OA\Property(property="option_id", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Quiz berhasil disubmit")
     * )
     */
    public function submitQuiz(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.option_id' => 'required|exists:quiz_options,id',
            'waktu_mulai' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = auth('peserta')->user();
            $quiz = Quiz::with('soalQuiz.options')->findOrFail($request->quiz_id);
            $waktuMulai = Carbon::parse($request->waktu_mulai);
            $waktuSelesai = Carbon::now();

            // Validasi: cek apakah waktu sudah habis
            if ($quiz->durasi_menit) {
                $batasWaktu = $waktuMulai->copy()->addMinutes($quiz->durasi_menit);
                if ($waktuSelesai->greaterThan($batasWaktu)) {
                    return response()->json([
                        'message' => 'Quiz time has expired'
                    ], 403);
                }
            }

            // Cek max attempt
            $attemptCount = QuizResult::where('quiz_id', $quiz->id)
                ->where('peserta_id', $user->id)
                ->count();

            if ($quiz->max_attempt > 0 && $attemptCount >= $quiz->max_attempt) {
                return response()->json([
                    'message' => 'You have reached the maximum number of attempts'
                ], 403);
            }

            $jumlahBenar = 0;
            $jumlahSalah = 0;
            $totalTidakJawab = 0;
            $totalPoin = 0;
            $poinDiperoleh = 0;
            
            // Format jawaban untuk disimpan sebagai JSON
            $jawabanData = [];

            // Hitung nilai dan simpan jawaban
            foreach ($quiz->soalQuiz as $question) {
                $totalPoin += $question->poin;
                
                // Cari jawaban user untuk question ini
                $userAnswer = collect($request->answers)->firstWhere('question_id', $question->id);
                
                if ($userAnswer) {
                    $optionId = $userAnswer['option_id'];
                    $option = $question->options->firstWhere('id', $optionId);
                    
                    if ($option) {
                        $isCorrect = $option->is_jawaban_benar;
                        
                        if ($isCorrect) {
                            $jumlahBenar++;
                            $poinDiperoleh += $question->poin;
                        } else {
                            $jumlahSalah++;
                        }
                        
                        // Simpan jawaban
                        $jawabanData[$question->id] = $optionId;
                    } else {
                        $totalTidakJawab++;
                    }
                } else {
                    $totalTidakJawab++;
                }
            }

            // Hitung nilai (persentase)
            $nilai = $totalPoin > 0 ? ($poinDiperoleh / $totalPoin) * 100 : 0;
            
            // Hitung durasi
            $durasiMenit = $waktuSelesai->diffInMinutes($waktuMulai);

            // Simpan hasil
            $result = QuizResult::create([
                'quiz_id' => $quiz->id,
                'peserta_id' => $user->id,
                'attempt' => $attemptCount + 1,
                'nilai' => round($nilai, 2),
                'jumlah_benar' => $jumlahBenar,
                'jumlah_salah' => $jumlahSalah,
                'total_tidak_jawab' => $totalTidakJawab,
                'is_passed' => $nilai >= $quiz->passing_grade,
                'jawaban' => $jawabanData,
                'durasi_pengerjaan_menit' => $durasiMenit,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
            ]);

            DB::commit();

            // Response data
            $responseData = [
                'result_id' => $result->id,
                'quiz_id' => $quiz->id,
                'attempt_number' => $result->attempt,
                'total_soal' => $quiz->jumlah_soal,
                'jawaban_benar' => $jumlahBenar,
                'jawaban_salah' => $jumlahSalah,
                'tidak_dijawab' => $totalTidakJawab,
                'total_poin' => $totalPoin,
                'poin_diperoleh' => $poinDiperoleh,
                'nilai' => round($nilai, 2),
                'passing_grade' => $quiz->passing_grade,
                'status' => $nilai >= $quiz->passing_grade ? 'passed' : 'failed',
                'durasi_menit' => $durasiMenit,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
            ];

            // Jika tampilkan_hasil = false, sembunyikan detail hasil
            if (!$quiz->tampilkan_hasil) {
                return response()->json([
                    'message' => 'Quiz submitted successfully. Results will be available later.',
                    'data' => [
                        'result_id' => $result->id,
                        'waktu_selesai' => $waktuSelesai,
                        'status' => 'submitted'
                    ]
                ]);
            }

            return response()->json([
                'message' => 'Quiz submitted successfully',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/my-attempts",
     *     summary="Mendapatkan riwayat quiz attempts saya",
     *     tags={"Student Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="quiz_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Filter attempts berdasarkan quiz"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar quiz attempts berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="quiz_id", type="integer", example=1),
     *                     @OA\Property(property="quiz_title", type="string", example="Quiz Laravel Basics"),
     *                     @OA\Property(property="attempt_number", type="integer", example=1),
     *                     @OA\Property(property="nilai", type="number", format="float", example=85.5),
     *                     @OA\Property(property="status", type="string", example="passed"),
     *                     @OA\Property(property="jumlah_benar", type="integer", example=8),
     *                     @OA\Property(property="jumlah_salah", type="integer", example=2),
     *                     @OA\Property(property="durasi_menit", type="integer", example=45),
     *                     @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                     @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function myAttempts(Request $request)
    {
        $user = auth('peserta')->user();
        $query = QuizResult::with('quiz')
            ->where('peserta_id', $user->id);

        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        $attempts = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $attempts->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'quiz_id' => $attempt->quiz_id,
                    'quiz_title' => $attempt->quiz->judul_quiz,
                    'attempt_number' => $attempt->attempt,
                    'nilai' => $attempt->nilai,
                    'status' => $attempt->is_passed ? 'passed' : 'failed',
                    'jumlah_benar' => $attempt->jumlah_benar,
                    'jumlah_salah' => $attempt->jumlah_salah,
                    'durasi_menit' => $attempt->durasi_pengerjaan_menit,
                    'waktu_mulai' => $attempt->waktu_mulai,
                    'waktu_selesai' => $attempt->waktu_selesai,
                ];
            })
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/results/{id}",
     *     summary="Mendapatkan hasil detail quiz",
     *     tags={"Student Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Quiz Result"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hasil quiz berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="result_id", type="integer", example=1),
     *                 @OA\Property(property="quiz_title", type="string", example="Quiz Laravel Basics"),
     *                 @OA\Property(property="attempt_number", type="integer", example=1),
     *                 @OA\Property(property="nilai", type="number", format="float", example=85.5),
     *                 @OA\Property(property="passing_grade", type="number", format="float", example=70.0),
     *                 @OA\Property(property="status", type="string", example="passed"),
     *                 @OA\Property(property="jumlah_benar", type="integer", example=8),
     *                 @OA\Property(property="jumlah_salah", type="integer", example=2),
     *                 @OA\Property(property="tidak_dijawab", type="integer", example=0),
     *                 @OA\Property(property="durasi_menit", type="integer", example=45),
     *                 @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                 @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                 @OA\Property(property="detail_jawaban", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="question_id", type="integer", example=1),
     *                         @OA\Property(property="pertanyaan", type="string", example="Apa itu Laravel?"),
     *                         @OA\Property(property="poin", type="integer", example=10),
     *                         @OA\Property(property="tingkat_kesulitan", type="string", example="mudah"),
     *                         @OA\Property(property="jawaban_anda", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="teks", type="string", example="Framework PHP")
     *                         ),
     *                         @OA\Property(property="jawaban_benar", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="teks", type="string", example="Framework PHP")
     *                         ),
     *                         @OA\Property(property="is_correct", type="boolean", example=true),
     *                         @OA\Property(property="pembahasan", type="string", example="Laravel adalah framework PHP yang populer", nullable=true)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized or results not available",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Results are not available for this quiz")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Result not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getResult($id)
    {
        
        $user = auth('peserta')->user();
        $result = QuizResult::with(['quiz.soalQuiz.options'])->findOrFail($id);

        // Validasi: pastikan result milik user yang login
        if ($result->peserta_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access to this quiz result'
            ], 403);
        }

        // Jika tampilkan_hasil = false, sembunyikan detail hasil
        if (!$result->quiz->tampilkan_hasil) {
            return response()->json([
                'message' => 'Results are not available for this quiz',
                'data' => [
                    'result_id' => $result->id,
                    'status' => 'submitted',
                    'waktu_selesai' => $result->waktu_selesai,
                ]
            ], 403);
        }

        // Format detail jawaban
        $detailJawaban = [];
        foreach ($result->quiz->soalQuiz as $question) {
            $userOptionId = $result->jawaban[$question->id] ?? null;
            $userOption = $userOptionId ? $question->options->firstWhere('id', $userOptionId) : null;
            $correctOption = $question->options->firstWhere('is_jawaban_benar', true);
            
            $detailJawaban[] = [
                'question_id' => $question->id,
                'pertanyaan' => $question->pertanyaan,
                'poin' => $question->poin,
                'tingkat_kesulitan' => $question->tingkat_kesulitan,
                'jawaban_anda' => $userOption ? [
                    'id' => $userOption->id,
                    'teks' => $userOption->teks_opsi,
                ] : null,
                'jawaban_benar' => [
                    'id' => $correctOption->id,
                    'teks' => $correctOption->teks_opsi,
                ],
                'is_correct' => $userOption ? $userOption->is_jawaban_benar : false,
                'pembahasan' => $question->pembahasan,
            ];
        }

        return response()->json([
            'data' => [
                'result_id' => $result->id,
                'quiz_title' => $result->quiz->judul_quiz,
                'attempt_number' => $result->attempt,
                'nilai' => $result->nilai,
                'passing_grade' => $result->quiz->passing_grade,
                'status' => $result->is_passed ? 'passed' : 'failed',
                'jumlah_benar' => $result->jumlah_benar,
                'jumlah_salah' => $result->jumlah_salah,
                'tidak_dijawab' => $result->total_tidak_jawab,
                'durasi_menit' => $result->durasi_pengerjaan_menit,
                'waktu_mulai' => $this->toWIT($result->waktu_mulai),
                'waktu_selesai' => $this->toWIT($result->waktu_selesai),
                'detail_jawaban' => $detailJawaban,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student/quizzes/{id}/my-best-attempt",
     *     summary="Mendapatkan attempt terbaik saya untuk quiz tertentu",
     *     tags={"Student Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID Quiz"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Best attempt berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="quiz_id", type="integer", example=1),
     *                 @OA\Property(property="quiz_title", type="string", example="Quiz Laravel Basics"),
     *                 @OA\Property(property="attempt_number", type="integer", example=2),
     *                 @OA\Property(property="nilai", type="number", format="float", example=90.0),
     *                 @OA\Property(property="status", type="string", example="passed"),
     *                 @OA\Property(property="jumlah_benar", type="integer", example=9),
     *                 @OA\Property(property="jumlah_salah", type="integer", example=1),
     *                 @OA\Property(property="durasi_menit", type="integer", example=50),
     *                 @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                 @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:50:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No attempts found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No completed attempts found for this quiz")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function myBestAttempt($id)
    {
        $user = auth('peserta')->user();
        $quiz = Quiz::findOrFail($id);

        $bestResult = QuizResult::where('quiz_id', $id)
            ->where('peserta_id', $user->id)
            ->orderBy('nilai', 'desc')
            ->first();

        if (!$bestResult) {
            return response()->json([
                'message' => 'No completed attempts found for this quiz'
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $bestResult->id,
                'quiz_id' => $bestResult->quiz_id,
                'quiz_title' => $quiz->judul_quiz,
                'attempt_number' => $bestResult->attempt,
                'nilai' => $bestResult->nilai,
                'status' => $bestResult->is_passed ? 'passed' : 'failed',
                'jumlah_benar' => $bestResult->jumlah_benar,
                'jumlah_salah' => $bestResult->jumlah_salah,
                'durasi_menit' => $bestResult->durasi_pengerjaan_menit,
                'waktu_mulai' => $bestResult->waktu_mulai,
                'waktu_selesai' => $bestResult->waktu_selesai,
            ]
        ]);
    }
}