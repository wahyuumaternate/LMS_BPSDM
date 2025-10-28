<?php

namespace Modules\Peserta\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Peserta\Entities\Peserta;
use Modules\Peserta\Transformers\PesertaResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PesertaController extends Controller
{
    /**
     * Get paginated list of Peserta
     * 
     * @OA\Get(
     *     path="/api/v1/peserta",
     *     tags={"Peserta"},
     *     summary="Get all Peserta",
     *     description="Returns paginated list of all Peserta with their OPD",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter results (searches username, email, nama_lengkap and nip)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status_kepegawaian",
     *         in="query",
     *         description="Filter by employment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pns", "pppk", "kontrak"})
     *     ),
     *     @OA\Parameter(
     *         name="opd_id",
     *         in="query",
     *         description="Filter by OPD ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="opd_id", type="integer", example=1),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                     @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                     @OA\Property(property="pangkat_golongan", type="string", example="III/a"),
     *                     @OA\Property(property="jabatan", type="string", example="Staff"),
     *                     @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *                     @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *                     @OA\Property(property="jenis_kelamin", type="string", example="laki_laki"),
     *                     @OA\Property(property="pendidikan_terakhir", type="string", example="s1"),
     *                     @OA\Property(property="status_kepegawaian", type="string", example="pns"),
     *                     @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                     @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                     @OA\Property(property="foto_profil", type="string", example="1698304599.jpg"),
     *                     @OA\Property(property="opd", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan"),
     *                         @OA\Property(property="kode_opd", type="string", example="DISDIK"),
     *                         @OA\Property(property="alamat", type="string", example="Jl. Pendidikan No. 1")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            // Get pagination parameters
            $perPage = $request->input('per_page', 15);
            $perPage = max(5, min(100, (int)$perPage));

            // Build query
            $query = Peserta::with('opd');

            // Handle search
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            }

            // Filter by status_kepegawaian
            if ($request->has('status_kepegawaian') && in_array($request->status_kepegawaian, ['pns', 'pppk', 'kontrak'])) {
                $query->where('status_kepegawaian', $request->status_kepegawaian);
            }

            // Filter by OPD
            if ($request->has('opd_id') && is_numeric($request->opd_id)) {
                $query->where('opd_id', $request->opd_id);
            }

            $pesertas = $query->paginate($perPage);

            return PesertaResource::collection($pesertas);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching Peserta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new Peserta
     * 
     * @OA\Post(
     *     path="/api/v1/peserta",
     *     tags={"Peserta"},
     *     summary="Create new Peserta",
     *     description="Create a new Peserta record",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"opd_id","username","email","password","nama_lengkap","status_kepegawaian"},
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="password123"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="pangkat_golongan", type="string", example="III/a"),
     *                 @OA\Property(property="jabatan", type="string", example="Staff"),
     *                 @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *                 @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *                 @OA\Property(property="jenis_kelamin", type="string", enum={"laki_laki", "perempuan"}, example="laki_laki"),
     *                 @OA\Property(property="pendidikan_terakhir", type="string", enum={"sma", "d3", "s1", "s2", "s3"}, example="s1"),
     *                 @OA\Property(property="status_kepegawaian", type="string", enum={"pns", "pppk", "kontrak"}, example="pns"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                 @OA\Property(property="foto_profil", type="file", format="binary")
     *             )
     *         ),
     *         @OA\JsonContent(
     *             required={"opd_id","username","email","password","nama_lengkap","status_kepegawaian"},
     *             @OA\Property(property="opd_id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *             @OA\Property(property="nip", type="string", example="198501012010011001"),
     *             @OA\Property(property="pangkat_golongan", type="string", example="III/a"),
     *             @OA\Property(property="jabatan", type="string", example="Staff"),
     *             @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *             @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *             @OA\Property(property="jenis_kelamin", type="string", enum={"laki_laki", "perempuan"}, example="laki_laki"),
     *             @OA\Property(property="pendidikan_terakhir", type="string", enum={"sma", "d3", "s1", "s2", "s3"}, example="s1"),
     *             @OA\Property(property="status_kepegawaian", type="string", enum={"pns", "pppk", "kontrak"}, example="pns"),
     *             @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Peserta created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Peserta created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="pangkat_golongan", type="string", example="III/a"),
     *                 @OA\Property(property="jabatan", type="string", example="Staff"),
     *                 @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *                 @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *                 @OA\Property(property="jenis_kelamin", type="string", example="laki_laki"),
     *                 @OA\Property(property="pendidikan_terakhir", type="string", example="s1"),
     *                 @OA\Property(property="status_kepegawaian", type="string", example="pns"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                 @OA\Property(property="foto_profil", type="string", example="1698304599.jpg"),
     *                 @OA\Property(property="opd", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan"),
     *                     @OA\Property(property="kode_opd", type="string", example="DISDIK")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
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
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating Peserta")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Get input data - try JSON first, then fallback to request->all()
            $input = $request->json()->all();
            if (empty($input) && $request->isJson()) {
                $input = json_decode($request->getContent(), true);
            }
            if (empty($input)) {
                $input = $request->all();
            }

            $validator = Validator::make($input, [
                'opd_id' => 'required|exists:opds,id',
                'username' => 'required|string|max:255|unique:pesertas',
                'email' => 'required|string|email|max:255|unique:pesertas',
                'password' => 'required|string|min:8',
                'nama_lengkap' => 'required|string|max:255',
                'nip' => 'nullable|string|unique:pesertas',
                'pangkat_golongan' => 'nullable|string|max:50',
                'jabatan' => 'nullable|string|max:100',
                'tanggal_lahir' => 'nullable|date',
                'tempat_lahir' => 'nullable|string|max:100',
                'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',
                'pendidikan_terakhir' => 'nullable|in:sma,d3,s1,s2,s3',
                'status_kepegawaian' => 'required|in:pns,pppk,kontrak',
                'no_telepon' => 'nullable|string|max:20',
                'alamat' => 'nullable|string',
                'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $input;
            if (isset($data['foto_profil']) && !$request->hasFile('foto_profil')) {
                unset($data['foto_profil']);
            }
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Upload foto profil jika ada
            if ($request->hasFile('foto_profil')) {
                $file = $request->file('foto_profil');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/profile/foto', $filename);
                $data['foto_profil'] = $filename;
            }

            $peserta = Peserta::create($data);
            $peserta->load('opd'); // Load relationship for response

            return response()->json([
                'message' => 'Peserta created successfully',
                'data' => new PesertaResource($peserta)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating Peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific Peserta by ID
     * 
     * @OA\Get(
     *     path="/api/v1/peserta/{id}",
     *     tags={"Peserta"},
     *     summary="Get Peserta by ID",
     *     description="Returns specific Peserta details with their OPD",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Peserta ID",
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
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="pangkat_golongan", type="string", example="III/a"),
     *                 @OA\Property(property="jabatan", type="string", example="Staff"),
     *                 @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *                 @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *                 @OA\Property(property="jenis_kelamin", type="string", example="laki_laki"),
     *                 @OA\Property(property="pendidikan_terakhir", type="string", example="s1"),
     *                 @OA\Property(property="status_kepegawaian", type="string", example="pns"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                 @OA\Property(property="foto_profil", type="string", example="1698304599.jpg"),
     *                 @OA\Property(property="opd", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan"),
     *                     @OA\Property(property="kode_opd", type="string", example="DISDIK"),
     *                     @OA\Property(property="alamat", type="string", example="Jl. Pendidikan No. 1")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 06:08:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Peserta not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Peserta not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            // Find Peserta or return 404 if not found
            $peserta = Peserta::with('opd')->find($id);

            if (!$peserta) {
                return response()->json(['message' => 'Peserta not found'], 404);
            }

            return new PesertaResource($peserta);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving Peserta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Peserta
     * 
     * @OA\Put(
     *     path="/api/v1/peserta/{id}",
     *     tags={"Peserta"},
     *     summary="Update Peserta",
     *     description="Update Peserta information",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Peserta ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe_updated"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="pangkat_golongan", type="string", example="III/b"),
     *                 @OA\Property(property="jabatan", type="string", example="Senior Staff"),
     *                 @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *                 @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *                 @OA\Property(property="jenis_kelamin", type="string", enum={"laki_laki", "perempuan"}, example="laki_laki"),
     *                 @OA\Property(property="pendidikan_terakhir", type="string", enum={"sma", "d3", "s1", "s2", "s3"}, example="s2"),
     *                 @OA\Property(property="status_kepegawaian", type="string", enum={"pns", "pppk", "kontrak"}, example="pns"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh Baru No. 456"),
     *                 @OA\Property(property="foto_profil", type="file", format="binary")
     *             )
     *         ),
     *         @OA\JsonContent(
     *             @OA\Property(property="opd_id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="johndoe_updated"),
     *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="nama_lengkap", type="string", example="John Doe Updated"),
     *             @OA\Property(property="nip", type="string", example="198501012010011001"),
     *             @OA\Property(property="pangkat_golongan", type="string", example="III/b"),
     *             @OA\Property(property="jabatan", type="string", example="Senior Staff"),
     *             @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *             @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *             @OA\Property(property="jenis_kelamin", type="string", enum={"laki_laki", "perempuan"}, example="laki_laki"),
     *             @OA\Property(property="pendidikan_terakhir", type="string", enum={"sma", "d3", "s1", "s2", "s3"}, example="s2"),
     *             @OA\Property(property="status_kepegawaian", type="string", enum={"pns", "pppk", "kontrak"}, example="pns"),
     *             @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Contoh Baru No. 456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Peserta updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Peserta updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="opd_id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe_updated"),
     *                 @OA\Property(property="email", type="string", example="john.updated@example.com"),
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="nip", type="string", example="198501012010011001"),
     *                 @OA\Property(property="pangkat_golongan", type="string", example="III/b"),
     *                 @OA\Property(property="jabatan", type="string", example="Senior Staff"),
     *                 @OA\Property(property="tanggal_lahir", type="string", format="date", example="1985-01-01"),
     *                 @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *                 @OA\Property(property="jenis_kelamin", type="string", example="laki_laki"),
     *                 @OA\Property(property="pendidikan_terakhir", type="string", example="s2"),
     *                 @OA\Property(property="status_kepegawaian", type="string", example="pns"),
     *                 @OA\Property(property="no_telepon", type="string", example="081234567890"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh Baru No. 456"),
     *                 @OA\Property(property="foto_profil", type="string", example="1698304799.jpg"),
     *                 @OA\Property(property="opd", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_opd", type="string", example="Dinas Pendidikan"),
     *                     @OA\Property(property="kode_opd", type="string", example="DISDIK")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25 06:08:19"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25 07:13:19")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Peserta not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Peserta not found")
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
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating Peserta")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            // Find Peserta or return 404 if not found
            $peserta = Peserta::find($id);

            if (!$peserta) {
                return response()->json(['message' => 'Peserta not found'], 404);
            }

            // Get input data - try JSON first, then fallback to request->all()
            $input = $request->json()->all();
            if (empty($input) && $request->isJson()) {
                $input = json_decode($request->getContent(), true);
            }
            if (empty($input)) {
                $input = $request->all();
            }

            $validator = Validator::make($input, [
                'opd_id' => 'sometimes|required|exists:opds,id',
                'username' => 'sometimes|required|string|max:255|unique:pesertas,username,' . $id,
                'email' => 'sometimes|required|string|email|max:255|unique:pesertas,email,' . $id,
                'password' => 'nullable|string|min:8',
                'nama_lengkap' => 'sometimes|required|string|max:255',
                'nip' => 'nullable|string|unique:pesertas,nip,' . $id,
                'pangkat_golongan' => 'nullable|string|max:50',
                'jabatan' => 'nullable|string|max:100',
                'tanggal_lahir' => 'nullable|date',
                'tempat_lahir' => 'nullable|string|max:100',
                'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',
                'pendidikan_terakhir' => 'nullable|in:sma,d3,s1,s2,s3',
                'status_kepegawaian' => 'sometimes|required|in:pns,pppk,kontrak',
                'no_telepon' => 'nullable|string|max:20',
                'alamat' => 'nullable|string',
                'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $input;
            if (isset($data['foto_profil']) && !$request->hasFile('foto_profil')) {
                unset($data['foto_profil']);
            }

            // Handle password separately
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Upload foto profil jika ada
            if ($request->hasFile('foto_profil')) {
                // Hapus foto lama jika ada
                if ($peserta->foto_profil) {
                    Storage::delete('public/profile/foto/' . $peserta->foto_profil);
                }

                $file = $request->file('foto_profil');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/profile/foto', $filename);
                $data['foto_profil'] = $filename;
            }

            $peserta->update($data);
            $peserta->load('opd'); // Load relationship for response

            return response()->json([
                'message' => 'Peserta updated successfully',
                'data' => new PesertaResource($peserta)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating Peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Peserta
     * 
     * @OA\Delete(
     *     path="/api/v1/peserta/{id}",
     *     tags={"Peserta"},
     *     summary="Delete Peserta",
     *     description="Soft delete a Peserta record",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Peserta ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Peserta deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Peserta deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Peserta not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Peserta not found")
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
     *             @OA\Property(property="message", type="string", example="Error deleting Peserta")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            // Find Peserta or return 404 if not found
            $peserta = Peserta::find($id);

            if (!$peserta) {
                return response()->json(['message' => 'Peserta not found'], 404);
            }

            // Soft delete (assumes SoftDeletes trait is used in the model)
            $peserta->delete();

            return response()->json([
                'message' => 'Peserta deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting Peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
