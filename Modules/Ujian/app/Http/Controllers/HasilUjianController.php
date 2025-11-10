<?php

namespace Modules\Ujian\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ujian\Entities\Ujian;
use Modules\Ujian\Entities\UjianResult;
use Modules\Peserta\Entities\Peserta;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HasilUjianController extends Controller
{
    /**
     * Display a listing of the results.
     */
    public function index(Request $request)
    {
        // Filter data if needed
        $filters = $request->only(['ujian_id', 'peserta_id', 'status', 'date_from', 'date_to']);

        $query = UjianResult::with(['ujian', 'peserta.user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['ujian_id'])) {
            $query->where('ujian_id', $filters['ujian_id']);
        }

        if (!empty($filters['peserta_id'])) {
            $query->where('peserta_id', $filters['peserta_id']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'passed') {
                $query->where('is_passed', true);
            } elseif ($filters['status'] === 'failed') {
                $query->where('is_passed', false);
            }
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $hasil = $query->paginate(15);

        // Get lists for filter dropdowns
        $ujians = Ujian::orderBy('judul_ujian')->get();
        $pesertas = Peserta::with('user')->orderBy('id')->get();

        return view('ujian::hasil.index', compact('hasil', 'ujians', 'pesertas', 'filters'));
    }

    /**
     * Display the specified result.
     */
    public function show($id)
    {
        $hasil = UjianResult::with(['ujian', 'peserta.user'])->findOrFail($id);

        // Parse jawaban dari JSON
        $jawaban = json_decode($hasil->jawaban, true);

        // Get soal
        $soalUjians = $hasil->ujian->soalUjians;

        // Check if the person viewing is the instructor of the course
        $isInstructor = Auth::user()->isInstructor() &&
            Auth::user()->instructor->kursus->contains($hasil->ujian->kursus_id);

        return view('ujian::hasil.show', compact('hasil', 'jawaban', 'soalUjians', 'isInstructor'));
    }

    /**
     * Display overview of a peserta's results
     */
    public function pesertaOverview($pesertaId)
    {
        $peserta = Peserta::with('user')->findOrFail($pesertaId);

        $hasil = UjianResult::with('ujian')
            ->where('peserta_id', $pesertaId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get stats
        $totalUjian = $hasil->total();
        $passedCount = UjianResult::where('peserta_id', $pesertaId)
            ->where('is_passed', true)
            ->count();
        $avgScore = UjianResult::where('peserta_id', $pesertaId)->avg('nilai');

        return view('ujian::hasil.peserta-overview', compact(
            'peserta',
            'hasil',
            'totalUjian',
            'passedCount',
            'avgScore'
        ));
    }

    /**
     * Display overview of an ujian's results
     */
    public function ujianOverview($ujianId)
    {
        $ujian = Ujian::with('kursus')->findOrFail($ujianId);

        $hasil = UjianResult::with('peserta.user')
            ->where('ujian_id', $ujianId)
            ->orderBy('nilai', 'desc')
            ->paginate(15);

        // Get stats
        $participantCount = $hasil->total();
        $passedCount = UjianResult::where('ujian_id', $ujianId)
            ->where('is_passed', true)
            ->count();
        $avgScore = UjianResult::where('ujian_id', $ujianId)->avg('nilai');

        // Get score distribution
        $scoreRanges = [
            '91-100' => 0,
            '81-90' => 0,
            '71-80' => 0,
            '61-70' => 0,
            '51-60' => 0,
            '0-50' => 0,
        ];

        $allScores = UjianResult::where('ujian_id', $ujianId)->pluck('nilai');

        foreach ($allScores as $score) {
            if ($score > 90) {
                $scoreRanges['91-100']++;
            } elseif ($score > 80) {
                $scoreRanges['81-90']++;
            } elseif ($score > 70) {
                $scoreRanges['71-80']++;
            } elseif ($score > 60) {
                $scoreRanges['61-70']++;
            } elseif ($score > 50) {
                $scoreRanges['51-60']++;
            } else {
                $scoreRanges['0-50']++;
            }
        }

        return view('ujian::hasil.ujian-overview', compact(
            'ujian',
            'hasil',
            'participantCount',
            'passedCount',
            'avgScore',
            'scoreRanges'
        ));
    }
}
