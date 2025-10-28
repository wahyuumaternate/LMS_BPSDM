<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\QuizOption;
use Modules\Quiz\Entities\QuizQuestion;
use Modules\Quiz\Transformers\QuizOptionResource;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Quiz Option",
 *     description="API Endpoints untuk manajemen opsi jawaban quiz"
 * )
 */
class QuizOptionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/quiz-options",
     *     summary="Mendapatkan daftar opsi jawaban quiz",
     *     tags={"Quiz Option"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="question_id",
     *         in="query",
     *         description="Filter berdasarkan ID pertanyaan",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar opsi jawaban berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="question_id", type="integer", example=1),
     *                     @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                     @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = QuizOption::with(['question']);

        // Filter by question_id
        if ($request->has('question_id')) {
            $query->where('question_id', $request->question_id);
        }

        $options = $query->orderBy('question_id')->orderBy('urutan')->get();

        return QuizOptionResource::collection($options);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/quiz-options",
     *     summary="Membuat opsi jawaban baru",
     *     tags={"Quiz Option"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question_id", "teks_opsi"},
     *             @OA\Property(property="question_id", type="integer", example=1),
     *             @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *             @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *             @OA\Property(property="urutan", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Opsi jawaban berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz option created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="question_id", type="integer", example=1),
     *                 @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                 @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:quiz_questions,id',
            'teks_opsi' => 'required|string',
            'is_jawaban_benar' => 'nullable|boolean',
            'urutan' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $option = QuizOption::create($request->all());

        return response()->json([
            'message' => 'Quiz option created successfully',
            'data' => new QuizOptionResource($option)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/quiz-options/{id}",
     *     summary="Mendapatkan detail opsi jawaban",
     *     tags={"Quiz Option"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID opsi jawaban",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail opsi jawaban berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="question_id", type="integer", example=1),
     *                 @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                 @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Opsi jawaban tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $option = QuizOption::with(['question'])->findOrFail($id);
        return new QuizOptionResource($option);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/quiz-options/{id}",
     *     summary="Mengupdate opsi jawaban",
     *     tags={"Quiz Option"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID opsi jawaban",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question_id", type="integer", example=1),
     *             @OA\Property(property="teks_opsi", type="string", example="Kumpulan data terstruktur"),
     *             @OA\Property(property="is_jawaban_benar", type="boolean", example=false),
     *             @OA\Property(property="urutan", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opsi jawaban berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz option updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="question_id", type="integer", example=1),
     *                 @OA\Property(property="teks_opsi", type="string", example="Kumpulan data terstruktur"),
     *                 @OA\Property(property="is_jawaban_benar", type="boolean", example=false),
     *                 @OA\Property(property="urutan", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Opsi jawaban tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $option = QuizOption::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'question_id' => 'sometimes|required|exists:quiz_questions,id',
            'teks_opsi' => 'sometimes|required|string',
            'is_jawaban_benar' => 'nullable|boolean',
            'urutan' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $option->update($request->all());

        return response()->json([
            'message' => 'Quiz option updated successfully',
            'data' => new QuizOptionResource($option)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/quiz-options/{id}",
     *     summary="Menghapus opsi jawaban",
     *     tags={"Quiz Option"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID opsi jawaban",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opsi jawaban berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz option deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Opsi jawaban tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $option = QuizOption::findOrFail($id);
        $option->delete();

        return response()->json([
            'message' => 'Quiz option deleted successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/quiz-options/bulk",
     *     summary="Membuat banyak opsi jawaban sekaligus untuk satu pertanyaan",
     *     tags={"Quiz Option"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question_id", "options"},
     *             @OA\Property(property="question_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="options",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"teks_opsi"},
     *                     @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                     @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                     @OA\Property(property="urutan", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Opsi jawaban berhasil dibuat secara bulk",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="4 quiz options created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="question_id", type="integer", example=1),
     *                     @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                     @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function bulkCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:quiz_questions,id',
            'options' => 'required|array|min:1',
            'options.*.teks_opsi' => 'required|string',
            'options.*.is_jawaban_benar' => 'nullable|boolean',
            'options.*.urutan' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $created = [];

        foreach ($request->options as $optionData) {
            $optionData['question_id'] = $request->question_id;
            $option = QuizOption::create($optionData);
            $created[] = $option;
        }

        return response()->json([
            'message' => count($created) . ' quiz options created successfully',
            'data' => QuizOptionResource::collection($created)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/quiz-options/question/{question_id}",
     *     summary="Mendapatkan semua opsi jawaban berdasarkan pertanyaan",
     *     tags={"Quiz Option"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="question_id",
     *         in="path",
     *         description="ID pertanyaan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar opsi jawaban berdasarkan pertanyaan berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="question_id", type="integer", example=1),
     *                     @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                     @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getByQuestion(Request $request, $questionId)
    {
        $validator = Validator::make(['question_id' => $questionId], [
            'question_id' => 'required|exists:quiz_questions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $options = QuizOption::where('question_id', $questionId)
            ->orderBy('urutan')
            ->get();

        return QuizOptionResource::collection($options);
    }
}
