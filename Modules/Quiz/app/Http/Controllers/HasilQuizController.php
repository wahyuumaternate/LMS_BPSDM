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
     * Shows only the latest attempt for each peserta-quiz combination.
     */
    public function index(Request $request)
    {
        // Get latest attempt for each peserta-quiz combination
        $latestResults = QuizResult::select('quiz_id', 'peserta_id', DB::raw('MAX(id) as latest_id'))
            ->groupBy('quiz_id', 'peserta_id');

        // Apply filters before grouping
        if ($request->filled('quiz_id')) {
            $latestResults->where('quiz_id', $request->quiz_id);
        }

        if ($request->filled('peserta_id')) {
            $latestResults->where('peserta_id', $request->peserta_id);
        }

        if ($request->filled('is_passed')) {
            $latestResults->where('is_passed', $request->boolean('is_passed'));
        }

        // Get the IDs of latest results
        $latestIds = $latestResults->pluck('latest_id');

        // Fetch full records for those IDs
        $query = QuizResult::with(['quiz.modul', 'peserta'])
            ->whereIn('id', $latestIds);

        // Apply is_passed filter to final results if needed
        if ($request->filled('is_passed')) {
            $query->where('is_passed', $request->boolean('is_passed'));
        }

        $results = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get all results for statistics (not just latest)
        $allResultsQuery = QuizResult::query();
        
        if ($request->filled('quiz_id')) {
            $allResultsQuery->where('quiz_id', $request->quiz_id);
        }

        if ($request->filled('peserta_id')) {
            $allResultsQuery->where('peserta_id', $request->peserta_id);
        }

        if ($request->filled('is_passed')) {
            $allResultsQuery->where('is_passed', $request->boolean('is_passed'));
        }

        $allResults = $allResultsQuery->get();

        $quizzes = Quiz::orderBy('judul_quiz')->get();
        $pesertas = Peserta::orderBy('nama_lengkap')->get();

        return view('quiz::hasil-quiz.index', compact('results', 'quizzes', 'pesertas', 'allResults'));
    }

    /**
     * Display the specified result with all attempts.
     * Shows all attempts made by the peserta for this specific quiz.
     */
    public function show($id)
    {
        $result = QuizResult::with([
            'quiz.modul',
            'peserta',
            'quiz.soalQuiz.options'
        ])->findOrFail($id);

        // Get all attempts for this peserta-quiz combination
        $allAttempts = QuizResult::with(['quiz.modul', 'peserta'])
            ->where('quiz_id', $result->quiz_id)
            ->where('peserta_id', $result->peserta_id)
            ->orderBy('attempt', 'asc')
            ->get();

        // Calculate statistics for this peserta's attempts
        $stats = [
            'total_attempts' => $allAttempts->count(),
            'best_score' => $allAttempts->max('nilai'),
            'worst_score' => $allAttempts->min('nilai'),
            'average_score' => $allAttempts->avg('nilai'),
            'passed_attempts' => $allAttempts->where('is_passed', true)->count(),
            'failed_attempts' => $allAttempts->where('is_passed', false)->count(),
            'average_duration' => $allAttempts->avg('durasi_pengerjaan_menit'),
            'first_attempt_date' => $allAttempts->first()->created_at ?? null,
            'latest_attempt_date' => $allAttempts->last()->created_at ?? null,
        ];

        // Decode saved answers for current result
        $jawaban = json_decode($result->jawaban, true) ?: [];

        return view('quiz::hasil-quiz.show', compact('result', 'jawaban', 'allAttempts', 'stats'));
    }

    /**
     * Display summary of results for a specific quiz.
     */
    public function quizOverview($quizId)
    {
        $quiz = Quiz::with(['modul'])->findOrFail($quizId);

        // Get latest attempt for each peserta
        $latestResults = QuizResult::select('peserta_id', DB::raw('MAX(id) as latest_id'))
            ->where('quiz_id', $quizId)
            ->groupBy('peserta_id')
            ->pluck('latest_id');

        $results = QuizResult::with(['peserta'])
            ->whereIn('id', $latestResults)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Gather statistics (using all attempts)
        $statsQuery = QuizResult::where('quiz_id', $quizId);
        $stats = [
            'total_participants' => QuizResult::where('quiz_id', $quizId)
                ->distinct('peserta_id')
                ->count('peserta_id'),
            'total_attempts' => $statsQuery->count(),
            'average_score' => $statsQuery->avg('nilai'),
            'pass_rate' => $statsQuery->count() > 0
                ? ($statsQuery->where('is_passed', true)->count() / $statsQuery->count()) * 100
                : 0,
            'highest_score' => $statsQuery->max('nilai'),
            'lowest_score' => $statsQuery->min('nilai'),
            'average_duration' => $statsQuery->avg('durasi_pengerjaan_menit'),
        ];

        // Get distribution of scores
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
            ->whereIn('id', $latestResults)
            ->groupBy('range')
            ->orderBy('range')
            ->get();

        return view('quiz::hasil-quiz.quiz-overview', compact('quiz', 'results', 'stats', 'scoreDistribution'));
    }

    /**
     * Display summary of results for a specific peserta.
     */
    public function pesertaOverview($pesertaId)
    {
        $peserta = Peserta::findOrFail($pesertaId);

        // Get latest attempt for each quiz
        $latestResults = QuizResult::select('quiz_id', DB::raw('MAX(id) as latest_id'))
            ->where('peserta_id', $pesertaId)
            ->groupBy('quiz_id')
            ->pluck('latest_id');

        $results = QuizResult::with(['quiz.modul'])
            ->whereIn('id', $latestResults)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Gather statistics
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

        // Get top performing quizzes
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
     */
    public function export(Request $request)
    {
        // Get latest attempt for each peserta-quiz combination
        $latestResults = QuizResult::select('quiz_id', 'peserta_id', DB::raw('MAX(id) as latest_id'))
            ->groupBy('quiz_id', 'peserta_id');

        // Apply filters
        if ($request->filled('quiz_id')) {
            $latestResults->where('quiz_id', $request->quiz_id);
        }

        if ($request->filled('peserta_id')) {
            $latestResults->where('peserta_id', $request->peserta_id);
        }

        if ($request->filled('is_passed')) {
            $latestResults->where('is_passed', $request->boolean('is_passed'));
        }

        $latestIds = $latestResults->pluck('latest_id');

        $results = QuizResult::with(['quiz.modul', 'peserta'])
            ->whereIn('id', $latestIds)
            ->orderBy('created_at', 'desc')
            ->get();

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
            'Total Attempts',
            'Latest Attempt',
            'Nilai',
            'Jumlah Benar',
            'Jumlah Salah',
            'Status',
            'Durasi (Menit)',
            'Waktu Mulai',
            'Waktu Selesai',
            'Tanggal'
        ];

        $callback = function () use ($results, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($results as $result) {
                $totalAttempts = QuizResult::where('quiz_id', $result->quiz_id)
                    ->where('peserta_id', $result->peserta_id)
                    ->count();

                $row = [
                    $result->id,
                    $result->quiz->judul_quiz ?? 'N/A',
                    $result->quiz->modul->nama_modul ?? 'N/A',
                    $result->peserta->nama_lengkap ?? 'N/A',
                    $result->peserta->nip ?? 'N/A',
                    $totalAttempts,
                    $result->attempt,
                    $result->nilai,
                    $result->jumlah_benar,
                    $result->jumlah_salah,
                    $result->is_passed ? 'Lulus' : 'Tidak Lulus',
                    $result->durasi_pengerjaan_menit,
                    $result->waktu_mulai,
                    $result->waktu_selesai,
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
     */
    public function destroy($id)
    {
        $result = QuizResult::findOrFail($id);
        $result->delete();

        return redirect()->route('hasil-quiz.index')
            ->with('success', 'Hasil quiz berhasil dihapus');
    }
}