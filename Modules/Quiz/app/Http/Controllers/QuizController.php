<?php

namespace Modules\Quiz\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\Quiz;
use Illuminate\Support\Facades\Validator;
use Modules\Modul\Entities\Modul;

class QuizController extends Controller
{
    /**
     * Display a listing of quizzes.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['modul']);

        // Filter by modul_id
        if ($request->has('modul_id')) {
            $query->where('modul_id', $request->modul_id);
        }

        $quizzes = $query->get();

        return view('quiz::index', compact('quizzes'));
    }

    /**
     * Show the form for creating a new quiz.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // You might need to fetch modules to populate a dropdown
        $moduls = Modul::all(); // Adjust this based on your actual module entity
        return view('quiz::create', compact('moduls'));
    }

    /**
     * Store a newly created quiz in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modul_id' => 'required|exists:moduls,id',
            'judul_quiz' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'nullable|integer|min:1',
            'bobot_nilai' => 'nullable|numeric|min:0.01|max:100',
            'passing_grade' => 'nullable|integer|min:1|max:100',
            'jumlah_soal' => 'nullable|integer|min:0',
            'random_soal' => 'nullable|boolean',
            'tampilkan_hasil' => 'nullable|boolean',
            'max_attempt' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle checkbox inputs
        $input = $request->all();
        $input['random_soal'] = $request->has('random_soal');
        $input['tampilkan_hasil'] = $request->has('tampilkan_hasil');

        $quiz = Quiz::create($input);

        return redirect()->route('quizzes.index')
            ->with('success', 'Quiz berhasil dibuat');
    }

    /**
     * Display the specified quiz.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $quiz = Quiz::with(['modul', 'soalQuiz'])->findOrFail($id);
        return view('quiz::show', compact('quiz')); // Use the quiz show view, not the soal-quiz show view
    }

    /**
     * Show the form for editing the specified quiz.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $quiz = Quiz::findOrFail($id);
        $moduls = Modul::all(); // Adjust this based on your actual module entity
        return view('quiz::edit', compact('quiz', 'moduls'));
    }

    /**
     * Update the specified quiz in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'modul_id' => 'required|exists:moduls,id',
            'judul_quiz' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'nullable|integer|min:1',
            'bobot_nilai' => 'nullable|numeric|min:0.01|max:100',
            'passing_grade' => 'nullable|integer|min:1|max:100',
            'jumlah_soal' => 'nullable|integer|min:0',
            'random_soal' => 'nullable|boolean',
            'tampilkan_hasil' => 'nullable|boolean',
            'max_attempt' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle checkbox inputs
        $input = $request->all();
        $input['random_soal'] = $request->has('random_soal');
        $input['tampilkan_hasil'] = $request->has('tampilkan_hasil');

        $quiz->update($input);

        return redirect()->route('quizzes.show', $quiz->id)
            ->with('success', 'Quiz berhasil diupdate');
    }

    /**
     * Remove the specified quiz from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return redirect()->route('quizzes.index')
            ->with('success', 'Quiz berhasil dihapus');
    }

    /**
     * Display the quiz for instructor to try.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function tryQuiz($id)
    {
        $quiz = Quiz::with(['soalQuiz.options' => function ($query) {
            $query->orderBy('urutan');
        }])->findOrFail($id);

        // If the quiz has no questions, redirect back with a message
        if ($quiz->soalQuiz->count() == 0) {
            return redirect()->route('quizzes.show', $id)
                ->with('error', 'Quiz ini belum memiliki soal. Silahkan tambahkan soal terlebih dahulu.');
        }

        // Random the questions if the quiz settings say so
        if ($quiz->random_soal) {
            $quiz->soalQuiz = $quiz->soalQuiz->shuffle();
        }

        return view('quiz::try-quiz', compact('quiz'));
    }

    /**
     * Process the instructor's quiz attempt.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processTryQuiz(Request $request, $id)
    {
        $quiz = Quiz::with(['soalQuiz.options'])->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Process the answers
        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $totalTidakJawab = 0;
        $detailJawaban = [];

        foreach ($quiz->soalQuiz as $soal) {
            if (isset($request->jawaban[$soal->id]) && !empty($request->jawaban[$soal->id])) {
                $jawaban = $request->jawaban[$soal->id];

                // Find the correct option
                $jawabanBenar = null;
                $jawabanPeserta = null;

                foreach ($soal->options as $option) {
                    if ($option->is_jawaban_benar) {
                        $jawabanBenar = $option;
                    }

                    if ($option->id == $jawaban || $option->urutan == $jawaban) {
                        $jawabanPeserta = $option;
                    }
                }

                $isCorrect = false;

                // Check if the answer is correct
                if ($jawabanPeserta && $jawabanBenar && $jawabanPeserta->id == $jawabanBenar->id) {
                    $jumlahBenar++;
                    $isCorrect = true;
                } else {
                    $jumlahSalah++;
                }

                // Store the detail for this question
                $detailJawaban[$soal->id] = [
                    'pertanyaan' => $soal->pertanyaan,
                    'jawaban_peserta' => $jawabanPeserta ? $jawabanPeserta->teks_opsi : null,
                    'jawaban_benar' => $jawabanBenar ? $jawabanBenar->teks_opsi : null,
                    'is_correct' => $isCorrect,
                ];
            } else {
                $totalTidakJawab++;

                // Store as not answered
                $detailJawaban[$soal->id] = [
                    'pertanyaan' => $soal->pertanyaan,
                    'jawaban_peserta' => null,
                    'jawaban_benar' => null,
                    'is_correct' => null,
                ];
            }
        }

        // Calculate nilai
        $totalSoal = $quiz->soalQuiz->count();
        $nilai = 0;

        if ($totalSoal > 0) {
            $nilai = ($jumlahBenar / $totalSoal) * 100;
        }

        $is_passed = $nilai >= $quiz->passing_grade;

        // Store result in session for result page
        session([
            'try_quiz_result' => [
                'quiz_id' => $quiz->id,
                'nilai' => $nilai,
                'jumlah_benar' => $jumlahBenar,
                'jumlah_salah' => $jumlahSalah,
                'total_tidak_jawab' => $totalTidakJawab,
                'is_passed' => $is_passed,
                'detail_jawaban' => $detailJawaban,
                'waktu_selesai' => now()
            ]
        ]);

        return redirect()->route('quizzes.try-result', $quiz->id);
    }

    /**
     * Show the result of the instructor's quiz attempt.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function tryQuizResult($id)
    {
        $quiz = Quiz::with(['soalQuiz.options'])->findOrFail($id);
        $result = session('try_quiz_result');

        // If no result in session, redirect back to quiz
        if (!$result || $result['quiz_id'] != $id) {
            return redirect()->route('quizzes.show', $id)
                ->with('error', 'Tidak ada data hasil uji coba quiz.');
        }

        return view('quiz::try-result', compact('quiz', 'result'));
    }
}
