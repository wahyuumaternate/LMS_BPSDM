<?php

namespace Modules\Quiz\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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

            return redirect()->route('soal-quiz.index', $soalQuiz->id)
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

            return redirect()->route('soal-quiz.index', $soalQuiz->id)
                ->with('success', 'Soal quiz berhasil diperbarui');
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $soalQuiz = QuizQuestion::findOrFail($id);
        $quizId = $soalQuiz->quiz_id;

        return DB::transaction(function () use ($soalQuiz, $quizId) {
            // Hapus semua opsi jawaban terlebih dahulu
            $soalQuiz->options()->delete();

            // Hapus pertanyaan
            $soalQuiz->delete();

            // Update quiz's question count
            $quiz = Quiz::findOrFail($quizId);
            $quiz->jumlah_soal = $quiz->soalQuiz()->count();
            $quiz->save();

            return redirect()->route('quizzes.show', $quizId)
                ->with('success', 'Soal quiz berhasil dihapus');
        });
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
     * Store multiple questions at once.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'questions' => 'required|array|min:1',
            'questions.*.pertanyaan' => 'required|string',
            'questions.*.poin' => 'nullable|integer|min:1',
            'questions.*.pembahasan' => 'nullable|string',
            'questions.*.tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',
            'questions.*.options' => 'required|array|min:2|max:5',
            'questions.*.options.*.teks_opsi' => 'required|string',
            'questions.*.options.*.is_jawaban_benar' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $created = 0;

        // Mulai transaksi database
        DB::transaction(function () use ($request, &$created) {
            foreach ($request->questions as $questionData) {
                // Create pertanyaan
                $soal = QuizQuestion::create([
                    'quiz_id' => $request->quiz_id,
                    'pertanyaan' => $questionData['pertanyaan'],
                    'poin' => $questionData['poin'] ?? 1,
                    'pembahasan' => $questionData['pembahasan'] ?? null,
                    'tingkat_kesulitan' => $questionData['tingkat_kesulitan'] ?? 'mudah',
                ]);

                // Create opsi jawaban
                foreach ($questionData['options'] as $index => $optionData) {
                    $soal->options()->create([
                        'teks_opsi' => $optionData['teks_opsi'],
                        'is_jawaban_benar' => $optionData['is_jawaban_benar'],
                        'urutan' => $index + 1,
                    ]);
                }

                $created++;
            }

            // Update quiz's question count
            $quiz = Quiz::findOrFail($request->quiz_id);
            $quiz->jumlah_soal = $quiz->soalQuiz()->count();
            $quiz->save();
        });

        return redirect()->route('quizzes.show', $request->quiz_id)
            ->with('success', "{$created} soal quiz berhasil dibuat");
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
