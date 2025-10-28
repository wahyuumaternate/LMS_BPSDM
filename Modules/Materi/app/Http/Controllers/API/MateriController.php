<?php

namespace Modules\Materi\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Materi\Entities\Materi;
use Modules\Materi\Transformers\MateriResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Materi Kursus",
 *     description="API endpoints for managing Materi Kursus (content)"
 * )
 */
class MateriController extends Controller
{
    /**
     * Get list of Materi Kursus
     * 
     * @OA\Get(
     *     path="/api/v1/materi",
     *     tags={"Materi Kursus"},
     *     summary="Get all Materi Kursus",
     *     description="Returns list of all Materi Kursus with optional filtering by module ID, content type, and published status",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="modul_id",
     *         in="query",
     *         description="Filter by module ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tipe_konten",
     *         in="query",
     *         description="Filter by content type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pdf", "doc", "video", "audio", "gambar", "link", "scorm"})
     *     ),
     *     @OA\Parameter(
     *         name="is_published",
     *         in="query",
     *         description="Filter by published status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="modul_id", type="integer", example=1),
     *                     @OA\Property(property="modul", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_modul", type="string", example="Pendahuluan Data Science"),
     *                         @OA\Property(property="kursus_id", type="integer", example=1)
     *                     ),
     *                     @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science"),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="tipe_konten", type="string", example="video", enum={"pdf", "doc", "video", "audio", "gambar", "link", "scorm"}),
     *                     @OA\Property(property="file_path", type="string", example="pengenalan-data-science-1234567890.mp4"),
     *                     @OA\Property(property="file_url", type="string", example="http://example.com/storage/materi/files/video/pengenalan-data-science-1234567890.mp4"),
     *                     @OA\Property(property="deskripsi", type="string", example="Video pengantar tentang konsep dasar data science"),
     *                     @OA\Property(property="durasi_menit", type="integer", example=45),
     *                     @OA\Property(property="ukuran_file", type="integer", example=25600),
     *                     @OA\Property(property="is_wajib", type="boolean", example=true),
     *                     @OA\Property(property="is_published", type="boolean", example=true),
     *                     @OA\Property(property="published_at", type="string", format="date-time", example="2025-10-20T14:30:00.000000Z"),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching materials")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Materi::with(['modul']);

            // Filter by modul_id
            if ($request->has('modul_id')) {
                $query->where('modul_id', $request->modul_id);
            }

            // Filter by tipe_konten
            if ($request->has('tipe_konten') && in_array($request->tipe_konten, ['pdf', 'doc', 'video', 'audio', 'gambar', 'link', 'scorm'])) {
                $query->where('tipe_konten', $request->tipe_konten);
            }

            // Filter by published_at
            if ($request->has('is_published')) {
                if ($request->boolean('is_published')) {
                    $query->whereNotNull('published_at');
                } else {
                    $query->whereNull('published_at');
                }
            }

            // Order by urutan
            $query->orderBy('urutan');

            $materis = $query->get();

            return MateriResource::collection($materis);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching materials: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new course material
     * 
     * @OA\Post(
     *     path="/api/v1/materi",
     *     tags={"Materi Kursus"},
     *     summary="Create new course material",
     *     description="Create a new material (content) for a module, supports both JSON and multipart/form-data",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"modul_id", "judul_materi", "tipe_konten"},
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science"),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(
     *                     property="tipe_konten", 
     *                     type="string", 
     *                     enum={"pdf", "doc", "video", "audio", "gambar", "link", "scorm"}, 
     *                     example="video"
     *                 ),
     *                 @OA\Property(
     *                     property="file", 
     *                     type="string",
     *                     format="binary",
     *                     description="Required unless tipe_konten is 'link'. Max size: 100MB"
     *                 ),
     *                 @OA\Property(
     *                     property="file_url", 
     *                     type="string", 
     *                     example="https://example.com/videos/data-science-intro.mp4",
     *                     description="Required if tipe_konten is 'link'"
     *                 ),
     *                 @OA\Property(property="deskripsi", type="string", example="Video pengantar tentang konsep dasar data science"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=45),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="is_published", type="boolean", example=false)
     *             )
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"modul_id", "judul_materi", "tipe_konten"},
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science"),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(
     *                     property="tipe_konten", 
     *                     type="string", 
     *                     enum={"pdf", "doc", "video", "audio", "gambar", "link", "scorm"}, 
     *                     example="link"
     *                 ),
     *                 @OA\Property(
     *                     property="file_url", 
     *                     type="string", 
     *                     example="https://example.com/videos/data-science-intro.mp4",
     *                     description="Required if tipe_konten is 'link'"
     *                 ),
     *                 @OA\Property(property="deskripsi", type="string", example="Materi tentang konsep dasar data science"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=45),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="is_published", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Material created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Materi created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science"),
     *                 @OA\Property(property="tipe_konten", type="string", example="video"),
     *                 @OA\Property(property="file_path", type="string", example="pengenalan-data-science-1234567890.mp4"),
     *                 @OA\Property(property="file_url", type="string", example="http://example.com/storage/materi/files/video/pengenalan-data-science-1234567890.mp4"),
     *                 @OA\Property(property="deskripsi", type="string", example="Video pengantar tentang konsep dasar data science"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=45),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="is_published", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating material")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'modul_id' => 'required|exists:moduls,id',
                'judul_materi' => 'required|string|max:255',
                'urutan' => 'nullable|integer|min:0',
                'tipe_konten' => 'required|in:pdf,doc,video,audio,gambar,link,scorm',
                'file' => 'nullable:tipe_konten,link|file|max:102400', // 100MB max
                'file_url' => 'nullable:tipe_konten,link|url',
                'deskripsi' => 'nullable|string',
                'durasi_menit' => 'nullable|integer|min:0',
                // 'is_wajib' => 'nullable|boolean',
                // 'is_published' => 'nullable|boolean',
            ]);

            if ($request->has('is_wajib')) {
                $data['is_wajib'] = filter_var($request->is_wajib, FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('is_published')) {
                $data['is_published'] = filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN);
            }
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->except(['file', 'file_url', 'is_published']);

            // If urutan not provided, set it to the last position
            if (!$request->filled('urutan')) {
                $lastUrutan = Materi::where('modul_id', $request->modul_id)
                    ->max('urutan');
                $data['urutan'] = ($lastUrutan ?? 0) + 1;
            }

            // Handle file upload or external link
            if ($request->tipe_konten === 'link') {
                $data['file_path'] = $request->file_url;
            } else if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug($request->judul_materi) . '-' . time() . '.' . $extension;

                $folder = 'public/materi/files/' . $request->tipe_konten;
                $file->storeAs($folder, $filename);

                $data['file_path'] = $filename;
                $data['ukuran_file'] = round($file->getSize() / 1024); // convert to KB
            }

            // Set published_at if needed
            if ($request->has('is_published') && $request->boolean('is_published')) {
                $data['published_at'] = now();
            }

            $materi = Materi::create($data);
            $materi->load(['modul']);

            return response()->json([
                'message' => 'Materi created successfully',
                'data' => new MateriResource($materi)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific course material by ID
     * 
     * @OA\Get(
     *     path="/api/v1/materi/{id}",
     *     tags={"Materi Kursus"},
     *     summary="Get course material by ID",
     *     description="Returns specific course material details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Material ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="modul", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_modul", type="string", example="Pendahuluan Data Science"),
     *                     @OA\Property(property="kursus_id", type="integer", example=1),
     *                     @OA\Property(property="urutan", type="integer", example=1),
     *                     @OA\Property(property="is_published", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science"),
     *                 @OA\Property(property="urutan", type="integer", example=1),
     *                 @OA\Property(property="tipe_konten", type="string", example="video"),
     *                 @OA\Property(property="file_path", type="string", example="pengenalan-data-science-1234567890.mp4"),
     *                 @OA\Property(property="file_url", type="string", example="http://example.com/storage/materi/files/video/pengenalan-data-science-1234567890.mp4"),
     *                 @OA\Property(property="deskripsi", type="string", example="Video pengantar tentang konsep dasar data science"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=45),
     *                 @OA\Property(property="ukuran_file", type="integer", example=25600),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="is_published", type="boolean", example=true),
     *                 @OA\Property(property="published_at", type="string", format="date-time", example="2025-10-20T14:30:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Material not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Material not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error retrieving material")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $materi = Materi::with(['modul'])->find($id);

            if (!$materi) {
                return response()->json(['message' => 'Material not found'], 404);
            }

            return new MateriResource($materi);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving material: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update course material
     * 
     * @OA\Post(
     *     path="/api/v1/materi/{id}",
     *     tags={"Materi Kursus"},
     *     summary="Update course material",
     *     description="Update an existing course material with file handling support",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Material ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT", description="Method spoofing"),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science - Updated"),
     *                 @OA\Property(property="urutan", type="integer", example=2),
     *                 @OA\Property(
     *                     property="tipe_konten", 
     *                     type="string", 
     *                     enum={"pdf", "doc", "video", "audio", "gambar", "link", "scorm"}, 
     *                     example="video"
     *                 ),
     *                 @OA\Property(
     *                     property="file", 
     *                     type="file",
     *                     format="binary",
     *                     description="Only required if changing the file"
     *                 ),
     *                 @OA\Property(
     *                     property="file_url", 
     *                     type="string", 
     *                     example="https://example.com/videos/new-data-science-intro.mp4",
     *                     description="Required if tipe_konten is 'link'"
     *                 ),
     *                 @OA\Property(property="deskripsi", type="string", example="Video pengantar yang diperbarui tentang konsep dasar data science"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=50),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="is_published", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Material updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Materi updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="modul", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_modul", type="string", example="Pendahuluan Data Science"),
     *                     @OA\Property(property="kursus_id", type="integer", example=1)
     *                 ),
     *                 @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science - Updated"),
     *                 @OA\Property(property="urutan", type="integer", example=2),
     *                 @OA\Property(property="tipe_konten", type="string", example="video"),
     *                 @OA\Property(property="file_path", type="string", example="pengenalan-data-science-updated-9876543210.mp4"),
     *                 @OA\Property(property="file_url", type="string", example="http://example.com/storage/materi/files/video/pengenalan-data-science-updated-9876543210.mp4"),
     *                 @OA\Property(property="deskripsi", type="string", example="Video pengantar yang diperbarui tentang konsep dasar data science"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=50),
     *                 @OA\Property(property="ukuran_file", type="integer", example=30720),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="is_published", type="boolean", example=true),
     *                 @OA\Property(property="published_at", type="string", format="date-time", example="2025-10-25T15:45:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 15:45:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Material not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Material not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating material")
     *         )
     *     )
     * )
     */
    /**
     * Update course material
     * 
     * @OA\Put(
     *     path="/api/v1/materi/{id}",
     *     tags={"Materi Kursus"},
     *     summary="Update course material (JSON)",
     *     description="Update an existing course material using JSON (for non-file updates)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Material ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="modul_id", type="integer", example=1),
     *             @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science - Updated"),
     *             @OA\Property(property="urutan", type="integer", example=2),
     *             @OA\Property(
     *                 property="tipe_konten", 
     *                 type="string", 
     *                 enum={"pdf", "doc", "video", "audio", "gambar", "link", "scorm"}, 
     *                 example="video"
     *             ),
     *             @OA\Property(
     *                 property="file_url", 
     *                 type="string", 
     *                 example="https://example.com/videos/new-data-science-intro.mp4",
     *                 description="Required if tipe_konten is 'link'"
     *             ),
     *             @OA\Property(property="deskripsi", type="string", example="Video pengantar yang diperbarui tentang konsep dasar data science"),
     *             @OA\Property(property="durasi_menit", type="integer", example=50),
     *             @OA\Property(property="is_wajib", type="boolean", example=true),
     *             @OA\Property(property="is_published", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Material updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Materi updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="modul_id", type="integer", example=1),
     *                 @OA\Property(property="modul", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_modul", type="string", example="Pendahuluan Data Science"),
     *                     @OA\Property(property="kursus_id", type="integer", example=1)
     *                 ),
     *                 @OA\Property(property="judul_materi", type="string", example="Pengenalan Data Science - Updated"),
     *                 @OA\Property(property="urutan", type="integer", example=2),
     *                 @OA\Property(property="tipe_konten", type="string", example="video"),
     *                 @OA\Property(property="file_path", type="string", example="pengenalan-data-science-updated-9876543210.mp4"),
     *                 @OA\Property(property="file_url", type="string", example="http://example.com/storage/materi/files/video/pengenalan-data-science-updated-9876543210.mp4"),
     *                 @OA\Property(property="deskripsi", type="string", example="Video pengantar yang diperbarui tentang konsep dasar data science"),
     *                 @OA\Property(property="durasi_menit", type="integer", example=50),
     *                 @OA\Property(property="ukuran_file", type="integer", example=30720),
     *                 @OA\Property(property="is_wajib", type="boolean", example=true),
     *                 @OA\Property(property="is_published", type="boolean", example=true),
     *                 @OA\Property(property="published_at", type="string", format="date-time", example="2025-10-25T15:45:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 15:45:00")
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $materi = Materi::find($id);

            if (!$materi) {
                return response()->json(['message' => 'Material not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'modul_id' => 'sometimes|required|exists:moduls,id',
                'judul_materi' => 'sometimes|required|string|max:255',
                'urutan' => 'nullable|integer|min:0',
                'tipe_konten' => 'sometimes|required|in:pdf,doc,video,audio,gambar,link,scorm',
                'file' => 'nullable|file|max:102400', // 100MB max
                'file_url' => 'required_if:tipe_konten,link|url',
                'deskripsi' => 'nullable|string',
                'durasi_menit' => 'nullable|integer|min:0',
                'is_wajib' => 'nullable|boolean',
                'is_published' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->except(['file', 'file_url', 'is_published']);

            // Handle file upload or external link
            if ($request->filled('tipe_konten') && $request->tipe_konten === 'link' && $request->filled('file_url')) {
                $data['file_path'] = $request->file_url;
            } else if ($request->hasFile('file')) {
                // Delete old file if not a link
                if ($materi->tipe_konten !== 'link' && $materi->file_path) {
                    Storage::delete('public/materi/files/' . $materi->tipe_konten . '/' . $materi->file_path);
                }

                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug($request->judul_materi ?? $materi->judul_materi) . '-' . time() . '.' . $extension;

                $folder = 'public/materi/files/' . ($request->tipe_konten ?? $materi->tipe_konten);
                $file->storeAs($folder, $filename);

                $data['file_path'] = $filename;
                $data['ukuran_file'] = round($file->getSize() / 1024); // convert to KB
            }

            // Set published_at if needed
            if ($request->has('is_published')) {
                if ($request->boolean('is_published') && !$materi->published_at) {
                    $data['published_at'] = now();
                } else if (!$request->boolean('is_published')) {
                    $data['published_at'] = null;
                }
            }

            $materi->update($data);
            $materi->load(['modul']);

            return response()->json([
                'message' => 'Materi updated successfully',
                'data' => new MateriResource($materi)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete course material
     * 
     * @OA\Delete(
     *     path="/api/v1/materi/{id}",
     *     tags={"Materi Kursus"},
     *     summary="Delete course material",
     *     description="Delete a course material and its associated file (if exists)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Material ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Material deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Materi deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Material not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Material not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error deleting material")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $materi = Materi::find($id);

            if (!$materi) {
                return response()->json(['message' => 'Material not found'], 404);
            }

            // Delete file if not a link
            if ($materi->tipe_konten !== 'link' && $materi->file_path) {
                Storage::delete('public/materi/files/' . $materi->tipe_konten . '/' . $materi->file_path);
            }

            $materi->delete();

            return response()->json([
                'message' => 'Materi deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder Materi Kursus
     * 
     * @OA\Post(
     *     path="/api/v1/materi/reorder",
     *     tags={"Materi Kursus"},
     *     summary="Reorder Materi Kursus",
     *     description="Update the order/sequence of multiple materials within a module at once",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"modul_id","materis"},
     *             @OA\Property(property="modul_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="materis",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id","urutan"},
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="urutan", type="integer", example=3)
     *                 ),
     *                 example={
     *                     {"id": 1, "urutan": 3},
     *                     {"id": 2, "urutan": 1},
     *                     {"id": 3, "urutan": 2}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Materials reordered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Materis reordered successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error reordering materials")
     *         )
     *     )
     * )
     */
    public function reorder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'modul_id' => 'required|exists:moduls,id',
                'materis' => 'required|array',
                'materis.*.id' => 'required|exists:materis,id',
                'materis.*.urutan' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if all materis belong to the specified modul
            $materiIds = collect($request->materis)->pluck('id')->toArray();
            $invalidMateris = Materi::whereIn('id', $materiIds)
                ->where('modul_id', '!=', $request->modul_id)
                ->exists();

            if ($invalidMateris) {
                return response()->json([
                    'message' => 'Some materis do not belong to the specified modul.'
                ], 422);
            }

            // Update urutan for each materi
            foreach ($request->materis as $materi) {
                Materi::where('id', $materi['id'])
                    ->update(['urutan' => $materi['urutan']]);
            }

            return response()->json([
                'message' => 'Materis reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error reordering materials: ' . $e->getMessage()
            ], 500);
        }
    }
}
