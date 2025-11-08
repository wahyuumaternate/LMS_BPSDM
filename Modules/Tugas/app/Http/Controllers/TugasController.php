<?php

namespace Modules\Tugas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tugas\Entities\Tugas;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TugasController extends Controller
{
    /**
     * Display a listing of the assignments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        try {
            $query = Tugas::with(['modul']);

            // Filter by modul_id
            if ($request->has('modul_id')) {
                $query->where('modul_id', $request->modul_id);
            }

            // Filter by is_published
            if ($request->has('is_published')) {
                $query->where('is_published', $request->boolean('is_published'));
            }

            $tugas = $query->orderBy('tanggal_deadline', 'asc')->paginate(10);
            $moduls = \Modules\Modul\Entities\Modul::orderBy('nama_modul')->get();

            return view('tugas::index', compact('tugas', 'moduls'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error fetching assignments: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new assignment.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $moduls = \Modules\Modul\Entities\Modul::orderBy('nama_modul')->get();
        return view('tugas::create', compact('moduls'));
    }

    /**
     * Store a newly created assignment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'modul_id' => 'required|exists:moduls,id',
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'petunjuk' => 'nullable|string',
                'file_tugas' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
                'tanggal_mulai' => 'nullable|date',
                'tanggal_deadline' => 'nullable|date|after_or_equal:tanggal_mulai',
                'nilai_maksimal' => 'nullable|integer|min:1|max:100',
                'bobot_nilai' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except(['file_tugas', 'is_published', '_token']);

            // Handle boolean field
            $data['is_published'] = $request->has('is_published');

            // Upload file tugas if provided
            if ($request->hasFile('file_tugas')) {
                $file = $request->file('file_tugas');
                $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/tugas', $filename);
                $data['file_tugas'] = 'tugas/' . $filename;
            }

            // Set published_at if is_published is true
            if ($data['is_published']) {
                $data['published_at'] = now();
            }

            Tugas::create($data);

            return redirect()->route('tugas.index')
                ->with('success', 'Tugas berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating assignment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified assignment.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        try {
            $tugas = Tugas::with(['modul', 'submissions.peserta'])->findOrFail($id);
            return view('tugas::show', compact('tugas'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Assignment not found');
        } catch (\Exception $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Error retrieving assignment: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified assignment.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        try {
            $tugas = Tugas::findOrFail($id);
            $moduls = \Modules\Modul\Entities\Modul::orderBy('nama_modul')->get();

            return view('tugas::edit', compact('tugas', 'moduls'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Assignment not found');
        } catch (\Exception $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Error retrieving assignment: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified assignment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $tugas = Tugas::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'modul_id' => 'required|exists:moduls,id',
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'petunjuk' => 'nullable|string',
                'file_tugas' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'tanggal_mulai' => 'nullable|date',
                'tanggal_deadline' => 'nullable|date|after_or_equal:tanggal_mulai',
                'nilai_maksimal' => 'nullable|integer|min:1|max:100',
                'bobot_nilai' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except(['file_tugas', 'is_published', '_token', '_method']);

            // Handle boolean field
            $data['is_published'] = $request->has('is_published');

            // Upload file tugas if provided
            if ($request->hasFile('file_tugas')) {
                // Delete old file if exists
                if ($tugas->file_tugas) {
                    Storage::delete('public/' . $tugas->file_tugas);
                }

                $file = $request->file('file_tugas');
                $filename = Str::slug($request->judul) . '-' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/tugas', $filename);
                $data['file_tugas'] = 'tugas/' . $filename;
            }

            // Set published_at if is_published changed to true
            if ($data['is_published'] && !$tugas->is_published) {
                $data['published_at'] = now();
            }

            $tugas->update($data);

            return redirect()->route('tugas.show', $tugas->id)
                ->with('success', 'Tugas berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Assignment not found');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating assignment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified assignment from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $tugas = Tugas::findOrFail($id);

            // Delete file tugas if exists
            if ($tugas->file_tugas) {
                Storage::delete('public/' . $tugas->file_tugas);
            }

            $tugas->delete();

            return redirect()->route('tugas.index')
                ->with('success', 'Tugas berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Assignment not found');
        } catch (\Exception $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Error deleting assignment: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the published status of an assignment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function togglePublish($id)
    {
        try {
            $tugas = Tugas::findOrFail($id);

            $tugas->is_published = !$tugas->is_published;

            if ($tugas->is_published) {
                $tugas->published_at = now();
                $message = 'Tugas berhasil dipublikasikan';
            } else {
                $tugas->published_at = null;
                $message = 'Tugas berhasil dibatalkan publikasi';
            }

            $tugas->save();

            return redirect()->back()->with('success', $message);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Assignment not found');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error toggling publish status: ' . $e->getMessage());
        }
    }

    /**
     * View assignment submissions.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function submissions($id)
    {
        try {
            $tugas = Tugas::with(['modul', 'submissions.peserta'])->findOrFail($id);
            return view('tugas::submissions.index', compact('tugas'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Assignment not found');
        } catch (\Exception $e) {
            return redirect()->route('tugas.index')
                ->with('error', 'Error retrieving assignment submissions: ' . $e->getMessage());
        }
    }
}
