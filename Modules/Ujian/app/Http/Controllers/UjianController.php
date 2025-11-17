<?php

namespace Modules\Ujian\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ujian\Entities\Ujian;
use Modules\Ujian\Entities\SoalUjian;
use Modules\Ujian\Entities\UjianResult;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $kursusId = $request->get('kursus_id');

        if ($kursusId) {
            $kursus = Kursus::findOrFail($kursusId);
            $ujians = Ujian::where('kursus_id', $kursusId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('ujian::index', compact('ujians', 'kursus'));
        } else {
            // Tampilkan semua ujian
            $ujians = Ujian::with('kursus')
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $kursus = Kursus::all();

            return view('ujian::index', compact('ujians', 'kursus'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $kursusId = $request->get('kursus_id');
        $kursus = null;

        if ($kursusId) {
            $kursus = Kursus::findOrFail($kursusId);
        } else {
            $kursus = Kursus::all();
        }

        return view('ujian::create', compact('kursus', 'kursusId'));
    }

    public function store(Request $request,)
    {
        $kursusId = $request->kursus_id;

        $validator = Validator::make($request->all(), [
            'judul_ujian' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_mulai' => 'nullable|date',
            'waktu_selesai' => 'nullable|date|after_or_equal:waktu_mulai',
            'durasi_menit' => 'required|integer|min:1',
            'bobot_nilai' => 'required|numeric|min:0.1|max:100',
            'passing_grade' => 'required|integer|min:0|max:100',
            'jumlah_soal' => 'required|integer|min:1',
            'random_soal' => 'sometimes|boolean',
            'tampilkan_hasil' => 'sometimes|boolean',
            'aturan_ujian' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $ujian = new Ujian();
            $ujian->kursus_id = $kursusId; // Ambil dari parameter route
            $ujian->judul_ujian = $request->input('judul_ujian');
            $ujian->deskripsi = $request->input('deskripsi');
            $ujian->waktu_mulai = $request->input('waktu_mulai');
            $ujian->waktu_selesai = $request->input('waktu_selesai');
            $ujian->durasi_menit = $request->input('durasi_menit');
            $ujian->bobot_nilai = $request->input('bobot_nilai');
            $ujian->passing_grade = $request->input('passing_grade');
            $ujian->jumlah_soal = $request->input('jumlah_soal');
            $ujian->random_soal = $request->has('random_soal') ? true : false;
            $ujian->tampilkan_hasil = $request->has('tampilkan_hasil') ? true : false;
            $ujian->aturan_ujian = $request->input('aturan_ujian');

            $ujian->save();

            DB::commit();

            return redirect()->route('ujians.index', $kursusId)
                ->with('success', 'Ujian berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create Ujian Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Gagal membuat ujian: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $ujian = Ujian::with(['kursus', 'soalUjians'])->findOrFail($id);
        $jumlahPeserta = UjianResult::where('ujian_id', $id)->count();
        $jumlahLulus = UjianResult::where('ujian_id', $id)->where('is_passed', true)->count();

        // Hitung nilai rata-rata
        $rataRata = UjianResult::where('ujian_id', $id)->avg('nilai') ?? 0;

        return view('ujian::show', compact('ujian', 'jumlahPeserta', 'jumlahLulus', 'rataRata'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ujian = Ujian::findOrFail($id);
        $kursus = Kursus::all();

        return view('ujian::edit', compact('ujian', 'kursus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'judul_ujian' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_mulai' => 'nullable|date',
            'waktu_selesai' => 'nullable|date|after_or_equal:waktu_mulai',
            'durasi_menit' => 'required|integer|min:1',
            'bobot_nilai' => 'required|numeric|min:0.1|max:100',
            'passing_grade' => 'required|integer|min:0|max:100',
            'random_soal' => 'sometimes|boolean',
            'tampilkan_hasil' => 'sometimes|boolean',
            'aturan_ujian' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $ujian = Ujian::findOrFail($id);
            $ujian->kursus_id = $request->input('kursus_id');
            $ujian->judul_ujian = $request->input('judul_ujian');
            $ujian->deskripsi = $request->input('deskripsi');
            $ujian->waktu_mulai = $request->input('waktu_mulai');
            $ujian->waktu_selesai = $request->input('waktu_selesai');
            $ujian->durasi_menit = $request->input('durasi_menit');
            $ujian->bobot_nilai = $request->input('bobot_nilai');
            $ujian->passing_grade = $request->input('passing_grade');
            $ujian->random_soal = $request->has('random_soal') ? true : false;
            $ujian->tampilkan_hasil = $request->has('tampilkan_hasil') ? true : false;
            $ujian->aturan_ujian = $request->input('aturan_ujian');

            $ujian->save();

            DB::commit();

            return redirect()->route('ujian.show', $ujian->id)
                ->with('success', 'Ujian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui ujian: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $ujian = Ujian::findOrFail($id);

            // Check if there are already submitted answers
            $hasResults = UjianResult::where('ujian_id', $id)->exists();
            if ($hasResults) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus ujian karena sudah ada peserta yang mengerjakan.');
            }

            // Delete all related questions first
            SoalUjian::where('ujian_id', $id)->delete();

            // Delete the exam
            $ujian->delete();

            return redirect()->route('ujian.index')
                ->with('success', 'Ujian berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus ujian: ' . $e->getMessage());
        }
    }

    /**
     * Mulai mengerjakan ujian
     */
    public function mulaiUjian($id)
    {
        $ujian = Ujian::with('kursus')->findOrFail($id);
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai peserta.');
        }

        // Check if exam is available
        $now = Carbon::now();
        if ($ujian->waktu_mulai && $now->lt(Carbon::parse($ujian->waktu_mulai))) {
            return redirect()->back()->with('error', 'Ujian belum dimulai.');
        }

        if ($ujian->waktu_selesai && $now->gt(Carbon::parse($ujian->waktu_selesai))) {
            return redirect()->back()->with('error', 'Ujian sudah berakhir.');
        }

        // Check if user has already taken this exam
        $existingResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $peserta->id)
            ->first();

        if ($existingResult && $existingResult->waktu_selesai) {
            return redirect()->back()->with('error', 'Anda sudah mengerjakan ujian ini.');
        }

        // Get questions
        $soalQuery = SoalUjian::where('ujian_id', $id);

        if ($ujian->random_soal) {
            $soalQuery->inRandomOrder();
        } else {
            $soalQuery->orderBy('id', 'asc');
        }

        $soalUjians = $soalQuery->get();

        if ($soalUjians->isEmpty()) {
            return redirect()->back()->with('error', 'Ujian belum memiliki soal.');
        }

        // Create or update exam result
        if (!$existingResult) {
            $ujianResult = new UjianResult();
            $ujianResult->ujian_id = $id;
            $ujianResult->peserta_id = $peserta->id;
            $ujianResult->waktu_mulai = $now;
            $ujianResult->save();
        } else {
            $ujianResult = $existingResult;
            // If already started but not finished
            if (!$ujianResult->waktu_selesai) {
                // Check if time is still available
                $startTime = Carbon::parse($ujianResult->waktu_mulai);
                $endTime = $startTime->copy()->addMinutes($ujian->durasi_menit);

                if ($now->gt($endTime)) {
                    // Auto submit if time is up
                    return redirect()->route('ujian.autosubmit', $id);
                }
            }
        }

        // Prepare timer information
        $waktuMulai = Carbon::parse($ujianResult->waktu_mulai);
        $waktuSelesai = $waktuMulai->copy()->addMinutes($ujian->durasi_menit);
        $sisa = $now->diffInSeconds($waktuSelesai, false);

        if ($sisa <= 0) {
            // Auto submit if time is up
            return redirect()->route('ujian.autosubmit', $id);
        }

        return view('ujian::kerja-ujian', compact('ujian', 'soalUjians', 'ujianResult', 'waktuMulai', 'waktuSelesai', 'sisa'));
    }

    /**
     * Submit jawaban ujian
     */
    public function submitUjian(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);
        $peserta = Auth::user()->peserta;

        if (!$peserta) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai peserta.');
        }

        // Get current result
        $ujianResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $peserta->id)
            ->first();

        if (!$ujianResult) {
            return redirect()->back()->with('error', 'Anda belum memulai ujian ini.');
        }

        if ($ujianResult->waktu_selesai) {
            return redirect()->back()->with('error', 'Anda sudah menyelesaikan ujian ini.');
        }

        // Collect answers
        $jawaban = [];
        $nilai = 0;
        $totalPoin = 0;

        // Get all questions
        $soalUjians = SoalUjian::where('ujian_id', $id)->get();

        foreach ($soalUjians as $soal) {
            $jawaban[$soal->id] = [
                'jawaban' => $request->input('jawaban_' . $soal->id, ''),
                'benar' => false,
                'poin' => 0
            ];

            // Calculate points for multiple choice and true/false
            if (in_array($soal->tipe_soal, ['pilihan_ganda', 'benar_salah'])) {
                if ($jawaban[$soal->id]['jawaban'] == $soal->jawaban_benar) {
                    $jawaban[$soal->id]['benar'] = true;
                    $jawaban[$soal->id]['poin'] = $soal->poin;
                    $nilai += $soal->poin;
                }
            }

            $totalPoin += $soal->poin;
        }

        // Calculate percentage score
        $nilaiPersen = 0;
        if ($totalPoin > 0) {
            $nilaiPersen = ($nilai / $totalPoin) * 100;
        }

        // Update result
        $ujianResult->jawaban = json_encode($jawaban);
        $ujianResult->nilai = $nilaiPersen;
        $ujianResult->is_passed = $nilaiPersen >= $ujian->passing_grade;
        $ujianResult->waktu_selesai = Carbon::now();
        $ujianResult->tanggal_dinilai = Carbon::now();
        $ujianResult->save();

        return redirect()->route('ujian.hasil', $id)
            ->with('success', 'Ujian berhasil diselesaikan.');
    }

    /**
     * Auto submit when time is up
     */
    public function autoSubmit($id)
    {
        $ujian = Ujian::findOrFail($id);
        $peserta = Auth::user()->peserta;

        $ujianResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $peserta->id)
            ->first();

        if (!$ujianResult) {
            return redirect()->route('ujian.index')
                ->with('error', 'Anda belum memulai ujian ini.');
        }

        if ($ujianResult->waktu_selesai) {
            return redirect()->route('ujian.hasil', $id);
        }

        // Auto calculate score with existing answers
        $jawaban = json_decode($ujianResult->jawaban ?? '{}', true);
        $nilai = 0;
        $totalPoin = 0;

        $soalUjians = SoalUjian::where('ujian_id', $id)->get();

        foreach ($soalUjians as $soal) {
            $soalId = $soal->id;

            if (!isset($jawaban[$soalId])) {
                $jawaban[$soalId] = [
                    'jawaban' => '',
                    'benar' => false,
                    'poin' => 0
                ];
            }

            // Calculate points for multiple choice and true/false
            if (in_array($soal->tipe_soal, ['pilihan_ganda', 'benar_salah'])) {
                if (isset($jawaban[$soalId]['jawaban']) && $jawaban[$soalId]['jawaban'] == $soal->jawaban_benar) {
                    $jawaban[$soalId]['benar'] = true;
                    $jawaban[$soalId]['poin'] = $soal->poin;
                    $nilai += $soal->poin;
                }
            }

            $totalPoin += $soal->poin;
        }

        // Calculate percentage score
        $nilaiPersen = 0;
        if ($totalPoin > 0) {
            $nilaiPersen = ($nilai / $totalPoin) * 100;
        }

        // Update result
        $ujianResult->jawaban = json_encode($jawaban);
        $ujianResult->nilai = $nilaiPersen;
        $ujianResult->is_passed = $nilaiPersen >= $ujian->passing_grade;
        $ujianResult->waktu_selesai = Carbon::now();
        $ujianResult->tanggal_dinilai = Carbon::now();
        $ujianResult->save();

        return redirect()->route('ujian.hasil', $id)
            ->with('warning', 'Waktu telah habis. Ujian diselesaikan secara otomatis.');
    }

    /**
     * Show exam result
     */
    public function hasil($id)
    {
        $ujian = Ujian::findOrFail($id);
        $peserta = Auth::user()->peserta;

        $ujianResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $peserta->id)
            ->first();

        if (!$ujianResult || !$ujianResult->waktu_selesai) {
            return redirect()->back()
                ->with('error', 'Anda belum menyelesaikan ujian ini.');
        }

        $jawaban = json_decode($ujianResult->jawaban, true);
        $soalUjians = SoalUjian::where('ujian_id', $id)->get();

        return view('ujian::hasil', compact('ujian', 'ujianResult', 'soalUjians', 'jawaban'));
    }

    /**
     * Display results for all participants (admin view)
     */
    public function daftarHasil($id)
    {
        $ujian = Ujian::with('kursus')->findOrFail($id);
        $hasil = UjianResult::with('peserta')
            ->where('ujian_id', $id)
            ->orderBy('nilai', 'desc')
            ->paginate(20);

        return view('ujian::daftar-hasil', compact('ujian', 'hasil'));
    }

    /**
     * Show specific participant's detailed result (admin view)
     */
    public function detailHasil($ujianId, $resultId)
    {
        $ujian = Ujian::findOrFail($ujianId);
        $ujianResult = UjianResult::with('peserta')->findOrFail($resultId);

        if ($ujianResult->ujian_id != $ujianId) {
            return redirect()->back()->with('error', 'Data hasil ujian tidak valid.');
        }

        $jawaban = json_decode($ujianResult->jawaban, true);
        $soalUjians = SoalUjian::where('ujian_id', $ujianId)->get();

        return view('ujian::detail-hasil', compact('ujian', 'ujianResult', 'soalUjians', 'jawaban'));
    }

    /**
     * Export results to Excel
     */
    public function exportHasil($id)
    {
        $ujian = Ujian::with('kursus')->findOrFail($id);
        $hasil = UjianResult::with('peserta')->where('ujian_id', $id)->get();

        // Implement export to Excel logic using Laravel Excel package
        // Example using Laravel Excel:
        // return Excel::download(new UjianResultsExport($hasil, $ujian), 'hasil-ujian-'.$ujian->judul_ujian.'.xlsx');

        // Placeholder return (implement actual Excel export based on your requirements)
        return redirect()->back()->with('info', 'Fitur export sedang dalam pengembangan.');
    }

    /**
     * Simulate/try an ujian as admin/instructor.
     */
    public function simulateUjian($id)
    {
        $ujian = Ujian::with(['kursus', 'soalUjians'])->findOrFail($id);

        // Check if there are questions
        if ($ujian->soalUjians->isEmpty()) {
            return redirect()->back()->with('error', 'Ujian belum memiliki soal.');
        }

        // Get user
        $user = Auth::user();

        // Check if user already has an ongoing simulation
        $existingResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $user->id)
            ->whereNull('waktu_selesai')
            ->first();

        // If there's an existing simulation in progress, continue it
        if ($existingResult) {
            // Check if time is still available
            $startTime = Carbon::parse($existingResult->waktu_mulai);
            $endTime = $startTime->copy()->addMinutes($ujian->durasi_menit);
            $now = Carbon::now();

            if ($now->gt($endTime)) {
                // Auto submit if time is up
                return redirect()->route('ujians.process-simulation', $id);
            }

            $ujianResult = $existingResult;
        } else {
            // Create a new simulation result
            $ujianResult = new UjianResult();
            $ujianResult->ujian_id = $id;
            $ujianResult->peserta_id = $user->id;
            // $ujianResult->is_simulation = true; // Mark as simulation
            $ujianResult->waktu_mulai = Carbon::now();
            $ujianResult->save();
        }

        // Prepare the questions
        $soalUjians = $ujian->soalUjians;
        if ($ujian->random_soal) {
            $soalUjians = $soalUjians->shuffle();
        }

        // Prepare timer information
        $waktuMulai = Carbon::parse($ujianResult->waktu_mulai);
        $waktuSelesai = $waktuMulai->copy()->addMinutes($ujian->durasi_menit);
        $sisa = Carbon::now()->diffInSeconds($waktuSelesai, false);

        if ($sisa <= 0) {
            return redirect()->route('ujians.process-simulation', $id);
        }

        return view('ujian::simulate', compact('ujian', 'soalUjians', 'ujianResult', 'waktuMulai', 'waktuSelesai', 'sisa'));
    }

    /**
     * Process simulation results.
     */
    public function processSimulation(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);
        $user = Auth::user();

        // Get current simulation result
        $ujianResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $user->id)
            // ->where('is_simulation', true)
            ->whereNull('waktu_selesai')
            ->firstOrFail();

        // Collect answers
        $jawaban = [];
        $nilai = 0;
        $totalPoin = 0;

        // Get all questions
        $soalUjians = $ujian->soalUjians;

        foreach ($soalUjians as $soal) {
            $jawabanKey = 'jawaban_' . $soal->id;

            $jawaban[$soal->id] = [
                'jawaban' => $request->input($jawabanKey, ''),
                'benar' => false,
                'poin' => 0
            ];

            // Calculate points for multiple choice and true/false
            if (in_array($soal->tipe_soal, ['pilihan_ganda', 'benar_salah'])) {
                if ($jawaban[$soal->id]['jawaban'] == $soal->jawaban_benar) {
                    $jawaban[$soal->id]['benar'] = true;
                    $jawaban[$soal->id]['poin'] = $soal->poin;
                    $nilai += $soal->poin;
                }
            }

            $totalPoin += $soal->poin;
        }

        // Calculate percentage score
        $nilaiPersen = 0;
        if ($totalPoin > 0) {
            $nilaiPersen = ($nilai / $totalPoin) * 100;
        }

        // Update result
        $ujianResult->jawaban = json_encode($jawaban);
        $ujianResult->nilai = $nilaiPersen;
        $ujianResult->is_passed = $nilaiPersen >= $ujian->passing_grade;
        $ujianResult->waktu_selesai = Carbon::now();
        $ujianResult->tanggal_dinilai = Carbon::now();
        $ujianResult->save();

        // Redirect to simulation result page
        return redirect()->route('ujians.simulation-result', $id)
            ->with('success', 'Simulasi ujian berhasil diselesaikan.');
    }

    /**
     * Display simulation result.
     */
    public function simulationResult($id)
    {
        $ujian = Ujian::findOrFail($id);
        $user = Auth::user();

        // Get the most recent simulation result
        $ujianResult = UjianResult::where('ujian_id', $id)
            ->where('peserta_id', $user->id)
            // ->where('is_simulation', true)
            ->whereNotNull('waktu_selesai')
            ->latest()
            ->firstOrFail();

        $jawaban = json_decode($ujianResult->jawaban, true);
        $soalUjians = $ujian->soalUjians;

        return view('ujian::simulation-result', compact('ujian', 'ujianResult', 'soalUjians', 'jawaban'));
    }
}
