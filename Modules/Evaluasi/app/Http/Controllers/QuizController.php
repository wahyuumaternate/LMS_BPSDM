<?php

namespace Modules\Evaluasi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluasi\Entities\Quiz;
use Modules\Evaluasi\Entities\SoalQuiz;
use Modules\Evaluasi\Entities\QuizResult;
use Modules\Materi\Entities\Modul;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function index($modulId)
    {
        $modul = Modul::with('kursus:id,judul,admin_instruktur_id')->findOrFail($modulId);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        // If peserta, check if they're enrolled in the course
        if ($user->tokenCan('peserta')) {
            $isEnrolled = $user->pendaftaranKursus()
                ->where('kursus_id', $modul->kursus_id)
                ->whereIn('status', ['disetujui', 'aktif'])
                ->exists();
            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda belum terdaftar dalam kursus ini'
                ], 403);
            }
            // Peserta only sees published quizzes
            $quizzes = Quiz::where('modul_id', $modulId)
                ->where('is_published', true)
                ->get(['id', 'judul_quiz', 'deskripsi', 'durasi_menit', 'passing_grade', 'max_attempt']);
            // Get user's quiz results for each quiz
            foreach ($quizzes as $quiz) {
                $results = QuizResult::where('quiz_id', $quiz->id)
                    ->where('peserta_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $quiz->results = $results;
                $quiz->attempts = $results->count();
                $quiz->best_score = $results->max('nilai') ?? 0;
                $quiz->passed = $results->where('is_passed', true)->count() > 0;
            }
        }
        // If admin/instruktur, check if they have access to the course
        else if ($user->tokenCan('super_admin') || $modul->kursus->admin_instruktur_id === $user->id) {
            $quizzes = Quiz::where('modul_id', $modulId)
                ->withCount('soal')
                ->get();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'data' => [
                'modul' => [
                    'id' => $modul->id,
                    'nama_modul' => $modul->nama_modul,
                    'kursus' => [
                        'id' => $modul->kursus->id,
                        'judul' => $modul->kursus->judul
                    ]
                ],
                'quizzes' => $quizzes
            ]
        ]);
    }
    public function store(Request $request, $modulId)
    {
        $modul = Modul::with('kursus:id,admin_instruktur_id')->findOrFail($modulId);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'judul_quiz' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:1',
            'bobot_nilai' => 'required|numeric|min:0|max:100',
            'passing_grade' => 'required|integer|min:0|max:100',
            'jumlah_soal' => 'nullable|integer|min:0',
            'random_soal' => 'nullable|boolean',
            'tampilkan_hasil' => 'nullable|boolean',
            'max_attempt' => 'required|integer|min:1',
            'is_published' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $quiz = new Quiz($request->all());
        $quiz->modul_id = $modulId;
        $quiz->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Quiz berhasil ditambahkan',
            'data' => $quiz
        ], 201);
    }
    public function show($id)
    {
        $quiz = Quiz::with('modul.kursus:id,judul,admin_instruktur_id')->findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        // If peserta, check if they're enrolled in the course
        if ($user->tokenCan('peserta')) {
            $isEnrolled = $user->pendaftaranKursus()
                ->where('kursus_id', $quiz->modul->kursus_id)
                ->whereIn('status', ['disetujui', 'aktif'])
                ->exists();
            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda belum terdaftar dalam kursus ini'
                ], 403);
            }
            // Peserta cannot access unpublished quiz
            if (!$quiz->is_published) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Quiz belum dipublikasikan'
                ], 403);
            }
            // Get user's quiz results
            $results = QuizResult::where('quiz_id', $quiz->id)
                ->where('peserta_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            $quiz->results = $results;
            $quiz->attempts = $results->count();
            $quiz->best_score = $results->max('nilai') ?? 0;
            $quiz->passed = $results->where('is_passed', true)->count() > 0;
            // Check if user can take the quiz (max attempts not reached)
            $quiz->can_take = $quiz->attempts < $quiz->max_attempt || $quiz->max_attempt === 0;
            // Do not include soal for peserta
            unset($quiz->soal);
        }
        // If admin/instruktur, check if they have access to the course
        else if ($user->tokenCan('super_admin') || $quiz->modul->kursus->admin_instruktur_id === $user->id) {
            $quiz->load('soal');
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'data' => $quiz
        ]);
    }
    public function update(Request $request, $id)
    {
        $quiz = Quiz::with('modul.kursus:id,admin_instruktur_id')->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $quiz->modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'judul_quiz' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'sometimes|required|integer|min:1',
            'bobot_nilai' => 'sometimes|required|numeric|min:0|max:100',
            'passing_grade' => 'sometimes|required|integer|min:0|max:100',
            'jumlah_soal' => 'nullable|integer|min:0',
            'random_soal' => 'nullable|boolean',
            'tampilkan_hasil' => 'nullable|boolean',
            'max_attempt' => 'sometimes|required|integer|min:1',
            'is_published' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $quiz->update($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Quiz berhasil diperbarui',
            'data' => $quiz
        ]);
    }
    public function destroy($id)
    {
        $quiz = Quiz::with('modul.kursus:id,admin_instruktur_id')->findOrFail($id);
        // Check authorization
        $user = Auth::guard('sanctum')->user();
        if (!($user->tokenCan('super_admin') || $quiz->modul->kursus->admin_instruktur_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Check if quiz has results
        $resultCount = $quiz->hasil()->count();
        if ($resultCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz tidak dapat dihapus karena memiliki ' . $resultCount . ' hasil pengerjaan'
            ], 400);
        }
        // Delete all quiz questions first
        $quiz->soal()->delete();
        // Delete quiz
        $quiz->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Quiz berhasil dihapus'
        ]);
    }
    // Start quiz attempt
    public function startQuiz($id)
    {
        $quiz = Quiz::with('modul.kursus:id,admin_instruktur_id')->findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        if (!$user->tokenCan('peserta')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya peserta yang dapat mengerjakan quiz'
            ], 403);
        }
        // Check if user is enrolled in the course
        $isEnrolled = $user->pendaftaranKursus()
            ->where('kursus_id', $quiz->modul->kursus_id)
            ->whereIn('status', ['disetujui', 'aktif'])
            ->exists();
        if (!$isEnrolled) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum terdaftar dalam kursus ini'
            ], 403);
        }
        // Check if quiz is published
        if (!$quiz->is_published) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz belum dipublikasikan'
            ], 403);
        }
        // Check if max attempts reached
        $attemptCount = QuizResult::where('quiz_id', $quiz->id)
            ->where('peserta_id', $user->id)
            ->count();
        if ($attemptCount >= $quiz->max_attempt && $quiz->max_attempt !== 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda telah mencapai batas maksimal percobaan untuk quiz ini'
            ], 400);
        }
        // Get questions for the quiz
        if ($quiz->random_soal && $quiz->jumlah_soal > 0) {
            // Get random questions
            $soal = SoalQuiz::where('quiz_id', $quiz->id)
                ->inRandomOrder()
                ->limit($quiz->jumlah_soal)
                ->get(['id', 'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'poin', 'tingkat_kesulitan']);
        } else {
            // Get all questions
            $soal = SoalQuiz::where('quiz_id', $quiz->id)
                ->get(['id', 'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'poin', 'tingkat_kesulitan']);
        }
        if ($soal->count() === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz belum memiliki soal'
            ], 400);
        }
        // Create new quiz result
        $result = new QuizResult([
            'quiz_id' => $quiz->id,
            'peserta_id' => $user->id,
            'waktu_mulai' => now(),
            'attempt' => $attemptCount + 1
        ]);
        $result->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Quiz dimulai',
            'data' => [
                'quiz' => [
                    'id' => $quiz->id,
                    'judul_quiz' => $quiz->judul_quiz,
                    'durasi_menit' => $quiz->durasi_menit,
                    'tampilkan_hasil' => $quiz->tampilkan_hasil,
                    'attempt' => $attemptCount + 1,
                    'max_attempt' => $quiz->max_attempt,
                    'waktu_mulai' => $result->waktu_mulai,
                    'batas_waktu' => $result->waktu_mulai->addMinutes($quiz->durasi_menit)
                ],
                'result_id' => $result->id,
                'soal' => $soal
            ]
        ]);
    }
    // Submit quiz answers
    public function submitQuiz(Request $request, $resultId)
    {
        $result = QuizResult::with(['quiz.soal', 'quiz.modul.kursus'])->findOrFail($resultId);
        $user = Auth::guard('sanctum')->user();
        if (!$user->tokenCan('peserta')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya peserta yang dapat mengerjakan quiz'
            ], 403);
        }
        // Check if this result belongs to this user
        if ($result->peserta_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }
        // Check if quiz already submitted
        if ($result->waktu_selesai !== null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz sudah disubmit sebelumnya'
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'jawaban' => 'required|array',
            'jawaban.*' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        $jawaban = $request->jawaban;
        $quiz = $result->quiz;
        $soalQuiz = $quiz->soal;
        // Calculate score
        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $totalPoin = 0;
        foreach ($soalQuiz as $soal) {
            if (isset($jawaban[$soal->id]) && $jawaban[$soal->id] === $soal->jawaban_benar) {
                $jumlahBenar++;
                $totalPoin += $soal->poin;
            } else {
                $jumlahSalah++;
            }
        }
        // Calculate score based on total points
        $totalMaxPoin = $soalQuiz->sum('poin');
        $nilai = $totalMaxPoin > 0 ? ($totalPoin / $totalMaxPoin) * 100 : 0;
        // Update result
        $result->update([
            'nilai' => $nilai,
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'is_passed' => $nilai >= $quiz->passing_grade,
            'waktu_selesai' => now(),
            'durasi_pengerjaan_menit' => now()->diffInMinutes($result->waktu_mulai)
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Quiz berhasil diselesaikan',
            'data' => [
                'result' => $result,
                'pembahasan' => $quiz->tampilkan_hasil ? $soalQuiz->pluck('pembahasan', 'id') : null
            ]
        ]);
    }
}
