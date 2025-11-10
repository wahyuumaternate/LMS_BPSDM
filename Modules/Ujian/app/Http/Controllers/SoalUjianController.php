<?php

namespace Modules\Ujian\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ujian\Entities\Ujian;
use Modules\Ujian\Entities\SoalUjian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SoalUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($ujianId)
    {
        $ujian = Ujian::with('kursus')->findOrFail($ujianId);
        $soals = SoalUjian::where('ujian_id', $ujianId)
            ->orderBy('id', 'asc')
            ->paginate(20);

        return view('ujian::soal.index', compact('ujian', 'soals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($ujianId)
    {
        $ujian = Ujian::findOrFail($ujianId);
        return view('ujian::soal.create', compact('ujian'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $ujianId)
    {
        $validator = Validator::make($request->all(), [
            'pertanyaan' => 'required|string',
            'tipe_soal' => 'required|in:pilihan_ganda,essay,benar_salah',
            'pilihan_a' => 'required_if:tipe_soal,pilihan_ganda|nullable|string',
            'pilihan_b' => 'required_if:tipe_soal,pilihan_ganda|nullable|string',
            'pilihan_c' => 'required_if:tipe_soal,pilihan_ganda|nullable|string',
            'pilihan_d' => 'required_if:tipe_soal,pilihan_ganda|nullable|string',
            'jawaban_benar' => 'required_if:tipe_soal,pilihan_ganda,benar_salah|nullable|string',
            'poin' => 'required|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $ujian = Ujian::findOrFail($ujianId);

        try {
            DB::beginTransaction();

            $soal = new SoalUjian();
            $soal->ujian_id = $ujianId;
            $soal->pertanyaan = $request->input('pertanyaan');
            $soal->tipe_soal = $request->input('tipe_soal');

            if ($soal->tipe_soal == 'pilihan_ganda') {
                $soal->pilihan_a = $request->input('pilihan_a');
                $soal->pilihan_b = $request->input('pilihan_b');
                $soal->pilihan_c = $request->input('pilihan_c');
                $soal->pilihan_d = $request->input('pilihan_d');
            } elseif ($soal->tipe_soal == 'benar_salah') {
                $soal->pilihan_a = 'Benar';
                $soal->pilihan_b = 'Salah';
                $soal->pilihan_c = null;
                $soal->pilihan_d = null;
            }

            $soal->jawaban_benar = $request->input('jawaban_benar');
            $soal->poin = $request->input('poin');
            $soal->pembahasan = $request->input('pembahasan');
            $soal->tingkat_kesulitan = $request->input('tingkat_kesulitan');

            $soal->save();

            // Update jumlah soal di ujian
            $jumlahSoal = SoalUjian::where('ujian_id', $ujianId)->count();
            $ujian->jumlah_soal = $jumlahSoal;
            $ujian->save();

            DB::commit();

            return redirect()->route('ujian.soal.index', $ujianId)
                ->with('success', 'Soal berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan soal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($ujianId, $id)
    {
        $ujian = Ujian::findOrFail($ujianId);
        $soal = SoalUjian::where('ujian_id', $ujianId)
            ->where('id', $id)
            ->firstOrFail();

        return view('ujian::soal.show', compact('ujian', 'soal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($ujianId, $id)
    {
        $ujian = Ujian::findOrFail($ujianId);
        $soal = SoalUjian::where('ujian_id', $ujianId)
            ->where('id', $id)
            ->firstOrFail();

        return view('ujian::soal.edit', compact('ujian', 'soal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $ujianId, $id)
    {
        $validator = Validator::make($request->all(), [
            'pertanyaan' => 'required|string',
            'tipe_soal' => 'required|in:pilihan_ganda,essay,benar_salah',
            'pilihan_a' => 'required_if:tipe_soal,pilihan_ganda|nullable|string',
            'pilihan_b' => 'required_if:tipe_soal,pilihan_ganda|nullable|string',
            'pilihan_c' => 'required_if:tipe_soal,pilihan_ganda|nullable|string',
            'pilihan_d' => 'required_if:tipe_soal,pilihan_ganda|nullable|string',
            'jawaban_benar' => 'required_if:tipe_soal,pilihan_ganda,benar_salah|nullable|string',
            'poin' => 'required|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $soal = SoalUjian::where('ujian_id', $ujianId)
                ->where('id', $id)
                ->firstOrFail();

            $soal->pertanyaan = $request->input('pertanyaan');
            $soal->tipe_soal = $request->input('tipe_soal');

            if ($soal->tipe_soal == 'pilihan_ganda') {
                $soal->pilihan_a = $request->input('pilihan_a');
                $soal->pilihan_b = $request->input('pilihan_b');
                $soal->pilihan_c = $request->input('pilihan_c');
                $soal->pilihan_d = $request->input('pilihan_d');
            } elseif ($soal->tipe_soal == 'benar_salah') {
                $soal->pilihan_a = 'Benar';
                $soal->pilihan_b = 'Salah';
                $soal->pilihan_c = null;
                $soal->pilihan_d = null;
            }

            $soal->jawaban_benar = $request->input('jawaban_benar');
            $soal->poin = $request->input('poin');
            $soal->pembahasan = $request->input('pembahasan');
            $soal->tingkat_kesulitan = $request->input('tingkat_kesulitan');

            $soal->save();

            DB::commit();

            return redirect()->route('ujian.soal.index', $ujianId)
                ->with('success', 'Soal berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui soal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($ujianId, $id)
    {
        try {
            DB::beginTransaction();

            $ujian = Ujian::findOrFail($ujianId);
            $soal = SoalUjian::where('ujian_id', $ujianId)
                ->where('id', $id)
                ->firstOrFail();

            // Check if exam has already been taken
            if ($ujian->ujianResults()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus soal karena ujian sudah pernah dikerjakan.');
            }

            $soal->delete();

            // Update jumlah soal di ujian
            $jumlahSoal = SoalUjian::where('ujian_id', $ujianId)->count();
            $ujian->jumlah_soal = $jumlahSoal;
            $ujian->save();

            DB::commit();

            return redirect()->route('ujian.soal.index', $ujianId)
                ->with('success', 'Soal berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus soal: ' . $e->getMessage());
        }
    }

    /**
     * Import soal dari file
     */
    public function importForm($ujianId)
    {
        $ujian = Ujian::findOrFail($ujianId);
        return view('ujian::soal.import', compact('ujian'));
    }

    /**
     * Process import soal
     */
    public function import(Request $request, $ujianId)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Logic to import questions from Excel/CSV file
            // This should be implemented based on your file structure

            // Placeholder return
            return redirect()->route('ujian.soal.index', $ujianId)
                ->with('info', 'Fitur import soal sedang dalam pengembangan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat import soal: ' . $e->getMessage());
        }
    }

    /**
     * Download template import
     */
    public function downloadTemplate()
    {
        // Logic to provide template download
        // Example with Laravel Excel:
        // return Excel::download(new SoalTemplateExport(), 'template-soal.xlsx');

        // Placeholder return
        return redirect()->back()->with('info', 'Fitur download template sedang dalam pengembangan.');
    }
}
