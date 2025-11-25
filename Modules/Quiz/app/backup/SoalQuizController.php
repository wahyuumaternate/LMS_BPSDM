<?php

namespace Modules\Quiz\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Quiz\Entities\Quiz;
use Modules\Quiz\Entities\QuizOption;
use Modules\Quiz\Entities\QuizQuestion;
use Modules\Quiz\Transformers\SoalQuizResource;
use Modules\Quiz\Transformers\QuizOptionResource;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Soal Quiz",
 *     description="API Endpoints untuk manajemen soal quiz dengan pendekatan relasional"
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
     *                     @OA\Property(property="poin", type="integer", example=5),
     *                     @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *                     @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="mudah"),
     *                     @OA\Property(
     *                         property="options",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="question_id", type="integer", example=1),
     *                             @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                             @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                             @OA\Property(property="urutan", type="integer", example=1)
     *                         )
     *                     ),
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
        $query = QuizQuestion::with(['quiz', 'options']);

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
     *     summary="Membuat soal quiz baru dengan opsi pilihan ganda",
     *     tags={"Soal Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quiz_id", "pertanyaan", "options"},
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(property="pertanyaan", type="string", example="Apa itu variable dalam pemrograman?"),
     *             @OA\Property(property="poin", type="integer", minimum=1, example=5),
     *             @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *             @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="mudah"),
     *             @OA\Property(
     *                 property="options",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"teks_opsi", "is_jawaban_benar"},
     *                     @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                     @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                     @OA\Property(property="urutan", type="integer", example=1)
     *                 )
     *             )
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
     *                 @OA\Property(property="poin", type="integer", example=5),
     *                 @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *                 @OA\Property(property="tingkat_kesulitan", type="string", example="mudah"),
     *                 @OA\Property(
     *                     property="options",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="question_id", type="integer", example=1),
     *                         @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                         @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                         @OA\Property(property="urutan", type="integer", example=1)
     *                     )
     *                 ),
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
        // Validasi untuk pertanyaan
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'pertanyaan' => 'required|string',
            'poin' => 'nullable|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',

            // Validasi untuk opsi jawaban
            'options' => 'required|array|min:2|max:5',
            'options.*.teks_opsi' => 'required|string',
            'options.*.is_jawaban_benar' => 'required|boolean',
            'options.*.urutan' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Pastikan hanya ada satu jawaban yang benar
        $correctCount = 0;
        foreach ($request->options as $option) {
            if (isset($option['is_jawaban_benar']) && $option['is_jawaban_benar']) {
                $correctCount++;
            }
        }

        if ($correctCount != 1) {
            return response()->json(['errors' => [
                'options' => ['Harus ada tepat satu jawaban benar']
            ]], 422);
        }

        // Gunakan transaksi untuk memastikan semua perubahan berhasil
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
            foreach ($request->options as $index => $optionData) {
                $urutan = $optionData['urutan'] ?? ($index + 1);

                $soalQuiz->options()->create([
                    'teks_opsi' => $optionData['teks_opsi'],
                    'is_jawaban_benar' => $optionData['is_jawaban_benar'],
                    'urutan' => $urutan,
                ]);
            }

            // Update jumlah_soal di quiz
            $quiz = Quiz::findOrFail($request->quiz_id);
            $quiz->jumlah_soal = $quiz->soalQuiz()->count();
            $quiz->save();

            // Load relasi options untuk response
            $soalQuiz->load('options');

            return response()->json([
                'message' => 'Soal quiz created successfully',
                'data' => new SoalQuizResource($soalQuiz)
            ], 201);
        });
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
     *                 @OA\Property(property="poin", type="integer", example=5),
     *                 @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *                 @OA\Property(property="tingkat_kesulitan", type="string", example="mudah"),
     *                 @OA\Property(
     *                     property="options",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="question_id", type="integer", example=1),
     *                         @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                         @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                         @OA\Property(property="urutan", type="integer", example=1)
     *                     )
     *                 ),
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
        $soalQuiz = QuizQuestion::with(['quiz', 'options'])->findOrFail($id);
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
     *             @OA\Property(property="poin", type="integer", minimum=1, example=10),
     *             @OA\Property(property="pembahasan", type="string", example="Array adalah struktur data untuk menyimpan kumpulan data"),
     *             @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="sedang"),
     *             @OA\Property(
     *                 property="options",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="teks_opsi", type="string", example="Kumpulan data"),
     *                     @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                     @OA\Property(property="urutan", type="integer", example=1)
     *                 )
     *             )
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
     *                 @OA\Property(property="poin", type="integer", example=10),
     *                 @OA\Property(property="pembahasan", type="string", example="Array adalah struktur data untuk menyimpan kumpulan data"),
     *                 @OA\Property(property="tingkat_kesulitan", type="string", example="sedang"),
     *                 @OA\Property(
     *                     property="options",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="question_id", type="integer", example=1),
     *                         @OA\Property(property="teks_opsi", type="string", example="Kumpulan data"),
     *                         @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                         @OA\Property(property="urutan", type="integer", example=1)
     *                     )
     *                 ),
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
            'poin' => 'nullable|integer|min:1',
            'pembahasan' => 'nullable|string',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',

            // Validasi untuk opsi jawaban
            'options' => 'sometimes|required|array|min:2|max:5',
            'options.*.id' => 'nullable|integer|exists:quiz_options,id',
            'options.*.teks_opsi' => 'required|string',
            'options.*.is_jawaban_benar' => 'required|boolean',
            'options.*.urutan' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Jika ada opsi, pastikan hanya satu jawaban benar
        if ($request->has('options')) {
            $correctCount = 0;
            foreach ($request->options as $option) {
                if (isset($option['is_jawaban_benar']) && $option['is_jawaban_benar']) {
                    $correctCount++;
                }
            }

            if ($correctCount != 1) {
                return response()->json(['errors' => [
                    'options' => ['Harus ada tepat satu jawaban benar']
                ]], 422);
            }
        }

        // Gunakan transaksi
        return DB::transaction(function () use ($request, $soalQuiz) {
            // Update pertanyaan
            $soalQuiz->update($request->only([
                'quiz_id',
                'pertanyaan',
                'poin',
                'pembahasan',
                'tingkat_kesulitan'
            ]));

            // Jika ada opsi baru, update atau buat
            if ($request->has('options')) {
                // Hapus semua opsi lama jika tidak ada ID yang diberikan
                $existingIds = collect($request->options)
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                // Hapus opsi yang tidak ada di request
                if (!empty($existingIds)) {
                    $soalQuiz->options()->whereNotIn('id', $existingIds)->delete();
                } else {
                    $soalQuiz->options()->delete(); // Hapus semua jika tidak ada ID
                }

                // Create atau update opsi
                foreach ($request->options as $index => $optionData) {
                    $urutan = $optionData['urutan'] ?? ($index + 1);

                    if (isset($optionData['id'])) {
                        // Update existing option
                        $option = QuizOption::find($optionData['id']);
                        if ($option && $option->question_id == $soalQuiz->id) {
                            $option->update([
                                'teks_opsi' => $optionData['teks_opsi'],
                                'is_jawaban_benar' => $optionData['is_jawaban_benar'],
                                'urutan' => $urutan,
                            ]);
                        }
                    } else {
                        // Create new option
                        $soalQuiz->options()->create([
                            'teks_opsi' => $optionData['teks_opsi'],
                            'is_jawaban_benar' => $optionData['is_jawaban_benar'],
                            'urutan' => $urutan,
                        ]);
                    }
                }
            }

            // Load relasi options untuk response
            $soalQuiz->load(['options', 'quiz']);

            return response()->json([
                'message' => 'Soal quiz updated successfully',
                'data' => new SoalQuizResource($soalQuiz)
            ]);
        });
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

        return DB::transaction(function () use ($soalQuiz, $quizId) {
            // Hapus semua opsi jawaban terlebih dahulu
            $soalQuiz->options()->delete();

            // Hapus pertanyaan
            $soalQuiz->delete();

            // Update quiz's question count
            $quiz = Quiz::findOrFail($quizId);
            $quiz->jumlah_soal = $quiz->soalQuiz()->count();
            $quiz->save();

            return response()->json([
                'message' => 'Soal quiz deleted successfully'
            ]);
        });
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
     *                     required={"pertanyaan", "options"},
     *                     @OA\Property(property="pertanyaan", type="string", example="Apa itu variable?"),
     *                     @OA\Property(property="poin", type="integer", minimum=1, example=5),
     *                     @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data"),
     *                     @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="mudah"),
     *                     @OA\Property(
     *                         property="options",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             required={"teks_opsi", "is_jawaban_benar"},
     *                             @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                             @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                             @OA\Property(property="urutan", type="integer", example=1)
     *                         )
     *                     )
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
     *                     @OA\Property(property="poin", type="integer", example=5),
     *                     @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data"),
     *                     @OA\Property(property="tingkat_kesulitan", type="string", example="mudah"),
     *                     @OA\Property(
     *                         property="options",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="question_id", type="integer", example=1),
     *                             @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                             @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                             @OA\Property(property="urutan", type="integer", example=1)
     *                         )
     *                     ),
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
            'soal.*.poin' => 'nullable|integer|min:1',
            'soal.*.pembahasan' => 'nullable|string',
            'soal.*.tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit',
            'soal.*.options' => 'required|array|min:2|max:5',
            'soal.*.options.*.teks_opsi' => 'required|string',
            'soal.*.options.*.is_jawaban_benar' => 'required|boolean',
            'soal.*.options.*.urutan' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validasi jawaban benar untuk semua soal
        foreach ($request->soal as $index => $soalData) {
            $correctCount = 0;
            foreach ($soalData['options'] as $option) {
                if ($option['is_jawaban_benar']) {
                    $correctCount++;
                }
            }

            if ($correctCount != 1) {
                return response()->json(['errors' => [
                    "soal.$index.options" => ['Harus ada tepat satu jawaban benar']
                ]], 422);
            }
        }

        return DB::transaction(function () use ($request) {
            $created = [];

            foreach ($request->soal as $soalData) {
                // Buat pertanyaan
                $soal = QuizQuestion::create([
                    'quiz_id' => $request->quiz_id,
                    'pertanyaan' => $soalData['pertanyaan'],
                    'poin' => $soalData['poin'] ?? 1,
                    'pembahasan' => $soalData['pembahasan'] ?? null,
                    'tingkat_kesulitan' => $soalData['tingkat_kesulitan'] ?? 'mudah',
                ]);

                // Buat opsi jawaban
                foreach ($soalData['options'] as $index => $optionData) {
                    $urutan = $optionData['urutan'] ?? ($index + 1);

                    $soal->options()->create([
                        'teks_opsi' => $optionData['teks_opsi'],
                        'is_jawaban_benar' => $optionData['is_jawaban_benar'],
                        'urutan' => $urutan,
                    ]);
                }

                $soal->load('options');
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
        });
    }

    /**
     * @OA\Get(
     *     path="/api/v1/soal-quiz/quiz/{quiz_id}",
     *     summary="Mendapatkan semua soal quiz berdasarkan quiz ID",
     *     tags={"Soal Quiz"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="quiz_id",
     *         in="path",
     *         description="ID quiz",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="random",
     *         in="query",
     *         description="Acak urutan soal (true/false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar soal quiz berdasarkan quiz ID berhasil diambil",
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
     *                     @OA\Property(property="poin", type="integer", example=5),
     *                     @OA\Property(property="pembahasan", type="string", example="Variable adalah tempat untuk menyimpan data dalam program"),
     *                     @OA\Property(property="tingkat_kesulitan", type="string", enum={"mudah", "sedang", "sulit"}, example="mudah"),
     *                     @OA\Property(
     *                         property="options",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="question_id", type="integer", example=1),
     *                             @OA\Property(property="teks_opsi", type="string", example="Tempat menyimpan data"),
     *                             @OA\Property(property="is_jawaban_benar", type="boolean", example=true),
     *                             @OA\Property(property="urutan", type="integer", example=1)
     *                         )
     *                     )
     *                 )
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
    public function getByQuiz(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        // Cek apakah perlu acak
        $random = filter_var($request->input('random', $quiz->random_soal), FILTER_VALIDATE_BOOLEAN);

        $query = QuizQuestion::with(['options' => function ($q) {
            $q->orderBy('urutan');
        }])->where('quiz_id', $quizId);

        // Terapkan pengacakan jika diperlukan
        if ($random) {
            $query->inRandomOrder();
        } else {
            // Jika tidak acak, urutkan berdasarkan ID atau urutan (jika ada)
            $query->orderBy('id');
        }

        // Ambil soal
        $soalQuizzes = $query->get();

        return SoalQuizResource::collection($soalQuizzes);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/soal-quiz/validate-options/{id}",
     *     summary="Memvalidasi opsi jawaban pada soal",
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
     *         description="Validasi berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="valid"),
     *             @OA\Property(property="message", type="string", example="Soal memiliki tepat satu jawaban benar"),
     *             @OA\Property(property="options_count", type="integer", example=4),
     *             @OA\Property(property="correct_options_count", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validasi gagal",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="invalid"),
     *             @OA\Property(property="message", type="string", example="Soal tidak memiliki jawaban benar"),
     *             @OA\Property(property="options_count", type="integer", example=4),
     *             @OA\Property(property="correct_options_count", type="integer", example=0)
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
    public function validateOptions($id)
    {
        $soalQuiz = QuizQuestion::with('options')->findOrFail($id);
        $options = $soalQuiz->options;
        $correctOptionsCount = $options->where('is_jawaban_benar', true)->count();

        $response = [
            'options_count' => $options->count(),
            'correct_options_count' => $correctOptionsCount,
        ];

        if ($options->count() < 2) {
            $response['status'] = 'invalid';
            $response['message'] = 'Soal harus memiliki minimal 2 opsi jawaban';
            return response()->json($response, 400);
        }

        if ($correctOptionsCount !== 1) {
            $response['status'] = 'invalid';
            $response['message'] = $correctOptionsCount === 0
                ? 'Soal tidak memiliki jawaban benar'
                : 'Soal memiliki lebih dari satu jawaban benar';
            return response()->json($response, 400);
        }

        $response['status'] = 'valid';
        $response['message'] = 'Soal memiliki tepat satu jawaban benar';

        return response()->json($response);
    }
}
