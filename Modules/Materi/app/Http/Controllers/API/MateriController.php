<?php

namespace Modules\Materi\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Materi\Entities\Materi;
use Modules\Materi\Transformers\MateriResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MateriController extends Controller
{
    public function index(Request $request)
    {
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
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modul_id' => 'required|exists:moduls,id',
            'judul_materi' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'tipe_konten' => 'required|in:pdf,doc,video,audio,gambar,link,scorm',
            'file' => 'required_unless:tipe_konten,link|file|max:102400', // 100MB max
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

        // If urutan not provided, set it to the last position
        if (!$request->filled('urutan')) {
            $lastUrutan = Materi::where('modul_id', $request->modul_id)
                ->max('urutan');
            $data['urutan'] = $lastUrutan + 1;
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

        return response()->json([
            'message' => 'Materi created successfully',
            'data' => new MateriResource($materi)
        ], 201);
    }

    public function show($id)
    {
        $materi = Materi::with(['modul'])->findOrFail($id);
        return new MateriResource($materi);
    }

    public function update(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);

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

        return response()->json([
            'message' => 'Materi updated successfully',
            'data' => new MateriResource($materi)
        ]);
    }

    public function destroy($id)
    {
        $materi = Materi::findOrFail($id);

        // Delete file if not a link
        if ($materi->tipe_konten !== 'link' && $materi->file_path) {
            Storage::delete('public/materi/files/' . $materi->tipe_konten . '/' . $materi->file_path);
        }

        $materi->delete();

        return response()->json([
            'message' => 'Materi deleted successfully'
        ]);
    }

    public function reorder(Request $request)
    {
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
    }
}
