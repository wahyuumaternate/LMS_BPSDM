<?php

namespace Modules\Forum\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Forum\Entities\Forum;
use Modules\Forum\Transformers\ForumResource;
use Modules\Kursus\Entities\Kursus;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Forum",
 *     description="API Endpoints untuk manajemen Forum Diskusi"
 * )
 */
class ForumController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/forum",
     *     summary="Mendapatkan daftar forum",
     *     tags={"Forum"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="query",
     *         description="Filter berdasarkan ID Kursus",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         description="Filter berdasarkan platform",
     *         required=false,
     *         @OA\Schema(type="string", enum={"telegram", "whatsapp", "other"})
     *     ),
     *     @OA\Parameter(
     *         name="is_aktif",
     *         in="query",
     *         description="Filter berdasarkan status aktif",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Pencarian berdasarkan judul forum",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar forum berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="kursus", type="object", 
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah")
     *                     ),
     *                     @OA\Property(property="judul", type="string", example="Forum Diskusi Kursus XYZ"),
     *                     @OA\Property(property="deskripsi", type="string", example="Forum untuk mendiskusikan materi kursus"),
     *                     @OA\Property(property="platform", type="string", example="telegram"),
     *                     @OA\Property(property="platform_text", type="string", example="Telegram"),
     *                     @OA\Property(property="link_grup", type="string", example="https://t.me/joinchat/abcdefg"),
     *                     @OA\Property(property="is_aktif", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-29T12:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-29T12:00:00.000000Z")
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
        $query = Forum::with('kursus');

        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->has('is_aktif')) {
            $query->where('is_aktif', $request->boolean('is_aktif'));
        }

        if ($request->has('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        $forumList = $query->orderBy('created_at', 'desc')->paginate(10);

        return ForumResource::collection($forumList);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/forum",
     *     summary="Membuat forum baru",
     *     tags={"Forum"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kursus_id", "platform", "link_grup"},
     *             @OA\Property(property="kursus_id", type="integer", example=1),
     *             @OA\Property(property="judul", type="string", example="Forum Diskusi Kursus XYZ"),
     *             @OA\Property(property="deskripsi", type="string", example="Forum untuk mendiskusikan materi kursus"),
     *             @OA\Property(property="platform", type="string", example="telegram", enum={"telegram", "whatsapp", "other"}),
     *             @OA\Property(property="link_grup", type="string", example="https://t.me/joinchat/abcdefg"),
     *             @OA\Property(property="is_aktif", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Forum berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forum created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Forum Diskusi Kursus XYZ"),
     *                 @OA\Property(property="deskripsi", type="string", example="Forum untuk mendiskusikan materi kursus"),
     *                 @OA\Property(property="platform", type="string", example="telegram"),
     *                 @OA\Property(property="platform_text", type="string", example="Telegram"),
     *                 @OA\Property(property="link_grup", type="string", example="https://t.me/joinchat/abcdefg"),
     *                 @OA\Property(property="is_aktif", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-29T12:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-29T12:00:00.000000Z")
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
            'kursus_id' => 'required|exists:kursus,id',
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'platform' => 'required|in:telegram,whatsapp,other',
            'link_grup' => 'required|string|max:255|url',
            'is_aktif' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Jika judul tidak diisi, gunakan judul kursus
        if (empty($request->judul)) {
            $kursus = Kursus::findOrFail($request->kursus_id);
            $request->merge(['judul' => "Forum " . $kursus->judul]);
        }

        $forum = Forum::create($request->all());

        return response()->json([
            'message' => 'Forum created successfully',
            'data' => new ForumResource($forum)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/forum/{id}",
     *     summary="Mendapatkan detail forum",
     *     tags={"Forum"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Forum",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail forum berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="kursus", type="object", 
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah")
     *                 ),
     *                 @OA\Property(property="judul", type="string", example="Forum Diskusi Kursus XYZ"),
     *                 @OA\Property(property="deskripsi", type="string", example="Forum untuk mendiskusikan materi kursus"),
     *                 @OA\Property(property="platform", type="string", example="telegram"),
     *                 @OA\Property(property="platform_text", type="string", example="Telegram"),
     *                 @OA\Property(property="link_grup", type="string", example="https://t.me/joinchat/abcdefg"),
     *                 @OA\Property(property="is_aktif", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-29T12:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-29T12:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Forum tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $forum = Forum::with('kursus')->findOrFail($id);
        return new ForumResource($forum);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/forum/{id}",
     *     summary="Mengupdate forum",
     *     tags={"Forum"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Forum",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="kursus_id", type="integer", example=1),
     *             @OA\Property(property="judul", type="string", example="Forum Diskusi Kursus XYZ"),
     *             @OA\Property(property="deskripsi", type="string", example="Forum untuk mendiskusikan materi kursus"),
     *             @OA\Property(property="platform", type="string", example="telegram", enum={"telegram", "whatsapp", "other"}),
     *             @OA\Property(property="link_grup", type="string", example="https://t.me/joinchat/abcdefg"),
     *             @OA\Property(property="is_aktif", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Forum berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forum updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="kursus_id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Forum Diskusi Kursus XYZ"),
     *                 @OA\Property(property="platform", type="string", example="telegram"),
     *                 @OA\Property(property="is_aktif", type="boolean", example=true)
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
     *         description="Forum tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $forum = Forum::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kursus_id' => 'sometimes|required|exists:kursus,id',
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'platform' => 'sometimes|required|in:telegram,whatsapp,other',
            'link_grup' => 'sometimes|required|string|max:255|url',
            'is_aktif' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $forum->update($request->all());

        return response()->json([
            'message' => 'Forum updated successfully',
            'data' => new ForumResource($forum)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/forum/{id}",
     *     summary="Menghapus forum",
     *     tags={"Forum"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Forum",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Forum berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forum deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Forum tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $forum = Forum::findOrFail($id);
        $forum->delete();

        return response()->json([
            'message' => 'Forum deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/kursus/{kursus_id}/forum",
     *     summary="Mendapatkan daftar forum berdasarkan kursus",
     *     tags={"Forum"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="kursus_id",
     *         in="path",
     *         description="ID Kursus",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar forum untuk kursus tertentu berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="judul", type="string", example="Forum Diskusi Kursus XYZ"),
     *                     @OA\Property(property="platform", type="string", example="telegram"),
     *                     @OA\Property(property="link_grup", type="string", example="https://t.me/joinchat/abcdefg")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="kursus",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Pengelolaan Keuangan Daerah")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kursus tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getByKursus($kursusId, Request $request)
    {
        // Verifikasi kursus
        $kursus = Kursus::findOrFail($kursusId);

        // Query forum
        $query = Forum::where('kursus_id', $kursusId);

        // Filter berdasarkan platform jika ada
        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }

        // Filter berdasarkan status aktif jika ada
        if ($request->has('is_aktif')) {
            $query->where('is_aktif', $request->boolean('is_aktif'));
        }

        // Pencarian berdasarkan judul jika ada
        if ($request->has('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Ambil data
        $forumList = $query->get();

        // Siapkan response
        $response = [
            'data' => ForumResource::collection($forumList),
            'kursus' => [
                'id' => $kursus->id,
                'judul' => $kursus->judul
            ]
        ];

        return response()->json($response);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/forum/{id}/toggle-status",
     *     summary="Mengaktifkan atau menonaktifkan forum",
     *     tags={"Forum"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Forum",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status forum berhasil diubah",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forum status toggled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="is_aktif", type="boolean", example=false),
     *                 @OA\Property(property="judul", type="string", example="Forum Diskusi Kursus XYZ")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Forum tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function toggleStatus($id)
    {
        $forum = Forum::findOrFail($id);
        $forum->is_aktif = !$forum->is_aktif;
        $forum->save();

        return response()->json([
            'message' => 'Forum status toggled successfully',
            'data' => new ForumResource($forum)
        ]);
    }
}
