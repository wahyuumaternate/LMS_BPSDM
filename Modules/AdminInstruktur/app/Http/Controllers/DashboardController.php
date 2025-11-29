<?php

namespace Modules\AdminInstruktur\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Peserta\Entities\Peserta;
use Modules\AdminInstruktur\Entities\AdminInstruktur;
use Modules\Kursus\Entities\Kursus;
use Modules\OPD\Entities\OPD;
// use Modules\Kursus\Entities\Kursus; // Uncomment when ready

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
        // $myCourses = Kursus::where('instruktur_id', $user->id)->latest()->take(5)->get();
        $myCourses = []; // Temporary until Kursus model ready
        
        // Get instruktur's students
        // $myStudents = Peserta::whereHas('kursus', function($q) use ($user) {
        //     $q->where('instruktur_id', $user->id);
        // })->latest()->take(10)->get();
        $myStudents = []; // Temporary

        return view('admininstruktur::instruktur.dashboard', compact(
            'stats',
            'myCourses',
            'myStudents'
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

        // Kursus statistics (uncomment when ready)
        $totalKursus = Kursus::count();
        $kursusAktif = Kursus::where('status', 'aktif')->count();
        $kursusPersiapan = Kursus::where('status', 'persiapan')->count();
        
        // Temporary data
        // $totalKursus = 45;
        // $kursusAktif = 32;
        // $kursusPersiapan = 8;
        $kursusGrowth = 12;

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
        // Uncomment when Kursus model is ready
        // $totalKursus = Kursus::where('instruktur_id', $instrukturId)->count();
        // $kursusAktif = Kursus::where('instruktur_id', $instrukturId)
        //     ->where('status', 'aktif')
        //     ->count();
        // $totalPeserta = Peserta::whereHas('kursus', function($q) use ($instrukturId) {
        //     $q->where('instruktur_id', $instrukturId);
        // })->count();
        
        // Temporary data
        $totalKursus = 8;
        $kursusAktif = 5;
        $totalPeserta = 124;
        $rataRataRating = 4.5;

        return [
            'totalKursus' => $totalKursus,
            'kursusAktif' => $kursusAktif,
            'totalPeserta' => $totalPeserta,
            'rataRataRating' => $rataRataRating,
        ];
    }

    /**
     * Get recent activities (last 10)
     */
    private function getRecentActivities()
    {
        // This is placeholder - implement activity log later
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
            ->take(10)
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
        $totalAdmin = AdminInstruktur::whereIn('role', ['admin', 'super_admin'])->count();

        return [
            'labels' => ['Peserta', 'Instruktur', 'Admin', 'Super Admin'],
            'data' => [
                $totalPeserta,
                $totalInstruktur,
                AdminInstruktur::where('role', 'admin')->count(),
                AdminInstruktur::where('role', 'super_admin')->count(),
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
            ->take(3)
            ->get();
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