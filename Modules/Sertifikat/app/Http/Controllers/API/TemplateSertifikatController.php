<?php

namespace Modules\Sertifikat\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sertifikat\Entities\TemplateSertifikat;
use Modules\Sertifikat\Transformers\TemplateSertifikatResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="TemplateSertifikat",
 *     description="API Endpoints untuk manajemen Template Sertifikat"
 * )
 */
class TemplateSertifikatController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/template-sertifikat",
     *     summary="Mendapatkan daftar template sertifikat",
     *     tags={"TemplateSertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Pencarian berdasarkan nama template",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar template sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama_template", type="string", example="Template Sertifikat Dasar"),
     *                     @OA\Property(property="path_background", type="string", example="templates/backgrounds/template-1.jpg"),
     *                     @OA\Property(property="background_url", type="string", example="http://localhost/storage/templates/backgrounds/template-1.jpg"),
     *                     @OA\Property(property="logo_bpsdm_url", type="string", example="http://localhost/storage/templates/logos/bpsdm-logo.png"),
     *                     @OA\Property(property="logo_pemda_url", type="string", example="http://localhost/storage/templates/logos/pemda-logo.png")
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
        $query = TemplateSertifikat::query();

        if ($request->has('search')) {
            $query->where('nama_template', 'like', '%' . $request->search . '%');
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(10);

        return TemplateSertifikatResource::collection($templates);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/template-sertifikat",
     *     summary="Membuat template sertifikat baru",
     *     tags={"TemplateSertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Form data",
     *         @OA\JsonContent(
     *             required={"nama_template"},
     *             @OA\Property(property="nama_template", type="string", example="Template Sertifikat Dasar"),
     *             @OA\Property(property="design_template", type="string", example="Template HTML"),
     *             @OA\Property(property="signature_config", type="string", example="Config JSON"),
     *             @OA\Property(property="footer_text", type="string", example="Footer Text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template sertifikat berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Template sertifikat created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
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
            'nama_template' => 'required|string|max:255',
            'design_template' => 'nullable|string',
            'background' => 'nullable|image|max:2048',
            'signature_config' => 'nullable|string',
            'logo_bpsdm' => 'nullable|image|max:2048',
            'logo_pemda' => 'nullable|image|max:2048',
            'footer_text' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $template = new TemplateSertifikat();
        $template->nama_template = $request->nama_template;
        $template->design_template = $request->design_template;
        $template->signature_config = $request->signature_config;
        $template->footer_text = $request->footer_text;

        // Upload background
        if ($request->hasFile('background')) {
            $path = $request->file('background')->store('templates/backgrounds', 'public');
            $template->path_background = $path;
        }

        // Upload logo BPSDM
        if ($request->hasFile('logo_bpsdm')) {
            $path = $request->file('logo_bpsdm')->store('templates/logos', 'public');
            $template->logo_bpsdm_path = $path;
        }

        // Upload logo Pemda
        if ($request->hasFile('logo_pemda')) {
            $path = $request->file('logo_pemda')->store('templates/logos', 'public');
            $template->logo_pemda_path = $path;
        }

        $template->save();

        return response()->json([
            'message' => 'Template sertifikat created successfully',
            'data' => new TemplateSertifikatResource($template)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/template-sertifikat/{id}",
     *     summary="Mendapatkan detail template sertifikat",
     *     tags={"TemplateSertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Template Sertifikat",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail template sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_template", type="string", example="Template Sertifikat Dasar"),
     *                 @OA\Property(property="design_template", type="string", example="<div>{{nama_peserta}}</div>"),
     *                 @OA\Property(property="path_background", type="string", example="templates/backgrounds/template-1.jpg"),
     *                 @OA\Property(property="background_url", type="string", example="http://localhost/storage/templates/backgrounds/template-1.jpg"),
     *                 @OA\Property(property="signature_config", type="string", example="{'posX':200, 'posY':500, 'width':150}"),
     *                 @OA\Property(property="logo_bpsdm_path", type="string", example="templates/logos/bpsdm-logo.png"),
     *                 @OA\Property(property="logo_bpsdm_url", type="string", example="http://localhost/storage/templates/logos/bpsdm-logo.png"),
     *                 @OA\Property(property="logo_pemda_path", type="string", example="templates/logos/pemda-logo.png"),
     *                 @OA\Property(property="logo_pemda_url", type="string", example="http://localhost/storage/templates/logos/pemda-logo.png"),
     *                 @OA\Property(property="footer_text", type="string", example="© BPSDM Provinsi 2025")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template sertifikat tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $template = TemplateSertifikat::findOrFail($id);
        return new TemplateSertifikatResource($template);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/template-sertifikat/{id}",
     *     summary="Mengupdate template sertifikat",
     *     tags={"TemplateSertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Template Sertifikat",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="nama_template", type="string", example="Template Sertifikat Dasar Updated"),
     *                 @OA\Property(property="design_template", type="string", example="<div>{{nama_peserta}}</div>"),
     *                 @OA\Property(property="background", type="string", format="binary", description="Background image file"),
     *                 @OA\Property(property="signature_config", type="string", example="{'posX':200, 'posY':500, 'width':150}"),
     *                 @OA\Property(property="logo_bpsdm", type="string", format="binary", description="Logo BPSDM image file"),
     *                 @OA\Property(property="logo_pemda", type="string", format="binary", description="Logo Pemda image file"),
     *                 @OA\Property(property="footer_text", type="string", example="© BPSDM Provinsi 2025")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Template sertifikat berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Template sertifikat updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama_template", type="string", example="Template Sertifikat Dasar Updated")
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
     *         description="Template sertifikat tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $template = TemplateSertifikat::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_template' => 'nullable|string|max:255',
            'design_template' => 'nullable|string',
            'background' => 'nullable|image|max:2048',
            'signature_config' => 'nullable|string',
            'logo_bpsdm' => 'nullable|image|max:2048',
            'logo_pemda' => 'nullable|image|max:2048',
            'footer_text' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update text fields
        if ($request->has('nama_template')) {
            $template->nama_template = $request->nama_template;
        }

        if ($request->has('design_template')) {
            $template->design_template = $request->design_template;
        }

        if ($request->has('signature_config')) {
            $template->signature_config = $request->signature_config;
        }

        if ($request->has('footer_text')) {
            $template->footer_text = $request->footer_text;
        }

        // Upload background
        if ($request->hasFile('background')) {
            // Delete old file if exists
            if ($template->path_background) {
                Storage::disk('public')->delete($template->path_background);
            }

            $path = $request->file('background')->store('templates/backgrounds', 'public');
            $template->path_background = $path;
        }

        // Upload logo BPSDM
        if ($request->hasFile('logo_bpsdm')) {
            // Delete old file if exists
            if ($template->logo_bpsdm_path) {
                Storage::disk('public')->delete($template->logo_bpsdm_path);
            }

            $path = $request->file('logo_bpsdm')->store('templates/logos', 'public');
            $template->logo_bpsdm_path = $path;
        }

        // Upload logo Pemda
        if ($request->hasFile('logo_pemda')) {
            // Delete old file if exists
            if ($template->logo_pemda_path) {
                Storage::disk('public')->delete($template->logo_pemda_path);
            }

            $path = $request->file('logo_pemda')->store('templates/logos', 'public');
            $template->logo_pemda_path = $path;
        }

        $template->save();

        return response()->json([
            'message' => 'Template sertifikat updated successfully',
            'data' => new TemplateSertifikatResource($template)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/template-sertifikat/{id}",
     *     summary="Menghapus template sertifikat",
     *     tags={"TemplateSertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Template Sertifikat",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Template sertifikat berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Template sertifikat deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template sertifikat tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Template sertifikat tidak dapat dihapus karena masih digunakan",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Cannot delete template because it's still used by sertifikats")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $template = TemplateSertifikat::findOrFail($id);

        // Check if template is being used
        if ($template->sertifikats()->count() > 0) {
            return response()->json([
                'error' => "Cannot delete template because it's still used by sertifikats"
            ], 400);
        }

        // Delete all files
        if ($template->path_background) {
            Storage::disk('public')->delete($template->path_background);
        }

        if ($template->logo_bpsdm_path) {
            Storage::disk('public')->delete($template->logo_bpsdm_path);
        }

        if ($template->logo_pemda_path) {
            Storage::disk('public')->delete($template->logo_pemda_path);
        }

        $template->delete();

        return response()->json([
            'message' => 'Template sertifikat deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/template-sertifikat/preview/{id}",
     *     summary="Mendapatkan preview template sertifikat",
     *     tags={"TemplateSertifikat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Template Sertifikat",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preview template sertifikat berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="html", type="string", example="<html>...</html>"),
     *             @OA\Property(property="template", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template sertifikat tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function preview($id)
    {
        $template = TemplateSertifikat::findOrFail($id);

        // Sample data for preview
        $sampleData = [
            'nama_peserta' => 'Nama Peserta',
            'nomor_sertifikat' => 'NO/SERT/2025/001',
            'tanggal_terbit' => date('d F Y'),
            'nama_kursus' => 'Nama Kursus Contoh',
            'nama_penandatangan' => 'Nama Penandatangan',
            'jabatan_penandatangan' => 'Jabatan Penandatangan'
        ];

        // Replace placeholders with sample data
        $html = $template->design_template;
        foreach ($sampleData as $key => $value) {
            $html = str_replace("{{" . $key . "}}", $value, $html);
        }

        return response()->json([
            'html' => $html,
            'template' => new TemplateSertifikatResource($template)
        ]);
    }
}
