<?php

namespace Modules\Materi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Materi\Entities\ProgresMateri;
use Modules\Materi\Entities\Materi;
use Modules\Peserta\Entities\Peserta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProgresMateriController extends Controller
{
    /**
     * Display a listing of progress records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        try {
            $query = ProgresMateri::with(['materi', 'peserta']);

            // Filter by peserta_id
            if ($request->has('peserta_id')) {
                $query->where('peserta_id', $request->peserta_id);
            }

            // Filter by materi_id
            if ($request->has('materi_id')) {
                $query->where('materi_id', $request->materi_id);
            }

            // Filter by completion status
            if ($request->has('is_selesai')) {
                $query->where('is_selesai', $request->boolean('is_selesai'));
            }

            // Get peserta and materi for filter dropdowns
            $pesertas = Peserta::orderBy('nama_lengkap')->get();
            $materis = Materi::orderBy('judul_materi')->get();

            $progresMateri = $query->paginate(15);

            return view('materi::progres-materi-create', compact('progresMateri', 'pesertas', 'materis'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error fetching progress records: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new progress record.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $pesertas = Peserta::orderBy('nama')->get();
        $materis = Materi::orderBy('judul_materi')->get();

        return view('materi::progres.create', compact('pesertas', 'materis'));
    }

    /**
     * Store a newly created progress record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'peserta_id' => 'required|exists:pesertas,id',
                'materi_id' => 'required|exists:materis,id',
                'is_selesai' => 'nullable|boolean',
                'progress_persen' => 'nullable|integer|min:0|max:100',
                'durasi_belajar_menit' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check if progress already exists
            $existingProgres = ProgresMateri::where('peserta_id', $request->peserta_id)
                ->where('materi_id', $request->materi_id)
                ->first();

            if ($existingProgres) {
                return redirect()->back()
                    ->with('warning', 'Progress sudah ada untuk peserta dan materi ini.')
                    ->withInput();
            }

            $data = $request->except(['_token']);

            // Handle boolean fields
            $data['is_selesai'] = $request->has('is_selesai');

            // Set default values
            if (!isset($data['progress_persen'])) {
                $data['progress_persen'] = 0;
            }

            $data['tanggal_mulai'] = now();

            // If marked as complete, set tanggal_selesai and progress_persen
            if ($data['is_selesai']) {
                $data['tanggal_selesai'] = now();
                $data['progress_persen'] = 100;
            }

            ProgresMateri::create($data);

            return redirect()->route('progres-materi.index')
                ->with('success', 'Progress berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating progress record: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified progress record.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        try {
            $progresMateri = ProgresMateri::with(['materi', 'peserta'])->findOrFail($id);
            return view('materi::progres.show', compact('progresMateri'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Progress record not found');
        } catch (\Exception $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Error retrieving progress record: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified progress record.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        try {
            $progresMateri = ProgresMateri::findOrFail($id);
            $pesertas = Peserta::orderBy('nama')->get();
            $materis = Materi::orderBy('judul_materi')->get();

            return view('materi::progres.edit', compact('progresMateri', 'pesertas', 'materis'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Progress record not found');
        } catch (\Exception $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Error retrieving progress record: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified progress record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $progresMateri = ProgresMateri::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'peserta_id' => 'required|exists:pesertas,id',
                'materi_id' => 'required|exists:materis,id',
                'progress_persen' => 'nullable|integer|min:0|max:100',
                'durasi_belajar_menit' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except(['_token', '_method']);

            // Handle boolean fields
            $data['is_selesai'] = $request->has('is_selesai');

            // If marked as complete and wasn't complete before, set tanggal_selesai and progress_persen
            if ($data['is_selesai'] && !$progresMateri->is_selesai) {
                $data['tanggal_selesai'] = now();
                $data['progress_persen'] = 100;
            }

            // If marked as incomplete and was complete before, unset tanggal_selesai
            if (!$data['is_selesai'] && $progresMateri->is_selesai) {
                $data['tanggal_selesai'] = null;
            }

            $progresMateri->update($data);

            return redirect()->route('progres-materi.show', $id)
                ->with('success', 'Progress berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Progress record not found');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating progress record: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified progress record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $progresMateri = ProgresMateri::findOrFail($id);
            $progresMateri->delete();

            return redirect()->route('progres-materi.index')
                ->with('success', 'Progress berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Progress record not found');
        } catch (\Exception $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Error deleting progress record: ' . $e->getMessage());
        }
    }

    /**
     * Show the progress overview for a specific peserta.
     *
     * @param  int  $pesertaId
     * @return \Illuminate\Contracts\View\View
     */
    public function pesertaOverview($pesertaId)
    {
        try {
            $peserta = Peserta::findOrFail($pesertaId);
            $progresMateri = ProgresMateri::with('materi')
                ->where('peserta_id', $pesertaId)
                ->get();

            // Calculate overall progress
            $totalMateri = Materi::count();
            $completedMateri = $progresMateri->where('is_selesai', true)->count();
            $overallProgress = $totalMateri > 0 ? round(($completedMateri / $totalMateri) * 100) : 0;

            // Group by module
            $progresPerModul = [];
            foreach ($progresMateri as $progres) {
                $modulId = $progres->materi->modul_id;
                $modulNama = $progres->materi->modul->nama_modul ?? 'Unknown';

                if (!isset($progresPerModul[$modulId])) {
                    $progresPerModul[$modulId] = [
                        'nama' => $modulNama,
                        'total' => 0,
                        'completed' => 0,
                        'progress' => 0
                    ];
                }

                $progresPerModul[$modulId]['total']++;
                if ($progres->is_selesai) {
                    $progresPerModul[$modulId]['completed']++;
                }
            }

            // Calculate progress per module
            foreach ($progresPerModul as &$modul) {
                $modul['progress'] = $modul['total'] > 0
                    ? round(($modul['completed'] / $modul['total']) * 100)
                    : 0;
            }

            return view('materi::progres.peserta-overview', compact(
                'peserta',
                'progresMateri',
                'overallProgress',
                'totalMateri',
                'completedMateri',
                'progresPerModul'
            ));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Peserta not found');
        } catch (\Exception $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Error retrieving peserta overview: ' . $e->getMessage());
        }
    }

    /**
     * Show the progress overview for a specific materi.
     *
     * @param  int  $materiId
     * @return \Illuminate\Contracts\View\View
     */
    public function materiOverview($materiId)
    {
        try {
            $materi = Materi::with('modul')->findOrFail($materiId);
            $progresMateri = ProgresMateri::with('peserta')
                ->where('materi_id', $materiId)
                ->get();

            // Calculate completion statistics
            $totalPeserta = Peserta::count();
            $startedCount = $progresMateri->count();
            $completedCount = $progresMateri->where('is_selesai', true)->count();

            $startedPercentage = $totalPeserta > 0 ? round(($startedCount / $totalPeserta) * 100) : 0;
            $completedPercentage = $totalPeserta > 0 ? round(($completedCount / $totalPeserta) * 100) : 0;

            // Calculate average duration for completed
            $avgDuration = $progresMateri->where('is_selesai', true)
                ->where('durasi_belajar_menit', '>', 0)
                ->avg('durasi_belajar_menit');

            return view('materi::progres.materi-overview', compact(
                'materi',
                'progresMateri',
                'totalPeserta',
                'startedCount',
                'completedCount',
                'startedPercentage',
                'completedPercentage',
                'avgDuration'
            ));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Materi not found');
        } catch (\Exception $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Error retrieving materi overview: ' . $e->getMessage());
        }
    }

    /**
     * Show dashboard with overall progress statistics.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function dashboard()
    {
        try {
            // Get counts
            $totalMateri = Materi::count();
            $totalPeserta = Peserta::count();
            $totalProgres = ProgresMateri::count();
            $totalCompleted = ProgresMateri::where('is_selesai', true)->count();

            // Calculate overall completion rate
            $completionRate = $totalProgres > 0 ? round(($totalCompleted / $totalProgres) * 100) : 0;

            // Get top 5 most popular materials
            $popularMateri = Materi::withCount(['progresMateri'])
                ->orderBy('progres_materi_count', 'desc')
                ->take(5)
                ->get();

            // Get top 5 materials with highest completion rates
            $highCompletionMateri = Materi::withCount([
                'progresMateri',
                'progresMateri as completed_count' => function ($query) {
                    $query->where('is_selesai', true);
                }
            ])
                ->having('progres_materi_count', '>', 0)
                ->orderByRaw('completed_count / progres_materi_count DESC')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    $item->completion_rate = $item->progres_materi_count > 0
                        ? round(($item->completed_count / $item->progres_materi_count) * 100)
                        : 0;
                    return $item;
                });

            // Get recent activity
            $recentActivity = ProgresMateri::with(['peserta', 'materi'])
                ->latest('updated_at')
                ->take(10)
                ->get();

            return view('materi::progres.dashboard', compact(
                'totalMateri',
                'totalPeserta',
                'totalProgres',
                'totalCompleted',
                'completionRate',
                'popularMateri',
                'highCompletionMateri',
                'recentActivity'
            ));
        } catch (\Exception $e) {
            return redirect()->route('progres-materi.index')
                ->with('error', 'Error loading dashboard: ' . $e->getMessage());
        }
    }
}
