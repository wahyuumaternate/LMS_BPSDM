<?php

namespace Modules\Materi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Materi\Entities\Materi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Modul\Entities\Modul;

class MateriController extends Controller
{
    /**
     * Display a listing of the materials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
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

            $materis = $query->paginate(10);

            return view('materi::index', compact('materis'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error fetching materials: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        // Get modules for dropdown
        $modules = \Modules\Modul\Entities\Modul::all();

        // Pre-select module if provided in query string
        $selected_module = $request->has('modul_id') ? $request->modul_id : null;

        return view('materi::create', compact('modules', 'selected_module'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'modul_id' => 'required|exists:moduls,id',
                'judul_materi' => 'required|string|max:255',
                'urutan' => 'nullable|integer|min:0',
                'tipe_konten' => 'required|in:pdf,video,dokumen,link,doc,docx,ppt,pptx',
                'file' => 'nullable|file|max:102400', // 100MB max
                'youtube_url' => 'nullable|url',
                'link_url' => 'nullable|url',
                'deskripsi' => 'nullable|string',
                'durasi_menit' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $data = $request->except(['file', 'youtube_url', 'link_url', 'is_wajib', 'is_published', '_token']);

            // Handle boolean fields
            $data['is_wajib'] = $request->has('is_wajib');
            $data['is_published'] = $request->has('is_published');

            // If urutan not provided, set it to the last position
            if (!$request->filled('urutan')) {
                $lastUrutan = Materi::where('modul_id', $request->modul_id)->max('urutan');
                $data['urutan'] = ($lastUrutan ?? 0) + 1;
            }

            // Handle file upload or external link based on tipe_konten
            if ($request->tipe_konten === 'video') {
                // Save YouTube URL
                $data['file_path'] = $request->youtube_url;
            } elseif ($request->tipe_konten === 'link') {
                // Save external link URL
                $data['file_path'] = $request->link_url;
            } elseif ($request->hasFile('file')) {
                // Upload file for pdf or dokumen
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug($request->judul_materi) . '-' . time() . '.' . $extension;

                // Store file in storage/app/public/materi
                $folder = 'materi';
                $file->storeAs($folder, $filename);

                $data['file_path'] = $filename;
                $data['ukuran_file'] = round($file->getSize() / 1024); // convert to KB
            }

            // Set published_at if needed
            if ($request->has('is_published')) {
                $data['published_at'] = now();
            }

            Materi::create($data);

            // Get kursus_id from modul to redirect to correct course page
            $modul = Modul::findOrFail($request->modul_id);
            $kursusId = $modul->kursus_id;

            return redirect()->route('course.materi', $kursusId)->with('success', 'Materi berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error creating material: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        try {
            $materi = Materi::with(['modul'])->findOrFail($id);
            return view('materi::show', compact('materi'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('materi.index')->with('error', 'Material not found');
        } catch (\Exception $e) {
            return redirect()
                ->route('materi.index')
                ->with('error', 'Error retrieving material: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        try {
            $materi = Materi::findOrFail($id);
            $modules = \Modules\Modul\Entities\Modul::all();

            return view('materi::edit', compact('materi', 'modules'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('materi.index')->with('error', 'Material not found');
        } catch (\Exception $e) {
            return redirect()
                ->route('materi.index')
                ->with('error', 'Error retrieving material: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $materi = Materi::with('modul')->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'modul_id' => 'required|exists:moduls,id',
                'judul_materi' => 'required|string|max:255',
                'urutan' => 'nullable|integer|min:0',
                'tipe_konten' => 'required|in:pdf,video,dokumen,link,doc,docx,ppt,pptx',
                'file' => 'nullable|file|max:102400',
                'youtube_url' => 'nullable|url',
                'link_url' => 'nullable|url',
                'deskripsi' => 'nullable|string',
                'durasi_menit' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $data = $request->except(['file', 'youtube_url', 'link_url', 'is_wajib', 'is_published', '_token', '_method']);

            // Handle boolean fields
            $data['is_wajib'] = $request->has('is_wajib');
            $data['is_published'] = $request->has('is_published');

            // Handle file upload or external link based on tipe_konten
            if ($request->tipe_konten === 'video') {
                $data['file_path'] = $request->youtube_url;

                // Delete old file if changing from file type to video
                if (in_array($materi->tipe_konten, ['pdf', 'dokumen']) && $materi->file_path) {
                    Storage::delete('materi/' . $materi->file_path);
                }
            } elseif ($request->tipe_konten === 'link') {
                $data['file_path'] = $request->link_url;

                // Delete old file if changing from file type to link
                if (in_array($materi->tipe_konten, ['pdf', 'dokumen']) && $materi->file_path) {
                    Storage::delete('materi/' . $materi->file_path);
                }
            } elseif ($request->hasFile('file')) {
                // Delete old file if exists
                if (in_array($materi->tipe_konten, ['pdf', 'dokumen']) && $materi->file_path) {
                    Storage::delete('materi/' . $materi->file_path);
                }

                // Upload new file
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug($request->judul_materi) . '-' . time() . '.' . $extension;

                $folder = 'materi';
                $file->storeAs($folder, $filename);

                $data['file_path'] = $filename;
                $data['ukuran_file'] = round($file->getSize() / 1024);
            }

            // Handle published_at
            if ($request->has('is_published') && !$materi->is_published) {
                $data['published_at'] = now();
            } elseif (!$request->has('is_published')) {
                $data['published_at'] = null;
            }

            $materi->update($data);

            // Get kursus_id from modul relation
            $kursusId = $materi->modul->kursus_id;

            return redirect()->route('course.materi', $kursusId)->with('success', 'Materi berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error updating material: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $materi = Materi::with('modul')->findOrFail($id);
            $kursusId = $materi->modul->kursus_id;

            // Delete file if not a link or video
            if (in_array($materi->tipe_konten, ['pdf', 'dokumen']) && $materi->file_path) {
                Storage::delete('materi/' . $materi->file_path);
            }

            $materi->delete();

            // Return JSON response for AJAX
            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil dihapus',
                'redirect' => route('course.materi', $kursusId),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Materi tidak ditemukan',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error menghapus materi: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Show the form for reordering materials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function reorderForm(Request $request)
    {
        try {
            $modulId = $request->modul_id;

            if (!$modulId) {
                return redirect()->route('materi.index')->with('error', 'Module ID is required');
            }

            $modul = \Modules\Modul\Entities\Modul::findOrFail($modulId);
            $materis = Materi::where('modul_id', $modulId)->orderBy('urutan')->get();

            return view('materi::reorder', compact('modul', 'materis'));
        } catch (\Exception $e) {
            return redirect()
                ->route('materi.index')
                ->with('error', 'Error loading reorder page: ' . $e->getMessage());
        }
    }

    /**
     * Update the order of materials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reorderUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'modul_id' => 'required|exists:moduls,id',
                'materis' => 'required|array',
                'materis.*.id' => 'required|exists:materis,id',
                'materis.*.urutan' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Check if all materis belong to the specified modul
            $materiIds = collect($request->materis)->pluck('id')->toArray();
            $invalidMateris = Materi::whereIn('id', $materiIds)->where('modul_id', '!=', $request->modul_id)->exists();

            if ($invalidMateris) {
                return redirect()->back()->with('error', 'Some materials do not belong to the specified module')->withInput();
            }

            // Update urutan for each materi
            foreach ($request->materis as $materiData) {
                Materi::where('id', $materiData['id'])->update(['urutan' => $materiData['urutan']]);
            }

            return redirect()
                ->route('materi.index', ['modul_id' => $request->modul_id])
                ->with('success', 'Urutan materi berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error reordering materials: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Toggle the published status of a material.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function togglePublish($id)
    {
        try {
            $materi = Materi::findOrFail($id);

            if ($materi->published_at) {
                $materi->published_at = null;
                $message = 'Materi berhasil dibatalkan publikasi';
            } else {
                $materi->published_at = now();
                $message = 'Materi berhasil dipublikasikan';
            }

            $materi->save();

            return redirect()->back()->with('success', $message);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Material not found');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error toggling publish status: ' . $e->getMessage());
        }
    }
}
