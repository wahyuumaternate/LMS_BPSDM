<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Entities\QuizResult;
use Modules\Quiz\Entities\SoalQuiz;
use Modules\Quiz\Transformers\QuizResultResource;
use Illuminate\Support\Facades\Validator;

class QuizResultController extends Controller
{
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

        // Calculate jumlah_benar and jumlah_salah
        $jumlahBenar = 0;
        $jumlahSalah = 0;

        foreach ($quiz->soalQuiz as $soal) {
            if (isset($request->jawaban[$soal->id])) {
                $jawaban = $request->jawaban[$soal->id];
                if ($soal->isAnswerCorrect($jawaban)) {
                    $jumlahBenar++;
                } else {
                    $jumlahSalah++;
                }
            } else {
                $jumlahSalah++;
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
        $resultData['is_passed'] = $nilai >= $quiz->passing_grade;
        $resultData['durasi_pengerjaan_menit'] =
            ceil((strtotime($request->waktu_selesai) - strtotime($request->waktu_mulai)) / 60);

        // Create result
        $result = QuizResult::create($resultData);

        return response()->json([
            'message' => 'Quiz result submitted successfully',
            'data' => new QuizResultResource($result)
        ], 201);
    }

    public function show($id)
    {
        $result = QuizResult::with(['quiz', 'peserta'])->findOrFail($id);
        return new QuizResultResource($result);
    }

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

    public function destroy($id)
    {
        $result = QuizResult::findOrFail($id);
        $result->delete();

        return response()->json([
            'message' => 'Quiz result deleted successfully'
        ]);
    }

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

        // Hide correct answers
        $questionsData = [];
        foreach ($questions as $question) {
            $questionsData[] = [
                'id' => $question->id,
                'pertanyaan' => $question->pertanyaan,
                'pilihan' => [
                    'a' => $question->pilihan_a,
                    'b' => $question->pilihan_b,
                    'c' => $question->pilihan_c,
                    'd' => $question->pilihan_d,
                ],
                'poin' => $question->poin
            ];
        }

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
