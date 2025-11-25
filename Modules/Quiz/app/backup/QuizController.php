<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Transformers\QuizResource;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Quiz",
 *     description="API Endpoints untuk manajemen Quiz"
 * )
 */
class QuizController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/quizzes",
     *     summary="Mendapatkan daftar quiz",
     *     tags={"Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="modul_id",
     *         in="query",
     *         description="Filter berdasarkan ID modul",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar quiz berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="modul_id", type="integer", example=1),
     *                     @OA\Property(property="judul_quiz", type="string", example="Quiz Pemrograman Dasar"),
     *                     @OA\Property(property="deskripsi", type="string", example="Quiz untuk menguji pemahaman dasar pemrograman"),
     *                     @OA\Property(property="durasi_menit", type="integer", example=60),
     *                     @OA\Property(property="bobot_nilai", type="number", format="float", example=20.5),
     *                     @OA\Property(property="passing_grade", type="integer", example=70),
     *                     @OA\Property(property="jumlah_soal", type="integer", example=10),
     *                     @OA\Property(property="random_soal", type="boolean", example=true),
     *                     @OA\Property(property="tampilkan_hasil", type="boolean", example=true),
     *                     @OA\Property(property="max_attempt", type="integer", example=3),
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
        $query = Quiz::with(['modul']);

        // Filter by modul_id
        if ($request->has('modul_id')) {
            $query->where('modul_id', $request->modul_id);
        }

        $quizzes = $query->get();

        return QuizResource::collection($quizzes);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/quizzes",
     *     summary="Membuat quiz baru",
     *     tags={"Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"modul_id", "judul_quiz"},
     *             @OA\Property(property="modul_id", type="integer", example=1),
     *             @OA\Property(property="judul_quiz", type="string", maxLength=255, example="Quiz Pemrograman Dasar"),
     *             @OA\Property(property="deskripsi", type="string", example="Quiz untuk menguji pemahaman dasar pemrograman"),
     *             @OA\Property(property="durasi_menit", type="integer", minimum=1, example=60),
     *             @OA\Property(property="bobot_nilai", type="number", format="float", minimum=0.01, maximum=100, example=20.5),
     *             @OA\Property(property="passing_grade", type="integer", minimum=1, maximum=100, example=70),
     *             @OA\Property(property="jumlah_soal", type="integer", minimum=0, example=10),
     *             @OA\Property(property="random_soal", type="boolean", example=true),
     *             @OA\Property(property="tampilkan_hasil", type="boolean", example=true),
     *             @OA\Property(property="max_attempt", type="integer", minimum=0, example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Quiz berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul_quiz", type="string", example="Quiz Pemrograman Dasar"),
     *                 @OA\Property(property="deskripsi", type="string", example="Quiz untuk menguji pemahaman dasar pemrograman"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=60),
     *                 @OA\Property(property="bobot_nilai", type="number", format="float", example=20.5),
     *                 @OA\Property(property="passing_grade", type="integer", example=70),
     *                 @OA\Property(property="jumlah_soal", type="integer", example=10),
     *                 @OA\Property(property="random_soal", type="boolean", example=true),
     *                 @OA\Property(property="tampilkan_hasil", type="boolean", example=true),
     *                 @OA\Property(property="max_attempt", type="integer", example=3),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
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

    /**
     * @OA\Get(
     *     path="/api/v1/quizzes/{id}",
     *     summary="Mendapatkan detail quiz",
     *     tags={"Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail quiz berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul_quiz", type="string", example="Quiz Pemrograman Dasar"),
     *                 @OA\Property(property="deskripsi", type="string", example="Quiz untuk menguji pemahaman dasar pemrograman"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=60),
     *                 @OA\Property(property="bobot_nilai", type="number", format="float", example=20.5),
     *                 @OA\Property(property="passing_grade", type="integer", example=70),
     *                 @OA\Property(property="jumlah_soal", type="integer", example=10),
     *                 @OA\Property(property="random_soal", type="boolean", example=true),
     *                 @OA\Property(property="tampilkan_hasil", type="boolean", example=true),
     *                 @OA\Property(property="max_attempt", type="integer", example=3),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $quiz = Quiz::with(['modul', 'soalQuiz'])->findOrFail($id);
        return new QuizResource($quiz);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/quizzes/{id}",
     *     summary="Mengupdate quiz",
     *     tags={"Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="modul_id", type="integer", example=1),
     *             @OA\Property(property="judul_quiz", type="string", maxLength=255, example="Quiz Pemrograman Lanjut"),
     *             @OA\Property(property="deskripsi", type="string", example="Quiz untuk menguji pemahaman lanjut pemrograman"),
     *             @OA\Property(property="durasi_menit", type="integer", minimum=1, example=90),
     *             @OA\Property(property="bobot_nilai", type="number", format="float", minimum=0.01, maximum=100, example=25.5),
     *             @OA\Property(property="passing_grade", type="integer", minimum=1, maximum=100, example=75),
     *             @OA\Property(property="jumlah_soal", type="integer", minimum=0, example=15),
     *             @OA\Property(property="random_soal", type="boolean", example=false),
     *             @OA\Property(property="tampilkan_hasil", type="boolean", example=false),
     *             @OA\Property(property="max_attempt", type="integer", minimum=0, example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul_quiz", type="string", example="Quiz Pemrograman Lanjut"),
     *                 @OA\Property(property="deskripsi", type="string", example="Quiz untuk menguji pemahaman lanjut pemrograman"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=90),
     *                 @OA\Property(property="bobot_nilai", type="number", format="float", example=25.5),
     *                 @OA\Property(property="passing_grade", type="integer", example=75),
     *                 @OA\Property(property="jumlah_soal", type="integer", example=15),
     *                 @OA\Property(property="random_soal", type="boolean", example=false),
     *                 @OA\Property(property="tampilkan_hasil", type="boolean", example=false),
     *                 @OA\Property(property="max_attempt", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/quizzes/{id}",
     *     summary="Menghapus quiz",
     *     tags={"Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quiz deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Quiz tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return response()->json([
            'message' => 'Quiz deleted successfully'
        ]);
    }
}
