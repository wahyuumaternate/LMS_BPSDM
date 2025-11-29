<?php

namespace Modules\AdminInstruktur\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Peserta\Entities\Peserta;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\Kursus\Entities\Kursus;
use Modules\OPD\Entities\OPD;
use Modules\Modul\Entities\Modul;
use Modules\Materi\Entities\Materi;
use Modules\Tugas\Entities\TugasSubmission;
use Modules\Quiz\Entities\QuizResult;
use Modules\Ujian\Entities\UjianResult;

class DashboardController extends Controller
{
    /**
     * Display role-based dashboard
     */
    public function index()
    {
        // Get authenticated user
        $user = Auth::guard('admin_instruktur')->user();

        // Check user role and redirect to appropriate dashboard
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return $this->adminDashboard();
        } else {
            return $this->instrukturDashboard();
        }
    }

    /**
     * Admin/Super Admin Dashboard
     */
    private function adminDashboard()
    {
        // Get statistics
        $stats = $this->getAdminStatistics();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get chart data
        $chartData = $this->getChartData();
        
        // Get recent courses
        $recentKursus = $this->getRecentKursus();

        return view('admininstruktur::admin.dashboard', compact(
            'stats',
            'recentActivities',
            'chartData',
            'recentKursus'
        ));
    }

    /**
     * Instruktur Dashboard
     */
    private function instrukturDashboard()
    {
        $user = Auth::guard('admin_instruktur')->user();
        
        // Get instruktur-specific statistics
        $stats = $this->getInstrukturStatistics($user->id);
        
        // Get instruktur's courses
        $myCourses = Kursus::where('admin_instruktur_id', $user->id)
            ->with(['jenisKursus.kategoriKursus'])
            ->withCount(['peserta' => function($query) {
                $query->whereIn('pendaftaran_kursus.status', ['disetujui', 'aktif', 'selesai']);
            }])
            ->latest()
            ->take(5)
            ->get();
        
        // Get recent submissions yang belum dinilai
        $recentSubmissions = $this->getRecentSubmissions($user->id);
        
        // Get chart data untuk progress kursus
        $chartData = $this->getInstrukturChartData($user->id);

        return view('admininstruktur::instruktur.dashboard', compact(
            'stats',
            'myCourses',
            'recentSubmissions',
            'chartData'
        ));
    }

    /**
     * Get admin statistics
     */
    private function getAdminStatistics()
    {
        // Total Peserta
        $totalPeserta = Peserta::count();
        $newPesertaThisMonth = Peserta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $pesertaLastMonth = Peserta::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $pesertaGrowth = $pesertaLastMonth > 0 
            ? round((($newPesertaThisMonth - $pesertaLastMonth) / $pesertaLastMonth) * 100, 1)
            : 0;

        // Total Instruktur
        $totalInstruktur = AdminInstruktur::where('role', 'instruktur')->count();
        $newInstrukturThisMonth = AdminInstruktur::where('role', 'instruktur')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $instrukturLastMonth = AdminInstruktur::where('role', 'instruktur')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $instrukturGrowth = $instrukturLastMonth > 0 
            ? round((($newInstrukturThisMonth - $instrukturLastMonth) / $instrukturLastMonth) * 100, 1)
            : 0;

        // Total Admin & Super Admin
        $totalAdmin = AdminInstruktur::whereIn('role', ['admin', 'super_admin'])->count();

        // Total OPD
        $totalOPD = OPD::count();

        // Kursus statistics
        $totalKursus = Kursus::count();
        $kursusAktif = Kursus::where('status', 'aktif')->count();
        $kursusPersiapan = Kursus::where('status', 'draft')->count();
        
        // Kursus growth calculation
        $kursusThisMonth = Kursus::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $kursusLastMonth = Kursus::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $kursusGrowth = $kursusLastMonth > 0
            ? round((($kursusThisMonth - $kursusLastMonth) / $kursusLastMonth) * 100, 1)
            : 0;

        // Peserta by OPD (top 5)
        $pesertaByOPD = Peserta::select('opd_id', DB::raw('count(*) as total'))
            ->with('opd:id,nama_opd')
            ->groupBy('opd_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Peserta by status kepegawaian
        $pesertaByStatus = Peserta::select('status_kepegawaian', DB::raw('count(*) as total'))
            ->groupBy('status_kepegawaian')
            ->get();

        return [
            'totalPeserta' => $totalPeserta,
            'pesertaGrowth' => $pesertaGrowth,
            'newPesertaThisMonth' => $newPesertaThisMonth,
            
            'totalInstruktur' => $totalInstruktur,
            'instrukturGrowth' => $instrukturGrowth,
            'newInstrukturThisMonth' => $newInstrukturThisMonth,
            
            'totalAdmin' => $totalAdmin,
            'totalOPD' => $totalOPD,
            
            'totalKursus' => $totalKursus,
            'kursusAktif' => $kursusAktif,
            'kursusPersiapan' => $kursusPersiapan,
            'kursusGrowth' => $kursusGrowth,
            
            'pesertaByOPD' => $pesertaByOPD,
            'pesertaByStatus' => $pesertaByStatus,
        ];
    }

    /**
     * Get instruktur statistics
     */
    private function getInstrukturStatistics($instrukturId)
    {
        // Total Kursus milik instruktur
        $totalKursus = Kursus::where('admin_instruktur_id', $instrukturId)->count();
        
        // Kursus Aktif
        $kursusAktif = Kursus::where('admin_instruktur_id', $instrukturId)
            ->where('status', 'aktif')
            ->count();
        
        // Kursus Draft
        $kursusDraft = Kursus::where('admin_instruktur_id', $instrukturId)
            ->where('status', 'draft')
            ->count();
        
        // Total Peserta dari semua kursus instruktur (via pendaftaran_kursus)
        $totalPeserta = DB::table('pendaftaran_kursus')
            ->join('kursus', 'pendaftaran_kursus.kursus_id', '=', 'kursus.id')
            ->where('kursus.admin_instruktur_id', $instrukturId)
            ->whereIn('pendaftaran_kursus.status', ['disetujui', 'aktif', 'selesai'])
            ->distinct('pendaftaran_kursus.peserta_id')
            ->count('pendaftaran_kursus.peserta_id');
        
        // Tugas yang belum dinilai menggunakan Eloquent
        $tugasBelumDinilai = TugasSubmission::whereHas('tugas.modul.kursus', function($query) use ($instrukturId) {
                $query->where('admin_instruktur_id', $instrukturId);
            })
            ->where('status', 'submitted')
            ->whereNull('nilai')
            ->count();

        // Total Modul
        $totalModul = Modul::whereHas('kursus', function($query) use ($instrukturId) {
                $query->where('admin_instruktur_id', $instrukturId);
            })
            ->count();

        // Total Materi
        $totalMateri = Materi::whereHas('modul.kursus', function($query) use ($instrukturId) {
                $query->where('admin_instruktur_id', $instrukturId);
            })
            ->count();

        return [
            'totalKursus' => $totalKursus,
            'kursusAktif' => $kursusAktif,
            'kursusDraft' => $kursusDraft,
            'totalPeserta' => $totalPeserta,
            'tugasBelumDinilai' => $tugasBelumDinilai,
            'totalModul' => $totalModul,
            'totalMateri' => $totalMateri,
        ];
    }

    /**
     * Get recent activities (last 10)
     */
    private function getRecentActivities()
    {
        $activities = [];

        // Get recent peserta registrations
        $recentPeserta = Peserta::with('opd')
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentPeserta as $peserta) {
            $activities[] = [
                'type' => 'peserta_registered',
                'icon' => 'bi-person-plus',
                'color' => 'success',
                'message' => "Peserta baru terdaftar",
                'details' => $peserta->nama_lengkap . ' dari ' . ($peserta->opd->nama_opd ?? 'OPD'),
                'time' => $peserta->created_at->diffForHumans(),
                'timestamp' => $peserta->created_at,
            ];
        }

        // Get recent instruktur additions
        $recentInstruktur = AdminInstruktur::where('role', 'instruktur')
            ->latest()
            ->take(2)
            ->get();

        foreach ($recentInstruktur as $instruktur) {
            $activities[] = [
                'type' => 'instruktur_added',
                'icon' => 'bi-person-badge',
                'color' => 'primary',
                'message' => "Instruktur baru ditambahkan",
                'details' => $instruktur->nama_lengkap,
                'time' => $instruktur->created_at->diffForHumans(),
                'timestamp' => $instruktur->created_at,
            ];
        }

        // Get recent course creations
        $recentKursus = Kursus::with('adminInstruktur')
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentKursus as $kursus) {
            $activities[] = [
                'type' => 'kursus_created',
                'icon' => 'bi-journal-plus',
                'color' => 'info',
                'message' => "Kursus baru dibuat",
                'details' => $kursus->judul . ' oleh ' . ($kursus->adminInstruktur->nama_lengkap ?? 'Instruktur'),
                'time' => $kursus->created_at->diffForHumans(),
                'timestamp' => $kursus->created_at,
            ];
        }

        // Sort by timestamp
        usort($activities, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get chart data for statistics
     */
    private function getChartData()
    {
        $totalPeserta = Peserta::count();
        $totalInstruktur = AdminInstruktur::where('role', 'instruktur')->count();
        $totalAdmin = AdminInstruktur::where('role', 'admin')->count();
        $totalSuperAdmin = AdminInstruktur::where('role', 'super_admin')->count();

        return [
            'labels' => ['Peserta', 'Instruktur', 'Admin', 'Super Admin'],
            'data' => [
                $totalPeserta,
                $totalInstruktur,
                $totalAdmin,
                $totalSuperAdmin,
            ],
            'colors' => ['#0d6efd', '#198754', '#0dcaf0', '#ffc107']
        ];
    }

    /**
     * Get recent courses (last 5)
     */
    private function getRecentKursus()
    {
        return Kursus::with(['adminInstruktur'])
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * Get recent submissions for instruktur
     */
    private function getRecentSubmissions($instrukturId)
    {
        try {
            return TugasSubmission::with(['peserta', 'tugas.modul.kursus'])
                ->whereHas('tugas.modul.kursus', function($query) use ($instrukturId) {
                    $query->where('admin_instruktur_id', $instrukturId);
                })
                ->where('status', 'submitted')
                ->whereNull('nilai')
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function($submission) {
                    return (object) [
                        'id' => $submission->id,
                        'peserta_id' => $submission->peserta_id,
                        'tanggal_submit' => $submission->created_at,
                        'submission_status' => $submission->status,
                        'nama_peserta' => $submission->peserta->nama_lengkap ?? 'N/A',
                        'judul_tugas' => $submission->tugas->judul ?? 'N/A',
                        'judul_kursus' => $submission->tugas->modul->kursus->judul ?? 'N/A',
                        'kursus_id' => $submission->tugas->modul->kursus->id ?? null,
                    ];
                });
        } catch (\Exception $e) {
            // Return empty if tables don't exist yet or error
            Log::error('Error getting recent submissions: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get chart data for instruktur dashboard
     */
    private function getInstrukturChartData($instrukturId)
    {
        // Get kursus data dengan jumlah peserta
        $kursusData = Kursus::where('admin_instruktur_id', $instrukturId)
            ->where('status', 'aktif')
            ->withCount(['peserta' => function($query) {
                $query->whereIn('pendaftaran_kursus.status', ['disetujui', 'aktif', 'selesai']);
            }])
            ->orderByDesc('peserta_count')
            ->take(4)
            ->get();

        $labels = [];
        $data = [];
        $colors = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107'];

        foreach ($kursusData as $index => $kursus) {
            $labels[] = Str::limit($kursus->judul, 30);
            $data[] = $kursus->peserta_count ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels))
        ];
    }

    /**
     * Get dashboard data via AJAX (optional)
     */
    public function getData(Request $request)
    {
        $type = $request->get('type', 'stats');

        switch ($type) {
            case 'stats':
                return response()->json($this->getAdminStatistics());
            
            case 'activities':
                return response()->json($this->getRecentActivities());
            
            case 'chart':
                return response()->json($this->getChartData());
            
            default:
                return response()->json(['error' => 'Invalid type'], 400);
        }
    }
}