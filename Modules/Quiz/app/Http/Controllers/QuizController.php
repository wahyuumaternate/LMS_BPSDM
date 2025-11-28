<?php

namespace Modules\Quiz\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Quiz\Entities\Quiz;
use Illuminate\Support\Facades\Validator;
use Modules\Modul\Entities\Modul;
use Modules\Kursus\Entities\Kursus;
use Modules\Quiz\Entities\QuizOption;
use Modules\Quiz\Entities\QuizQuestion;

class QuizController extends Controller
{
    /**
     * Display a listing of quizzes for a specific course.
     *
     * @param int $kursusId
     * @return \Illuminate\View\View
     */
    public function index($kursusId)
    {
        $kursus = Kursus::with(['modul.quizzes'])->findOrFail($kursusId);

        return view('quiz::index', compact('kursus'));
    }

    /**
     * Store a newly created quiz in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modul_id' => 'required|exists:moduls,id',
            'judul_quiz' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:0',
            'bobot_nilai' => 'nullable|numeric|min:0|max:100',
            'passing_grade' => 'required|integer|min:0|max:100',
            'jumlah_soal' => 'nullable|integer|min:0',
            'random_soal' => 'nullable|boolean',
            'tampilkan_hasil' => 'nullable|boolean',
            'max_attempt' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get modul to get kursus_id
        $modul = Modul::findOrFail($request->modul_id);

        // Handle checkbox inputs
        $data = $request->all();
        $data = $request->except(['_token', '_method']);
        $data['random_soal'] = $request->boolean('random_soal') ? 1 : 0;
        $data['tampilkan_hasil'] = $request->boolean('tampilkan_hasil') ? 1 : 0;
        $data['is_published'] = $request->boolean('is_published') ? 1 : 0;

        // Set published_at if is_published is true
        if ($data['is_published']) {
            $data['published_at'] = now();
        }

        $quiz = Quiz::create($data);

        return redirect()->route('course.kuis', $modul->kursus_id)
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
        $quiz = Quiz::with(['modul.kursus', 'soalQuiz'])->findOrFail($id);
        return view('quiz::show', compact('quiz'));
    }

    /**
     * Update the specified quiz in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $quiz = Quiz::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'modul_id' => 'required|exists:moduls,id',
            'judul_quiz' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:0',
            'bobot_nilai' => 'nullable|numeric|min:0|max:100',
            'passing_grade' => 'required|integer|min:0|max:100',
            'jumlah_soal' => 'nullable|integer|min:0',
            'random_soal' => 'nullable|boolean',
            'tampilkan_hasil' => 'nullable|boolean',
            'max_attempt' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get modul to get kursus_id
        $modul = Modul::findOrFail($request->modul_id);

       // ✅ CARA BARU (BENAR)
        $data = $request->except(['_token', '_method']);
        $data['random_soal'] = $request->boolean('random_soal') ? 1 : 0;
        $data['tampilkan_hasil'] = $request->boolean('tampilkan_hasil') ? 1 : 0;
        $data['is_published'] = $request->boolean('is_published') ? 1 : 0;
        // Set published_at if is_published changed to true
        if ($data['is_published'] && !$quiz->is_published) {
            $data['published_at'] = now();
        } elseif (!$data['is_published']) {
            $data['published_at'] = null;
        }

        $quiz->update($data);

        return redirect()->route('course.kuis', $modul->kursus_id)
            ->with('success', 'Quiz berhasil diupdate');
    }

    /**
     * Remove the specified quiz from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $kursusId = $quiz->modul->kursus_id;

        $quiz->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quiz berhasil dihapus',
            'redirect' => route('course.kuis', $kursusId)
        ]);
    }

    /**
     * Display questions page for a quiz.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function questions($id)
    {
        $quiz = Quiz::with(['modul.kursus', 'soalQuiz.options'])->findOrFail($id);

        return view('quiz::questions', compact('quiz'));
    }

    /**
     * Display the quiz for instructor/student to try.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function tryQuiz($id)
    {
        $quiz = Quiz::with(['questions.options' => function ($query) {
            $query->orderBy('urutan');
        }])->findOrFail($id);

        // If the quiz has no questions, redirect back with a message
        if ($quiz->questions->count() == 0) {
            return redirect()->route('quizzes.show', $id)
                ->with('error', 'Quiz ini belum memiliki soal. Silahkan tambahkan soal terlebih dahulu.');
        }

        // Get questions to display
        $questions = $quiz->questions;

        // Random the questions if the quiz settings say so
        if ($quiz->random_soal) {
            $questions = $questions->shuffle();
        }

        // Limit questions if jumlah_soal is set
        if ($quiz->jumlah_soal > 0 && $quiz->jumlah_soal < $questions->count()) {
            $questions = $questions->take($quiz->jumlah_soal);
        }

        return view('quiz::try-quiz', compact('quiz', 'questions'));
    }

    /**
     * Process the quiz attempt.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processTryQuiz(Request $request, $id)
    {
        $quiz = Quiz::with(['questions.options'])->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jawaban' => 'nullable|array',
            'jawaban.*' => 'nullable|integer',
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

        foreach ($quiz->questions as $question) {
            $jawabanBenar = $question->options->where('is_jawaban_benar', 1)->first();

            if (isset($request->jawaban[$question->id]) && !empty($request->jawaban[$question->id])) {
                $jawabanPeserta = $question->options->where('id', $request->jawaban[$question->id])->first();

                $isCorrect = false;
                if ($jawabanPeserta && $jawabanBenar && $jawabanPeserta->id == $jawabanBenar->id) {
                    $jumlahBenar++;
                    $isCorrect = true;
                } else {
                    $jumlahSalah++;
                }

                $detailJawaban[$question->id] = [
                    'pertanyaan' => $question->pertanyaan,
                    'jawaban_peserta' => $jawabanPeserta ? $jawabanPeserta->teks_opsi : null,
                    'jawaban_peserta_id' => $jawabanPeserta ? $jawabanPeserta->id : null,
                    'jawaban_benar' => $jawabanBenar ? $jawabanBenar->teks_opsi : null,
                    'jawaban_benar_id' => $jawabanBenar ? $jawabanBenar->id : null,
                    'is_correct' => $isCorrect,
                ];
            } else {
                $totalTidakJawab++;

                $detailJawaban[$question->id] = [
                    'pertanyaan' => $question->pertanyaan,
                    'jawaban_peserta' => null,
                    'jawaban_peserta_id' => null,
                    'jawaban_benar' => $jawabanBenar ? $jawabanBenar->teks_opsi : null,
                    'jawaban_benar_id' => $jawabanBenar ? $jawabanBenar->id : null,
                    'is_correct' => false,
                ];
            }
        }

        // Calculate nilai
        $totalSoal = $quiz->questions->count();
        $nilai = 0;

        if ($totalSoal > 0) {
            $nilai = round(($jumlahBenar / $totalSoal) * 100, 2);
        }

        $is_passed = $nilai >= $quiz->passing_grade;

        // Store result in session for result page
        session([
            'try_quiz_result' => [
                'quiz_id' => $quiz->id,
                'quiz_judul' => $quiz->judul_quiz,
                'nilai' => $nilai,
                'jumlah_benar' => $jumlahBenar,
                'jumlah_salah' => $jumlahSalah,
                'total_tidak_jawab' => $totalTidakJawab,
                'total_soal' => $totalSoal,
                'passing_grade' => $quiz->passing_grade,
                'is_passed' => $is_passed,
                'detail_jawaban' => $detailJawaban,
                'waktu_mulai' => $request->waktu_mulai ?? now(),
                'waktu_selesai' => now()
            ]
        ]);

        return redirect()->route('quizzes.try-result', $quiz->id);
    }

    /**
     * Show the result of the quiz attempt.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function tryQuizResult($id)
    {
        $quiz = Quiz::with(['modul.kursus', 'questions.options'])->findOrFail($id);
        $result = session('try_quiz_result');

        // If no result in session or different quiz, redirect back
        if (!$result || $result['quiz_id'] != $id) {
            return redirect()->route('quizzes.show', $id)
                ->with('error', 'Tidak ada data hasil quiz.');
        }

        return view('quiz::try-result', compact('quiz', 'result'));
    }
}
