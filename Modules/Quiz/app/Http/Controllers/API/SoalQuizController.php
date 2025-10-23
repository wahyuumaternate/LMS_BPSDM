<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\SoalQuiz;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Transformers\SoalQuizResource;
use Illuminate\Support\Facades\Validator;

class SoalQuizController extends Controller
{
    public function index(Request $request)
    {
        $query = SoalQuiz::with(['quiz']);

        // Filter by quiz_id
        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter by tingkat_kesulitan
        if ($request->has('tingkat_kesulitan')) {
            $query->where('tingkat_kesulitan', $request->tingkat_kesulitan);
        }

        $soalQuizzes = $query->get();

        return SoalQuizResource::collection($soalQuizzes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string',
            'pilihan_b' => 'required|string',
            'pilihan_c' => 'required|string',
            'pilihan_d' => 'required|string',
            'jawaban_benar' => 'required|in:a,b,c,d',
            'poin' => 'nullable|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create new question
        $soalQuiz = SoalQuiz::create($request->all());

        // Update quiz's question count
        $quiz = Quiz::findOrFail($request->quiz_id);
        $quiz->jumlah_soal = $quiz->soalQuiz()->count();
        $quiz->save();

        return response()->json([
            'message' => 'Soal quiz created successfully',
            'data' => new SoalQuizResource($soalQuiz)
        ], 201);
    }

    public function show($id)
    {
        $soalQuiz = SoalQuiz::with(['quiz'])->findOrFail($id);
        return new SoalQuizResource($soalQuiz);
    }

    public function update(Request $request, $id)
    {
        $soalQuiz = SoalQuiz::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quiz_id' => 'sometimes|required|exists:quizzes,id',
            'pertanyaan' => 'sometimes|required|string',
            'pilihan_a' => 'sometimes|required|string',
            'pilihan_b' => 'sometimes|required|string',
            'pilihan_c' => 'sometimes|required|string',
            'pilihan_d' => 'sometimes|required|string',
            'jawaban_benar' => 'sometimes|required|in:a,b,c,d',
            'poin' => 'nullable|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $soalQuiz->update($request->all());

        return response()->json([
            'message' => 'Soal quiz updated successfully',
            'data' => new SoalQuizResource($soalQuiz)
        ]);
    }

    public function destroy($id)
    {
        $soalQuiz = SoalQuiz::findOrFail($id);
        $quizId = $soalQuiz->quiz_id;

        $soalQuiz->delete();

        // Update quiz's question count
        $quiz = Quiz::findOrFail($quizId);
        $quiz->jumlah_soal = $quiz->soalQuiz()->count();
        $quiz->save();

        return response()->json([
            'message' => 'Soal quiz deleted successfully'
        ]);
    }

    public function bulkCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'soal' => 'required|array|min:1',
            'soal.*.pertanyaan' => 'required|string',
            'soal.*.pilihan_a' => 'required|string',
            'soal.*.pilihan_b' => 'required|string',
            'soal.*.pilihan_c' => 'required|string',
            'soal.*.pilihan_d' => 'required|string',
            'soal.*.jawaban_benar' => 'required|in:a,b,c,d',
            'soal.*.poin' => 'nullable|integer|min:1',
            'soal.*.pembahasan' => 'nullable|string',
            'soal.*.tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $created = [];

        foreach ($request->soal as $soalData) {
            $soalData['quiz_id'] = $request->quiz_id;
            $soal = SoalQuiz::create($soalData);
            $created[] = $soal;
        }

        // Update quiz's question count
        $quiz = Quiz::findOrFail($request->quiz_id);
        $quiz->jumlah_soal = $quiz->soalQuiz()->count();
        $quiz->save();

        return response()->json([
            'message' => count($created) . ' soal quiz created successfully',
            'data' => SoalQuizResource::collection($created)
        ], 201);
    }
}
