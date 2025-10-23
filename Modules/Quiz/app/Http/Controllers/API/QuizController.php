<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Transformers\QuizResource;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $query = Quiz::with(['modul']);
        
        // Filter by modul_id
        if ($request->has('modul_id')) {
            $query->where('modul_id', $request->modul_id);
        }
        
        $quizzes = $query->get();
        
        return QuizResource::collection($quizzes);
    }

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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quiz = Quiz::create($request->all());

        return response()->json([
            'message' => 'Quiz created successfully',
            'data' => new QuizResource($quiz)
        ], 201);
    }

    public function show($id)
    {
        $quiz = Quiz::with(['modul', 'soalQuiz'])->findOrFail($id);
        return new QuizResource($quiz);
    }

    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'modul_id' => 'sometimes|required|exists:moduls,id',
            'judul_quiz' => 'sometimes|required|string|max:255',
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quiz->update($request->all());

        return response()->json([
            'message' => 'Quiz updated successfully',
            'data' => new QuizResource($quiz)
        ]);
    }

    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return response()->json([
            'message' => 'Quiz deleted successfully'
        ]);
    }
}
