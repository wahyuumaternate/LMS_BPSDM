<?php

namespace Modules\Quiz\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Entities\QuizResult;
use Modules\Peserta\Entities\Peserta;
use Illuminate\Support\Facades\DB;

class HasilQuizController extends Controller
{
    /**
     * Display a listing of all quiz results.
     * Shows only the best attempt for each peserta per quiz.
     *
     * @param Request $request
     * @param int|null $quizId
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $quizId = null)
    {
        // Subquery to get the best attempt (highest score) for each peserta per quiz
        $bestAttempts = QuizResult::select('peserta_id', 'quiz_id', DB::raw('MAX(nilai) as max_nilai'))
            ->groupBy('peserta_id', 'quiz_id');

        // Filter by quiz_id from route parameter or query string
        $selectedQuizId = $quizId ?? $request->input('quiz_id');
        if ($selectedQuizId) {
            $bestAttempts->where('quiz_id', $selectedQuizId);
        }

        // Filter by peserta_id
        if ($request->has('peserta_id') && $request->peserta_id != '') {
            $bestAttempts->where('peserta_id', $request->peserta_id);
        }

        // Join to get the full record of the best attempt
        $query = QuizResult::with(['quiz.modul', 'peserta'])
            ->joinSub($bestAttempts, 'best_attempts', function ($join) {
                $join->on('quiz_results.peserta_id', '=', 'best_attempts.peserta_id')
                    ->on('quiz_results.quiz_id', '=', 'best_attempts.quiz_id')
                    ->on('quiz_results.nilai', '=', 'best_attempts.max_nilai');
            });

        // Filter by is_passed
        if ($request->has('is_passed') && $request->is_passed !== '') {
            $query->where('quiz_results.is_passed', $request->boolean('is_passed'));
        }

        $results = $query->orderBy('quiz_results.created_at', 'desc')
            ->paginate(20);

        // Get all results for statistics (including all attempts)
        $allResultsQuery = QuizResult::query();
        
        if ($selectedQuizId) {
            $allResultsQuery->where('quiz_id', $selectedQuizId);
        }
        
        if ($request->has('peserta_id') && $request->peserta_id != '') {
            $allResultsQuery->where('peserta_id', $request->peserta_id);
        }
        
        if ($request->has('is_passed') && $request->is_passed !== '') {
            $allResultsQuery->where('is_passed', $request->boolean('is_passed'));
        }
        
        $allResults = $allResultsQuery->get();

        // Get quiz detail if quiz_id is provided
        $selectedQuiz = $selectedQuizId ? Quiz::find($selectedQuizId) : null;

        $quizzes = Quiz::orderBy('judul_quiz')->get();
        $pesertas = Peserta::orderBy('nama_lengkap')->get();

        return view('quiz::hasil-quiz.index', compact('results', 'quizzes', 'pesertas', 'allResults', 'selectedQuiz', 'selectedQuizId'));
    }

    /**
     * Display the specified result with all attempts.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $result = QuizResult::with([
            'quiz.modul',
            'peserta',
            'quiz.soalQuiz.options'
        ])->findOrFail($id);

        // Get all attempts for this peserta and quiz
        $allAttempts = QuizResult::where('peserta_id', $result->peserta_id)
            ->where('quiz_id', $result->quiz_id)
            ->orderBy('attempt', 'asc')
            ->get();

        // Decode saved answers for current result
        // Check if jawaban is already an array (Laravel auto-casts) or needs decoding
        if (is_array($result->jawaban)) {
            $jawaban = $result->jawaban;
        } else {
            $jawaban = json_decode($result->jawaban, true) ?: [];
        }

        // Calculate statistics for all attempts
        $attemptStats = [
            'total_attempts' => $allAttempts->count(),
            'best_score' => $allAttempts->max('nilai'),
            'average_score' => $allAttempts->avg('nilai'),
            'first_attempt_date' => $allAttempts->first()->created_at,
            'last_attempt_date' => $allAttempts->last()->created_at,
            'passed_attempts' => $allAttempts->where('is_passed', true)->count(),
        ];

        return view('quiz::hasil-quiz.show', compact('result', 'jawaban', 'allAttempts', 'attemptStats'));
    }

    /**
     * Display summary of results for a specific quiz.
     *
     * @param int $quizId
     * @return \Illuminate\View\View
     */
    public function quizOverview($quizId)
    {
        $quiz = Quiz::with(['modul'])->findOrFail($quizId);

        // Get best attempt for each peserta
        $bestAttempts = QuizResult::select('peserta_id', 'quiz_id', DB::raw('MAX(nilai) as max_nilai'))
            ->where('quiz_id', $quizId)
            ->groupBy('peserta_id', 'quiz_id');

        $results = QuizResult::with(['peserta'])
            ->joinSub($bestAttempts, 'best_attempts', function ($join) {
                $join->on('quiz_results.peserta_id', '=', 'best_attempts.peserta_id')
                    ->on('quiz_results.quiz_id', '=', 'best_attempts.quiz_id')
                    ->on('quiz_results.nilai', '=', 'best_attempts.max_nilai');
            })
            ->orderBy('quiz_results.created_at', 'desc')
            ->paginate(20);

        // Gather statistics from all attempts
        $statsQuery = QuizResult::where('quiz_id', $quizId);
        $stats = [
            'total_attempts' => $statsQuery->count(),
            'unique_participants' => $statsQuery->distinct('peserta_id')->count('peserta_id'),
            'average_score' => $statsQuery->avg('nilai'),
            'pass_rate' => $statsQuery->count() > 0
                ? ($statsQuery->where('is_passed', true)->count() / $statsQuery->count()) * 100
                : 0,
            'highest_score' => $statsQuery->max('nilai'),
            'lowest_score' => $statsQuery->min('nilai'),
            'average_duration' => $statsQuery->avg('durasi_pengerjaan_menit'),
        ];

        // Get distribution of scores (based on best attempts)
        $scoreDistribution = DB::table('quiz_results')
            ->select(
                DB::raw('
                CASE 
                    WHEN nilai BETWEEN 0 AND 20 THEN "0-20"
                    WHEN nilai BETWEEN 21 AND 40 THEN "21-40"
                    WHEN nilai BETWEEN 41 AND 60 THEN "41-60"
                    WHEN nilai BETWEEN 61 AND 80 THEN "61-80"
                    ELSE "81-100" 
                END as range'),
                DB::raw('count(*) as count')
            )
            ->whereIn('id', function ($query) use ($quizId) {
                $query->select(DB::raw('MIN(id)'))
                    ->from('quiz_results')
                    ->where('quiz_id', $quizId)
                    ->groupBy('peserta_id', 'nilai')
                    ->havingRaw('nilai = MAX(nilai)');
            })
            ->groupBy('range')
            ->orderBy('range')
            ->get();

        return view('quiz::hasil-quiz.quiz-overview', compact('quiz', 'results', 'stats', 'scoreDistribution'));
    }

    /**
     * Display summary of results for a specific peserta.
     *
     * @param int $pesertaId
     * @return \Illuminate\View\View
     */
    public function pesertaOverview($pesertaId)
    {
        $peserta = Peserta::findOrFail($pesertaId);

        // Get best attempt for each quiz
        $bestAttempts = QuizResult::select('peserta_id', 'quiz_id', DB::raw('MAX(nilai) as max_nilai'))
            ->where('peserta_id', $pesertaId)
            ->groupBy('peserta_id', 'quiz_id');

        $results = QuizResult::with(['quiz.modul'])
            ->joinSub($bestAttempts, 'best_attempts', function ($join) {
                $join->on('quiz_results.peserta_id', '=', 'best_attempts.peserta_id')
                    ->on('quiz_results.quiz_id', '=', 'best_attempts.quiz_id')
                    ->on('quiz_results.nilai', '=', 'best_attempts.max_nilai');
            })
            ->orderBy('quiz_results.created_at', 'desc')
            ->paginate(20);

        // Gather statistics from all attempts
        $statsQuery = QuizResult::where('peserta_id', $pesertaId);
        $stats = [
            'total_quizzes' => $statsQuery->distinct('quiz_id')->count('quiz_id'),
            'total_attempts' => $statsQuery->count(),
            'average_score' => $statsQuery->avg('nilai'),
            'pass_rate' => $statsQuery->count() > 0
                ? ($statsQuery->where('is_passed', true)->count() / $statsQuery->count()) * 100
                : 0,
            'highest_score' => $statsQuery->max('nilai'),
            'latest_attempt' => $statsQuery->max('created_at'),
        ];

        // Get top performing quizzes (based on best attempt)
        $topQuizzes = QuizResult::with('quiz')
            ->select('quiz_id', DB::raw('MAX(nilai) as max_score'))
            ->where('peserta_id', $pesertaId)
            ->groupBy('quiz_id')
            ->orderBy('max_score', 'desc')
            ->take(5)
            ->get();

        return view('quiz::hasil-quiz.peserta-overview', compact('peserta', 'results', 'stats', 'topQuizzes'));
    }

    /**
     * Export quiz results to CSV/Excel.
     * Exports only the best attempt for each peserta per quiz.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // Subquery to get the best attempt for each peserta per quiz
        $bestAttempts = QuizResult::select('peserta_id', 'quiz_id', DB::raw('MAX(nilai) as max_nilai'))
            ->groupBy('peserta_id', 'quiz_id');

        // Apply filters
        if ($request->has('quiz_id') && $request->quiz_id != '') {
            $bestAttempts->where('quiz_id', $request->quiz_id);
        }

        if ($request->has('peserta_id') && $request->peserta_id != '') {
            $bestAttempts->where('peserta_id', $request->peserta_id);
        }

        $query = QuizResult::with(['quiz.modul', 'peserta'])
            ->joinSub($bestAttempts, 'best_attempts', function ($join) {
                $join->on('quiz_results.peserta_id', '=', 'best_attempts.peserta_id')
                    ->on('quiz_results.quiz_id', '=', 'best_attempts.quiz_id')
                    ->on('quiz_results.nilai', '=', 'best_attempts.max_nilai');
            });

        $results = $query->orderBy('quiz_results.created_at', 'desc')->get();

        // Create CSV data
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quiz_results_export_' . date('Y-m-d') . '.csv"',
        ];

        $columns = [
            'ID',
            'Quiz',
            'Modul',
            'Peserta',
            'NIP',
            'Nilai Terbaik',
            'Total Attempt',
            'Jumlah Benar',
            'Jumlah Salah',
            'Status',
            'Durasi (Menit)',
            'Tanggal Attempt Terbaik'
        ];

        $callback = function () use ($results, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($results as $result) {
                // Get total attempts for this peserta and quiz
                $totalAttempts = QuizResult::where('peserta_id', $result->peserta_id)
                    ->where('quiz_id', $result->quiz_id)
                    ->count();

                $row = [
                    $result->id,
                    $result->quiz->judul_quiz ?? 'N/A',
                    $result->quiz->modul->nama_modul ?? 'N/A',
                    $result->peserta->nama_lengkap ?? 'N/A',
                    $result->peserta->nip ?? 'N/A',
                    $result->nilai,
                    $totalAttempts,
                    $result->jumlah_benar,
                    $result->jumlah_salah,
                    $result->is_passed ? 'Lulus' : 'Tidak Lulus',
                    $result->durasi_pengerjaan_menit,
                    $result->created_at->format('Y-m-d H:i:s')
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Remove the specified quiz result from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = QuizResult::findOrFail($id);
        $result->delete();

        return redirect()->route('hasil-quiz.index')
            ->with('success', 'Hasil quiz berhasil dihapus');
    }
}