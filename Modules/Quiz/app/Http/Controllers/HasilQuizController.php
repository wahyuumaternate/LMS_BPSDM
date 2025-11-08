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
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = QuizResult::with(['quiz.modul', 'peserta']);

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

        $results = $query->orderBy('created_at', 'desc')->paginate(20);
        $quizzes = Quiz::orderBy('judul_quiz')->get();
        $pesertas = Peserta::orderBy('nama_lengkap')->get();

        return view('quiz::hasil-quiz.index', compact('results', 'quizzes', 'pesertas'));
    }

    /**
     * Display the specified result.
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

        // Decode saved answers
        $jawaban = json_decode($result->jawaban, true) ?: [];

        return view('quiz::hasil-quiz.show', compact('result', 'jawaban'));
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

        $results = QuizResult::with(['peserta'])
            ->where('quiz_id', $quizId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Gather statistics
        $statsQuery = QuizResult::where('quiz_id', $quizId);
        $stats = [
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
            ->where('quiz_id', $quizId)
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

        $results = QuizResult::with(['quiz.modul'])
            ->where('peserta_id', $pesertaId)
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
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $query = QuizResult::with(['quiz.modul', 'peserta']);

        // Apply filters
        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        if ($request->has('peserta_id')) {
            $query->where('peserta_id', $request->peserta_id);
        }

        $results = $query->orderBy('created_at', 'desc')->get();

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
            'Attempt',
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
                $row = [
                    $result->id,
                    $result->quiz->judul_quiz ?? 'N/A',
                    $result->quiz->modul->nama_modul ?? 'N/A',
                    $result->peserta->nama_lengkap ?? 'N/A',
                    $result->peserta->nip ?? 'N/A',
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
