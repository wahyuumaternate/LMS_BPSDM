<?php

namespace Modules\Quiz\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Entities\QuizOption;
use Modules\Quiz\Entities\QuizQuestion;
use Illuminate\Support\Facades\Validator;

class SoalQuizController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = QuizQuestion::with(['quiz', 'options']);

        // Filter by quiz_id
        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter by tingkat_kesulitan
        if ($request->has('tingkat_kesulitan')) {
            $query->where('tingkat_kesulitan', $request->tingkat_kesulitan);
        }

        $soalQuizzes = $query->paginate(15);

        return view('quiz::soal-quiz.index', compact('soalQuizzes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Pre-select quiz if quiz_id is provided in the request
        $selectedQuizId = $request->input('quiz_id');
        $quizzes = Quiz::orderBy('judul_quiz')->get();

        return view('quiz::soal-quiz.create', compact('quizzes', 'selectedQuizId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi untuk pertanyaan
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'pertanyaan' => 'required|string',
            'poin' => 'nullable|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',

            // Validasi untuk opsi jawaban
            'teks_opsi' => 'required|array|min:2|max:5',
            'teks_opsi.*' => 'required|string',
            'is_jawaban_benar' => 'required|integer|min:0|max:4', // Index of the correct answer
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Mulai transaksi database
        return DB::transaction(function () use ($request) {
            // Create pertanyaan
            $soalQuiz = QuizQuestion::create([
                'quiz_id' => $request->quiz_id,
                'pertanyaan' => $request->pertanyaan,
                'poin' => $request->poin ?? 1,
                'pembahasan' => $request->pembahasan,
                'tingkat_kesulitan' => $request->tingkat_kesulitan ?? 'mudah',
            ]);

            // Create opsi jawaban
            foreach ($request->teks_opsi as $index => $teksOpsi) {
                $soalQuiz->options()->create([
                    'teks_opsi' => $teksOpsi,
                    'is_jawaban_benar' => ($index == $request->is_jawaban_benar),
                    'urutan' => $index + 1,
                ]);
            }

            // Update jumlah_soal di quiz
            $quiz = Quiz::findOrFail($request->quiz_id);
            $quiz->jumlah_soal = $quiz->soalQuiz()->count();
            $quiz->save();

            return redirect()->route('quizzes.show', $soalQuiz->id)
                ->with('success', 'Soal quiz berhasil dibuat');
        });
    }

    public function show($id)
    {
        $soalQuiz = QuizQuestion::with(['quiz', 'options' => function ($query) {
            $query->orderBy('urutan');
        }])->findOrFail($id);

        return view('quiz::soal-quiz.show', compact('soalQuiz'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $soalQuiz = QuizQuestion::with(['options' => function ($query) {
            $query->orderBy('urutan');
        }])->findOrFail($id);
        $quizzes = Quiz::orderBy('judul_quiz')->get();

        // Get the index of the correct answer
        $correctAnswerIndex = -1;
        foreach ($soalQuiz->options as $index => $option) {
            if ($option->is_jawaban_benar) {
                $correctAnswerIndex = $index;
                break;
            }
        }

        return view('quiz::soal-quiz.edit', compact('soalQuiz', 'quizzes', 'correctAnswerIndex'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $soalQuiz = QuizQuestion::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'pertanyaan' => 'required|string',
            'poin' => 'nullable|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',

            // Validasi untuk opsi jawaban
            'teks_opsi' => 'required|array|min:2|max:5',
            'teks_opsi.*' => 'required|string',
            'option_ids' => 'nullable|array',
            'option_ids.*' => 'nullable|integer|exists:quiz_options,id',
            'is_jawaban_benar' => 'required|integer|min:0|max:4', // Index of the correct answer
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Mulai transaksi database
        return DB::transaction(function () use ($request, $soalQuiz) {
            // Update pertanyaan
            $soalQuiz->update([
                'quiz_id' => $request->quiz_id,
                'pertanyaan' => $request->pertanyaan,
                'poin' => $request->poin ?? 1,
                'pembahasan' => $request->pembahasan,
                'tingkat_kesulitan' => $request->tingkat_kesulitan ?? 'mudah',
            ]);

            // Update atau buat opsi jawaban baru
            $optionIds = $request->option_ids ?? [];

            // Hapus opsi yang tidak ada di request
            $soalQuiz->options()->whereNotIn('id', array_filter($optionIds))->delete();

            foreach ($request->teks_opsi as $index => $teksOpsi) {
                $optionId = isset($optionIds[$index]) ? $optionIds[$index] : null;

                if ($optionId) {
                    // Update existing option
                    QuizOption::where('id', $optionId)->update([
                        'teks_opsi' => $teksOpsi,
                        'is_jawaban_benar' => ($index == $request->is_jawaban_benar),
                        'urutan' => $index + 1,
                    ]);
                } else {
                    // Create new option
                    $soalQuiz->options()->create([
                        'teks_opsi' => $teksOpsi,
                        'is_jawaban_benar' => ($index == $request->is_jawaban_benar),
                        'urutan' => $index + 1,
                    ]);
                }
            }

            return redirect()->route('quizzes.show', $soalQuiz->id)
                ->with('success', 'Soal quiz berhasil diperbarui');
        });
    }

    public function destroy($id)
    {
        try {
            $soalQuiz = QuizQuestion::findOrFail($id);
            $quizId = $soalQuiz->quiz_id;

            Log::info('Before delete question', [
                'question_id' => $id,
                'quiz_id' => $quizId,
                'quiz_exists' => Quiz::find($quizId) ? 'yes' : 'no'
            ]);

            DB::beginTransaction();

            // Hapus options
            QuizOption::where('question_id', $soalQuiz->id)->delete();

            // Hapus question
            $soalQuiz->delete();

            Log::info('After delete question', [
                'question_id' => $id,
                'quiz_id' => $quizId,
                'quiz_exists' => Quiz::find($quizId) ? 'yes' : 'no'
            ]);

            DB::commit();

            if (!Quiz::find($quizId)) {
                Log::error('QUIZ DELETED!', ['quiz_id' => $quizId]);
                throw new \Exception('Quiz terhapus saat menghapus question!');
            }

            return redirect()->route('quizzes.show', $quizId)
                ->with('success', 'Soal berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Error:', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
    /**
     * Create multiple questions at once for a quiz.
     *
     * @return \Illuminate\View\View
     */
    public function createBulk(Request $request)
    {
        $selectedQuizId = $request->input('quiz_id');
        $quizzes = Quiz::orderBy('judul_quiz')->get();

        return view('quiz::soal-quiz.create-bulk', compact('quizzes', 'selectedQuizId'));
    }

    /**
     * Store multiple soal quiz at once (bulk create).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBulk(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'soal' => 'required|array|min:1',
            'soal.*.pertanyaan' => 'required|string',
            'soal.*.poin' => 'nullable|integer|min:1',
            'soal.*.options' => 'required|array|min:2',
            'soal.*.options.*.teks_opsi' => 'required|string',
            'soal.*.options.*.urutan' => 'required|integer|min:1',
            'soal.*.jawaban_benar' => 'required|integer|min:0',
        ], [
            'quiz_id.required' => 'Quiz ID harus diisi',
            'quiz_id.exists' => 'Quiz tidak ditemukan',
            'soal.required' => 'Minimal harus ada 1 soal',
            'soal.min' => 'Minimal harus ada 1 soal',
            'soal.*.pertanyaan.required' => 'Pertanyaan harus diisi',
            'soal.*.options.required' => 'Pilihan jawaban harus diisi',
            'soal.*.options.min' => 'Minimal harus ada 2 pilihan jawaban',
            'soal.*.options.*.teks_opsi.required' => 'Teks opsi harus diisi',
            'soal.*.jawaban_benar.required' => 'Jawaban benar harus dipilih',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $quiz = Quiz::findOrFail($request->quiz_id);
            $soalArray = $request->soal;
            $totalCreated = 0;

            // Get max urutan untuk urutan soal
            $maxUrutan = QuizQuestion::where('quiz_id', $quiz->id)->max('urutan') ?? 0;

            foreach ($soalArray as $soalData) {
                $maxUrutan++;

                // Create Question (selalu pilihan_ganda)
                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'pertanyaan' => $soalData['pertanyaan'],
                    'poin' => $soalData['poin'] ?? 1,
                    'urutan' => $maxUrutan,
                ]);

                // Create Options
                if (isset($soalData['options']) && is_array($soalData['options'])) {
                    foreach ($soalData['options'] as $optionIndex => $optionData) {
                        QuizOption::create([
                            'question_id' => $question->id,
                            'teks_opsi' => $optionData['teks_opsi'],
                            'urutan' => $optionData['urutan'],
                            'is_jawaban_benar' => ($optionIndex == $soalData['jawaban_benar']) ? 1 : 0,
                        ]);
                    }
                }

                $totalCreated++;
            }

            DB::commit();

            return redirect()->route('quizzes.show', $quiz->id)
                ->with('success', "Berhasil menambahkan {$totalCreated} soal");
        } catch (\Exception $e) {
            DB::rollBack();

            // Log error untuk debugging
            Log::error('Bulk Store Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Filter questions by quiz.
     *
     * @param int $quizId
     * @return \Illuminate\View\View
     */
    public function getByQuiz($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        $query = QuizQuestion::with(['options' => function ($q) {
            $q->orderBy('urutan');
        }])->where('quiz_id', $quizId);

        $soalQuizzes = $query->paginate(15);

        return view('quiz::soal-quiz.by-quiz', compact('quiz', 'soalQuizzes'));
    }

    /**
     * Validate if a question has exactly one correct answer.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateOptions($id)
    {
        $soalQuiz = QuizQuestion::with('options')->findOrFail($id);
        $options = $soalQuiz->options;
        $correctOptionsCount = $options->where('is_jawaban_benar', true)->count();

        $result = [
            'options_count' => $options->count(),
            'correct_options_count' => $correctOptionsCount,
        ];

        if ($options->count() < 2) {
            $result['status'] = 'invalid';
            $result['message'] = 'Soal harus memiliki minimal 2 opsi jawaban';
            $result['is_valid'] = false;
        } elseif ($correctOptionsCount !== 1) {
            $result['status'] = 'invalid';
            $result['message'] = $correctOptionsCount === 0
                ? 'Soal tidak memiliki jawaban benar'
                : 'Soal memiliki lebih dari satu jawaban benar';
            $result['is_valid'] = false;
        } else {
            $result['status'] = 'valid';
            $result['message'] = 'Soal memiliki tepat satu jawaban benar';
            $result['is_valid'] = true;
        }

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->back()->with('validation_result', $result);
    }
}
