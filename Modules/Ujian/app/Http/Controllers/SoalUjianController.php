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
    public function index(Request $request)
    {
        $ujianId = $request->query('ujian_id');
        if (!$ujianId) {
            return redirect()->route('ujians.index')->with('error', 'ID Ujian diperlukan');
        }

        $ujian = Ujian::with('kursus')->findOrFail($ujianId);
        $soals = SoalUjian::where('ujian_id', $ujianId)->orderBy('id', 'asc')->paginate(20);

        return view('ujian::soal-ujian.index', compact('ujian', 'soals'));
    }

    public function create(Request $request)
    {
        $ujianId = $request->query('ujian_id');
        if (!$ujianId) {
            return redirect()->route('ujians.index')->with('error', 'ID Ujian diperlukan');
        }

        $ujian = Ujian::findOrFail($ujianId);
        return view('ujian::soal-ujian.create', compact('ujian'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ujianId = $request->input('ujian_id');
        if (!$ujianId) {
            return redirect()->route('ujians.index')->with('error', 'ID Ujian diperlukan');
        }
        // dd($ujianId);

        $validator = Validator::make($request->all(), [
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string',
            'pilihan_b' => 'required|string',
            'pilihan_c' => 'required|string',
            'pilihan_d' => 'required|string',
            'jawaban_benar' => 'required|string|in:A,B,C,D',
            'poin' => 'required|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
        ]);

        $ujian = Ujian::findOrFail($ujianId);

        try {
            DB::beginTransaction();

            $soal = new SoalUjian();
            $soal->ujian_id = $ujianId;
            $soal->pertanyaan = $request->input('pertanyaan');
            $soal->tipe_soal = 'pilihan_ganda'; // Selalu pilihan ganda
            $soal->pilihan_a = $request->input('pilihan_a');
            $soal->pilihan_b = $request->input('pilihan_b');
            $soal->pilihan_c = $request->input('pilihan_c');
            $soal->pilihan_d = $request->input('pilihan_d');
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

            return redirect()->route('soal-ujian.by-ujian', $ujianId)->with('success', 'Soal berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan soal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request, $id)
    {
        $soal = SoalUjian::findOrFail($id);
        $ujianId = $soal->ujian_id;
        $ujian = Ujian::findOrFail($ujianId);

        return view('ujian::soal-ujian.show', compact('ujian', 'soal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $soal = SoalUjian::findOrFail($id);
        $ujianId = $soal->ujian_id;
        $ujian = Ujian::findOrFail($ujianId);

        return view('ujian::soal-ujian.edit', compact('ujian', 'soal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($id);
        $soal = SoalUjian::findOrFail($id);
        $ujianId = $soal->ujian_id;

        $validator = Validator::make($request->all(), [
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string',
            'pilihan_b' => 'required|string',
            'pilihan_c' => 'required|string',
            'pilihan_d' => 'required|string',
            'jawaban_benar' => 'required|string|in:A,B,C,D',
            'poin' => 'required|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $soal->pertanyaan = $request->input('pertanyaan');
            $soal->tipe_soal = 'pilihan_ganda'; // Selalu pilihan ganda
            $soal->pilihan_a = $request->input('pilihan_a');
            $soal->pilihan_b = $request->input('pilihan_b');
            $soal->pilihan_c = $request->input('pilihan_c');
            $soal->pilihan_d = $request->input('pilihan_d');
            $soal->jawaban_benar = $request->input('jawaban_benar');
            $soal->poin = $request->input('poin');
            $soal->pembahasan = $request->input('pembahasan');
            $soal->tingkat_kesulitan = $request->input('tingkat_kesulitan');

            $soal->save();

            DB::commit();

            return redirect()
                ->route('soal-ujian.by-ujian', ['ujian_id' => $ujianId])
                ->with('success', 'Soal berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui soal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        // dd($id);
        try {
            DB::beginTransaction();

            $soal = SoalUjian::findOrFail($id);
            $ujianId = $soal->ujian_id;
            $ujian = Ujian::findOrFail($ujianId);

            // Check if exam has already been taken
            if ($ujian->ujianResults()->count() > 0) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus soal karena ujian sudah pernah dikerjakan.');
            }

            $soal->delete();

            // Update jumlah soal di ujian
            $jumlahSoal = SoalUjian::where('ujian_id', $ujianId)->count();
            $ujian->jumlah_soal = $jumlahSoal;
            $ujian->save();

            DB::commit();

            return redirect()
                ->route('soal-ujian.by-ujian', ['ujian_id' => $ujianId])
                ->with('success', 'Soal berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menghapus soal: ' . $e->getMessage());
        }
    }

    /**
     * Import soal dari file
     */
    public function importForm($ujianId)
    {
        $ujian = Ujian::findOrFail($ujianId);
        return view('ujian::soal-ujian.import', compact('ujian'));
    }

    /**
     * Process import soal
     */
    public function import(Request $request, $ujianId)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Logic to import questions from Excel/CSV file
            // This should be implemented based on your file structure
            // Import harus menyesuaikan dengan format pilihan ganda

            // Placeholder return
            return redirect()
                ->route('soal-ujian.by-ujian', ['ujian_id' => $ujianId])
                ->with('info', 'Fitur import soal sedang dalam pengembangan.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
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
        // return Excel::download(new SoalTemplateExport(), 'template-soal-pilihan-ganda.xlsx');

        // Placeholder return
        return redirect()->back()->with('info', 'Fitur download template sedang dalam pengembangan.');
    }

    public function getByUjian($ujianId)
    {
        $ujian = Ujian::findOrFail($ujianId);
        $soals = SoalUjian::where('ujian_id', $ujianId)->paginate(10);

        return view('ujian::soal-ujian.by-ujian', compact('ujian', 'soals'));
    }
}
