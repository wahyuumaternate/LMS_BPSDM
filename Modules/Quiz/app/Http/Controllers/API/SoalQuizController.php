<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\SoalQuiz;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Transformers\SoalQuizResource;
use Illuminate\Support\Facades\Validator;
use Modules\Quiz\Entities\QuizQuestion;

/**
 * @OA\Tag(
 *     name="Soal Quiz",
 *     description="API Endpoints untuk manajemen soal quiz"
 * )
 */
class SoalQuizController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/soal-quiz",
     *     summary="Mendapatkan daftar soal quiz",
     *     tags={"Soal Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="quiz_id",
     *         in="query",
     *         description="Filter berdasarkan ID quiz",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tingkat_kesulitan",
     *         in="query",
     *         description="Filter berdasarkan tingkat kesulitan",
     *         required=false,
     *         @OA\Schema(type="string", enum={"mudah", "sedang", "sulit"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar soal quiz berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="quiz_id", type="integer", example=1),
     *                     @OA\Property(property="pertanyaan", type="string", example="Apa itu variable dalam pemrograman?"),
     *                     @OA\Property(property="pilihan_a", type="string", example="Tempat menyimpan data"),
     *                     @OA\Property(property="pilihan_b", type="string", example="Fungsi matematika"),
     *                     @OA\Property(property="pilihan_c", type="string", example="Tipe data"),
     *                     @OA\Property(property="pilihan_d", type="string", example="Operator"),
     *                     @OA\Property(property="jawaban_benar", type="string", enum={"a", "b", "c", "d"}, example="a"),
     *                     @OA\Property(property="poin", type="integer", example=5),
     *                     @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *                     @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="mudah"),
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
        $query = QuizQuestion::with(['quiz']);

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

    /**
     * @OA\Post(
     *     path="/api/v1/soal-quiz",
     *     summary="Membuat soal quiz baru",
     *     tags={"Soal Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quiz_id", "pertanyaan", "pilihan_a", "pilihan_b", "pilihan_c", "pilihan_d", "jawaban_benar"},
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(property="pertanyaan", type="string", example="Apa itu variable dalam pemrograman?"),
     *             @OA\Property(property="pilihan_a", type="string", example="Tempat menyimpan data"),
     *             @OA\Property(property="pilihan_b", type="string", example="Fungsi matematika"),
     *             @OA\Property(property="pilihan_c", type="string", example="Tipe data"),
     *             @OA\Property(property="pilihan_d", type="string", example="Operator"),
     *             @OA\Property(property="jawaban_benar", type="string", enum={"a", "b", "c", "d"}, example="a"),
     *             @OA\Property(property="poin", type="integer", minimum=1, example=5),
     *             @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *             @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="mudah")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Soal quiz berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Soal quiz created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="quiz_id", type="integer", example=1),
     *                 @OA\Property(property="pertanyaan", type="string", example="Apa itu variable dalam pemrograman?"),
     *                 @OA\Property(property="pilihan_a", type="string", example="Tempat menyimpan data"),
     *                 @OA\Property(property="pilihan_b", type="string", example="Fungsi matematika"),
     *                 @OA\Property(property="pilihan_c", type="string", example="Tipe data"),
     *                 @OA\Property(property="pilihan_d", type="string", example="Operator"),
     *                 @OA\Property(property="jawaban_benar", type="string", example="a"),
     *                 @OA\Property(property="poin", type="integer", example=5),
     *                 @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *                 @OA\Property(property="tingkat_kesulitan", type="string", example="mudah"),
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
        $soalQuiz = QuizQuestion::create($request->all());

        // Update quiz's question count
        $quiz = Quiz::findOrFail($request->quiz_id);
        $quiz->jumlah_soal = $quiz->soalQuiz()->count();
        $quiz->save();

        return response()->json([
            'message' => 'Soal quiz created successfully',
            'data' => new SoalQuizResource($soalQuiz)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/soal-quiz/{id}",
     *     summary="Mendapatkan detail soal quiz",
     *     tags={"Soal Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID soal quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail soal quiz berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="quiz_id", type="integer", example=1),
     *                 @OA\Property(property="pertanyaan", type="string", example="Apa itu variable dalam pemrograman?"),
     *                 @OA\Property(property="pilihan_a", type="string", example="Tempat menyimpan data"),
     *                 @OA\Property(property="pilihan_b", type="string", example="Fungsi matematika"),
     *                 @OA\Property(property="pilihan_c", type="string", example="Tipe data"),
     *                 @OA\Property(property="pilihan_d", type="string", example="Operator"),
     *                 @OA\Property(property="jawaban_benar", type="string", example="a"),
     *                 @OA\Property(property="poin", type="integer", example=5),
     *                 @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *                 @OA\Property(property="tingkat_kesulitan", type="string", example="mudah"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Soal quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $soalQuiz = QuizQuestion::with(['quiz'])->findOrFail($id);
        return new SoalQuizResource($soalQuiz);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/soal-quiz/{id}",
     *     summary="Mengupdate soal quiz",
     *     tags={"Soal Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID soal quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(property="pertanyaan", type="string", example="Apa itu array dalam pemrograman?"),
     *             @OA\Property(property="pilihan_a", type="string", example="Kumpulan data"),
     *             @OA\Property(property="pilihan_b", type="string", example="Fungsi matematika"),
     *             @OA\Property(property="pilihan_c", type="string", example="Tipe data"),
     *             @OA\Property(property="pilihan_d", type="string", example="Operator"),
     *             @OA\Property(property="jawaban_benar", type="string", enum={"a", "b", "c", "d"}, example="a"),
     *             @OA\Property(property="poin", type="integer", minimum=1, example=10),
     *             @OA\Property(property="pembahasan", type="string", example="Array adalah struktur data untuk menyimpan kumpulan data"),
     *             @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="sedang")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Soal quiz berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Soal quiz updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="quiz_id", type="integer", example=1),
     *                 @OA\Property(property="pertanyaan", type="string", example="Apa itu array dalam pemrograman?"),
     *                 @OA\Property(property="pilihan_a", type="string", example="Kumpulan data"),
     *                 @OA\Property(property="pilihan_b", type="string", example="Fungsi matematika"),
     *                 @OA\Property(property="pilihan_c", type="string", example="Tipe data"),
     *                 @OA\Property(property="pilihan_d", type="string", example="Operator"),
     *                 @OA\Property(property="jawaban_benar", type="string", example="a"),
     *                 @OA\Property(property="poin", type="integer", example=10),
     *                 @OA\Property(property="pembahasan", type="string", example="Array adalah struktur data untuk menyimpan kumpulan data"),
     *                 @OA\Property(property="tingkat_kesulitan", type="string", example="sedang"),
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
     *         description="Soal quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $soalQuiz = QuizQuestion::findOrFail($id);

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

    /**
     * @OA\Delete(
     *     path="/api/v1/soal-quiz/{id}",
     *     summary="Menghapus soal quiz",
     *     tags={"Soal Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID soal quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Soal quiz berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Soal quiz deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Soal quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $soalQuiz = QuizQuestion::findOrFail($id);
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

    /**
     * @OA\Post(
     *     path="/api/v1/soal-quiz/bulk",
     *     summary="Membuat banyak soal quiz sekaligus",
     *     tags={"Soal Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quiz_id", "soal"},
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="soal",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"pertanyaan", "pilihan_a", "pilihan_b", "pilihan_c", "pilihan_d", "jawaban_benar"},
     *                     @OA\Property(property="pertanyaan", type="string", example="Apa itu variable?"),
     *                     @OA\Property(property="pilihan_a", type="string", example="Tempat menyimpan data"),
     *                     @OA\Property(property="pilihan_b", type="string", example="Fungsi matematika"),
     *                     @OA\Property(property="pilihan_c", type="string", example="Tipe data"),
     *                     @OA\Property(property="pilihan_d", type="string", example="Operator"),
     *                     @OA\Property(property="jawaban_benar", type="string", enum={"a", "b", "c", "d"}, example="a"),
     *                     @OA\Property(property="poin", type="integer", minimum=1, example=5),
     *                     @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data"),
     *                     @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="mudah")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Soal quiz berhasil dibuat secara bulk",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="5 soal quiz created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="quiz_id", type="integer", example=1),
     *                     @OA\Property(property="pertanyaan", type="string", example="Apa itu variable?"),
     *                     @OA\Property(property="pilihan_a", type="string", example="Tempat menyimpan data"),
     *                     @OA\Property(property="pilihan_b", type="string", example="Fungsi matematika"),
     *                     @OA\Property(property="pilihan_c", type="string", example="Tipe data"),
     *                     @OA\Property(property="pilihan_d", type="string", example="Operator"),
     *                     @OA\Property(property="jawaban_benar", type="string", example="a"),
     *                     @OA\Property(property="poin", type="integer", example=5),
     *                     @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data"),
     *                     @OA\Property(property="tingkat_kesulitan", type="string", example="mudah"),
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
            $soal = QuizQuestion::create($soalData);
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
