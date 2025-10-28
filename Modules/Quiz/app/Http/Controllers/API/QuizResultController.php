<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Entities\QuizResult;
use Modules\Quiz\Entities\SoalQuiz;
use Modules\Quiz\Transformers\QuizResultResource;
use Modules\Quiz\Transformers\QuizForStudentResource;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Quiz Result",
 *     description="API Endpoints untuk manajemen hasil quiz"
 * )
 */
class QuizResultController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/quiz-results",
     *     summary="Mendapatkan daftar hasil quiz",
     *     tags={"Quiz Result"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="quiz_id",
     *         in="query",
     *         description="Filter berdasarkan ID quiz",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="peserta_id",
     *         in="query",
     *         description="Filter berdasarkan ID peserta",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_passed",
     *         in="query",
     *         description="Filter berdasarkan status kelulusan",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar hasil quiz berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="quiz_id", type="integer", example=1),
     *                     @OA\Property(property="peserta_id", type="integer", example=1),
     *                     @OA\Property(property="attempt", type="integer", example=1),
     *                     @OA\Property(property="nilai", type="number", format="float", example=85.5),
     *                     @OA\Property(property="jumlah_benar", type="integer", example=17),
     *                     @OA\Property(property="jumlah_salah", type="integer", example=3),
     *                     @OA\Property(property="is_passed", type="boolean", example=true),
     *                     @OA\Property(property="durasi_pengerjaan_menit", type="integer", example=45),
     *                     @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                     @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = QuizResult::with(['quiz', 'peserta']);

        // Filter by quiz_id
        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter by peserta_id
        if ($request->has('peserta_id')) {
            $query->where('peserta_id', $request->peserta_id);
        }

        // Filter by is_passed
        if ($request->has('is_passed')) {
            $query->where('is_passed', $request->boolean('is_passed'));
        }

        $results = $query->orderBy('created_at', 'desc')->get();

        return QuizResultResource::collection($results);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/quiz-results",
     *     summary="Submit hasil quiz",
     *     tags={"Quiz Result"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quiz_id", "peserta_id", "jawaban", "waktu_mulai", "waktu_selesai"},
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(property="peserta_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="jawaban",
     *                 type="object",
     *                 description="Object dengan key adalah soal_id dan value adalah jawaban (a/b/c/d)",
     *                 example={"1": "a", "2": "b", "3": "c"}
     *             ),
     *             @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00"),
     *             @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:45:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Hasil quiz berhasil disubmit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz result submitted successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="quiz_id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="attempt", type="integer", example=1),
     *                 @OA\Property(property="nilai", type="number", format="float", example=85.5),
     *                 @OA\Property(property="jumlah_benar", type="integer", example=17),
     *                 @OA\Property(property="jumlah_salah", type="integer", example=3),
     *                 @OA\Property(property="is_passed", type="boolean", example=true),
     *                 @OA\Property(property="durasi_pengerjaan_menit", type="integer", example=45),
     *                 @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                 @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error atau maximum attempts tercapai",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have reached the maximum number of attempts for this quiz."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'peserta_id' => 'required|exists:pesertas,id',
            'jawaban' => 'required|array',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after_or_equal:waktu_mulai',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the quiz
        $quiz = Quiz::with('soalQuiz')->findOrFail($request->quiz_id);

        // Check max attempts
        if ($quiz->max_attempt > 0) {
            $attemptCount = QuizResult::where('quiz_id', $quiz->id)
                ->where('peserta_id', $request->peserta_id)
                ->count();

            if ($attemptCount >= $quiz->max_attempt) {
                return response()->json([
                    'message' => 'You have reached the maximum number of attempts for this quiz.'
                ], 422);
            }
        }

        // Calculate attempt number
        $attempt = QuizResult::where('quiz_id', $quiz->id)
            ->where('peserta_id', $request->peserta_id)
            ->max('attempt') + 1;

        // Prepare result data
        $resultData = [
            'quiz_id' => $request->quiz_id,
            'peserta_id' => $request->peserta_id,
            'attempt' => $attempt,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
        ];

        // Calculate jumlah_benar, jumlah_salah, dan total_tidak_jawab
        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $totalTidakJawab = 0;

        foreach ($quiz->soalQuiz as $soal) {
            if (isset($request->jawaban[$soal->id])) {
                $jawaban = $request->jawaban[$soal->id];
                if ($soal->isAnswerCorrect($jawaban)) {
                    $jumlahBenar++;
                } else {
                    $jumlahSalah++;
                }
            } else {
                $totalTidakJawab++;
            }
        }

        // Calculate nilai
        $totalSoal = $quiz->jumlah_soal;
        $nilai = 0;

        if ($totalSoal > 0) {
            $nilai = ($jumlahBenar / $totalSoal) * 100;
        }

        // Update result data
        $resultData['nilai'] = $nilai;
        $resultData['jumlah_benar'] = $jumlahBenar;
        $resultData['jumlah_salah'] = $jumlahSalah;
        $resultData['total_tidak_jawab'] = $totalTidakJawab;
        $resultData['is_passed'] = $nilai >= $quiz->passing_grade;
        $resultData['jawaban'] = json_encode($request->jawaban); // Simpan jawaban peserta
        $resultData['durasi_pengerjaan_menit'] =
            ceil((strtotime($request->waktu_selesai) - strtotime($request->waktu_mulai)) / 60);

        // Create result
        $result = QuizResult::create($resultData);

        return response()->json([
            'message' => 'Quiz result submitted successfully',
            'data' => new QuizResultResource($result)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/quiz-results/{id}",
     *     summary="Mendapatkan detail hasil quiz",
     *     tags={"Quiz Result"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID hasil quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail hasil quiz berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="quiz_id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="attempt", type="integer", example=1),
     *                 @OA\Property(property="nilai", type="number", format="float", example=85.5),
     *                 @OA\Property(property="jumlah_benar", type="integer", example=17),
     *                 @OA\Property(property="jumlah_salah", type="integer", example=3),
     *                 @OA\Property(property="is_passed", type="boolean", example=true),
     *                 @OA\Property(property="durasi_pengerjaan_menit", type="integer", example=45),
     *                 @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                 @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hasil quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $result = QuizResult::with(['quiz', 'peserta'])->findOrFail($id);
        return new QuizResultResource($result);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/quiz-results/{id}",
     *     summary="Mengupdate hasil quiz",
     *     tags={"Quiz Result"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID hasil quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nilai", type="number", format="float", minimum=0, maximum=100, example=90),
     *             @OA\Property(property="jumlah_benar", type="integer", minimum=0, example=18),
     *             @OA\Property(property="jumlah_salah", type="integer", minimum=0, example=2),
     *             @OA\Property(property="is_passed", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hasil quiz berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz result updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="quiz_id", type="integer", example=1),
     *                 @OA\Property(property="peserta_id", type="integer", example=1),
     *                 @OA\Property(property="attempt", type="integer", example=1),
     *                 @OA\Property(property="nilai", type="number", format="float", example=90),
     *                 @OA\Property(property="jumlah_benar", type="integer", example=18),
     *                 @OA\Property(property="jumlah_salah", type="integer", example=2),
     *                 @OA\Property(property="is_passed", type="boolean", example=true),
     *                 @OA\Property(property="durasi_pengerjaan_menit", type="integer", example=45),
     *                 @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                 @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hasil quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $result = QuizResult::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nilai' => 'sometimes|required|numeric|min:0|max:100',
            'jumlah_benar' => 'sometimes|required|integer|min:0',
            'jumlah_salah' => 'sometimes|required|integer|min:0',
            'is_passed' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result->update($request->all());

        return response()->json([
            'message' => 'Quiz result updated successfully',
            'data' => new QuizResultResource($result)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/quiz-results/{id}",
     *     summary="Menghapus hasil quiz",
     *     tags={"Quiz Result"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID hasil quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hasil quiz berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz result deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hasil quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $result = QuizResult::findOrFail($id);
        $result->delete();

        return response()->json([
            'message' => 'Quiz result deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/quiz-results/peserta/{peserta_id}",
     *     summary="Mendapatkan hasil quiz berdasarkan peserta",
     *     tags={"Quiz Result"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="peserta_id",
     *         in="path",
     *         description="ID peserta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="quiz_id",
     *         in="query",
     *         description="Filter berdasarkan ID quiz",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar hasil quiz peserta berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="quiz_id", type="integer", example=1),
     *                     @OA\Property(property="peserta_id", type="integer", example=1),
     *                     @OA\Property(property="attempt", type="integer", example=1),
     *                     @OA\Property(property="nilai", type="number", format="float", example=85.5),
     *                     @OA\Property(property="jumlah_benar", type="integer", example=17),
     *                     @OA\Property(property="jumlah_salah", type="integer", example=3),
     *                     @OA\Property(property="is_passed", type="boolean", example=true),
     *                     @OA\Property(property="durasi_pengerjaan_menit", type="integer", example=45),
     *                     @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z"),
     *                     @OA\Property(property="waktu_selesai", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:45:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getByPeserta(Request $request, $pesertaId)
    {
        $validator = Validator::make(['peserta_id' => $pesertaId], [
            'peserta_id' => 'required|exists:pesertas,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = QuizResult::with(['quiz.modul'])
            ->where('peserta_id', $pesertaId);

        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        $results = $query->orderBy('created_at', 'desc')->get();

        return QuizResultResource::collection($results);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/quiz-results/start",
     *     summary="Memulai quiz dan mendapatkan soal-soal",
     *     tags={"Quiz Result"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quiz_id", "peserta_id"},
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(property="peserta_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz berhasil dimulai",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="quiz_info",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Quiz Pemrograman Dasar"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=60),
     *                 @OA\Property(property="jumlah_soal", type="integer", example=20)
     *             ),
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="pertanyaan", type="string", example="Apa itu variable?"),
     *                     @OA\Property(
     *                         property="pilihan",
     *                         type="object",
     *                         @OA\Property(property="a", type="string", example="Tempat menyimpan data"),
     *                         @OA\Property(property="b", type="string", example="Fungsi matematika"),
     *                         @OA\Property(property="c", type="string", example="Tipe data"),
     *                         @OA\Property(property="d", type="string", example="Operator")
     *                     ),
     *                     @OA\Property(property="poin", type="integer", example=5)
     *                 )
     *             ),
     *             @OA\Property(property="waktu_mulai", type="string", format="date-time", example="2024-01-01T10:00:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error atau maximum attempts tercapai",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have reached the maximum number of attempts for this quiz."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function startQuiz(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'peserta_id' => 'required|exists:pesertas,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the quiz
        $quiz = Quiz::findOrFail($request->quiz_id);

        // Check max attempts
        if ($quiz->max_attempt > 0) {
            $attemptCount = QuizResult::where('quiz_id', $quiz->id)
                ->where('peserta_id', $request->peserta_id)
                ->count();

            if ($attemptCount >= $quiz->max_attempt) {
                return response()->json([
                    'message' => 'You have reached the maximum number of attempts for this quiz.'
                ], 422);
            }
        }

        // Get questions for the quiz
        $questions = $quiz->getQuestions();

        // Hide correct answers - Gunakan Resource
        $questionsData = QuizForStudentResource::collection($questions);

        return response()->json([
            'quiz_info' => [
                'id' => $quiz->id,
                'judul' => $quiz->judul_quiz,
                'durasi_menit' => $quiz->durasi_menit,
                'jumlah_soal' => $quiz->jumlah_soal,
            ],
            'questions' => $questionsData,
            'waktu_mulai' => now(),
        ]);
    }
}
