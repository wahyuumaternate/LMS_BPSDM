<?php

namespace Modules\Tugas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tugas\Entities\TugasSubmission;
use Modules\Tugas\Entities\Tugas;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TugasSubmissionController extends Controller
{
    /**
     * Display a listing of the tugas submissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        dd(1);
        try {
            $query = TugasSubmission::with(['tugas', 'peserta', 'penilai']);

            // Filter by tugas_id
            if ($request->has('tugas_id')) {
                $query->where('tugas_id', $request->tugas_id);
            }

            // Filter by peserta_id
            if ($request->has('peserta_id')) {
                $query->where('peserta_id', $request->peserta_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $submissions = $query->orderBy('tanggal_submit', 'desc')->paginate(15);
            $tugasList = Tugas::orderBy('judul')->get();

            // Get peserta list
            $pesertas = \Modules\Peserta\Entities\Peserta::orderBy('nama_lengkap')->get();

            // return view('tugas::submissions.index', compact('submissions', 'tugasList', 'pesertas'));
            return view('tugas::submissions.index', compact('submissions', 'tugasList', 'pesertas'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error fetching submissions: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new submission.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $tugasList = Tugas::where('is_published', true)
            ->whereDate('tanggal_deadline', '>=', now()->format('Y-m-d'))
            ->orWhereNull('tanggal_deadline')
            ->orderBy('judul')
            ->get();

        $pesertas = \Modules\Peserta\Entities\Peserta::orderBy('nama_lengkap')->get();

        return view('tugas::submissions.create', compact('tugasList', 'pesertas'));
    }

    /**
     * Store a newly created submission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tugas_id' => 'required|exists:tugas,id',
                'peserta_id' => 'required|exists:pesertas,id',
                'catatan_peserta' => 'nullable|string',
                'file_jawaban' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check if submission already exists
            $existingSubmission = TugasSubmission::where('tugas_id', $request->tugas_id)
                ->where('peserta_id', $request->peserta_id)
                ->first();

            if ($existingSubmission) {
                return redirect()->back()
                    ->with('warning', 'Pengumpulan untuk tugas ini sudah ada. Silakan perbarui pengumpulan yang sudah ada.')
                    ->withInput();
            }

            $data = $request->except(['_token', 'file_jawaban']);

            // Upload file jawaban if provided
            if ($request->hasFile('file_jawaban')) {
                $file = $request->file('file_jawaban');
                $filename = 'submission-' . $request->peserta_id . '-' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/tugas/submissions', $filename);
                $data['file_jawaban'] = 'tugas/submissions/' . $filename;
            }

            // Check if late submission
            $tugas = Tugas::findOrFail($request->tugas_id);
            $isLate = $tugas->tanggal_deadline && now()->gt($tugas->tanggal_deadline);

            $data['tanggal_submit'] = now();
            $data['status'] = $isLate ? 'late' : 'submitted';

            TugasSubmission::create($data);

            return redirect()->route('submission.index')
                ->with('success', 'Pengumpulan tugas berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error membuat pengumpulan tugas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified submission.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        try {
            $submission = TugasSubmission::with(['tugas', 'peserta', 'penilai'])->findOrFail($id);
            return view('tugas::submissions.show', compact('submission'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Pengumpulan tugas tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Error menampilkan pengumpulan tugas: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified submission.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        try {
            $submission = TugasSubmission::findOrFail($id);

            // Can only edit if status is draft or returned
            if (!in_array($submission->status, ['draft', 'returned'])) {
                return redirect()->route('submission.index')
                    ->with('error', 'Tidak dapat mengubah pengumpulan yang sudah dinilai.');
            }

            $tugasList = Tugas::orderBy('judul')->get();
            $pesertas = \Modules\Peserta\Entities\Peserta::orderBy('nama_lengkap')->get();

            return view('tugas::submissions.edit', compact('submission', 'tugasList', 'pesertas'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Pengumpulan tugas tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Error menampilkan form edit: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified submission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $submission = TugasSubmission::findOrFail($id);

            // Can only update if status is draft or returned
            if (!in_array($submission->status, ['draft', 'returned'])) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat mengubah pengumpulan yang sudah dinilai.');
            }

            $validator = Validator::make($request->all(), [
                'catatan_peserta' => 'nullable|string',
                'file_jawaban' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->except(['_token', '_method', 'file_jawaban']);

            // Upload file jawaban if provided
            if ($request->hasFile('file_jawaban')) {
                // Delete old file if exists
                if ($submission->file_jawaban) {
                    Storage::delete('public/' . $submission->file_jawaban);
                }

                $file = $request->file('file_jawaban');
                $filename = 'submission-' . $submission->peserta_id . '-' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/tugas/submissions', $filename);
                $data['file_jawaban'] = 'tugas/submissions/' . $filename;
            }

            $submission->update($data);

            return redirect()->route('submission.show', $submission->id)
                ->with('success', 'Pengumpulan tugas berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Pengumpulan tugas tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error memperbarui pengumpulan tugas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Grade the specified submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function grade(Request $request, $id)
    {
        try {
            $submission = TugasSubmission::findOrFail($id);

            // Can only grade submitted or late submissions
            if (!in_array($submission->status, ['submitted', 'late'])) {
                return redirect()->back()
                    ->with('error', 'Hanya dapat menilai tugas yang telah dikumpulkan.');
            }

            $validator = Validator::make($request->all(), [
                'nilai' => 'required|integer|min:0|max:100',
                'catatan_penilai' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get current admin/instructor ID from session
            $adminInstrukturId = auth()->guard('admin_instruktur')->id();

            $submission->update([
                'admin_instruktur_id' => $adminInstrukturId,
                'nilai' => $request->nilai,
                'catatan_penilai' => $request->catatan_penilai,
                'tanggal_dinilai' => now(),
                'status' => 'graded',
            ]);

            return redirect()->back()
                ->with('success', 'Tugas berhasil dinilai');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Pengumpulan tugas tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error menilai tugas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Return the submission for revision.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function returnForRevision(Request $request, $id)
    {
        try {
            $submission = TugasSubmission::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'catatan_penilai' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get current admin/instructor ID from session
            $adminInstrukturId = auth()->guard('admin_instruktur')->id();

            $submission->update([
                'admin_instruktur_id' => $adminInstrukturId,
                'catatan_penilai' => $request->catatan_penilai,
                'status' => 'returned',
            ]);

            return redirect()->back()
                ->with('success', 'Tugas berhasil dikembalikan untuk revisi');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Pengumpulan tugas tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error mengembalikan tugas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified submission from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $submission = TugasSubmission::findOrFail($id);

            // Delete file if exists
            if ($submission->file_jawaban) {
                Storage::delete('public/' . $submission->file_jawaban);
            }

            $submission->delete();

            return redirect()->route('submission.index')
                ->with('success', 'Pengumpulan tugas berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Pengumpulan tugas tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Error menghapus pengumpulan tugas: ' . $e->getMessage());
        }
    }

    /**
     * Download submission file.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadFile($id)
    {
        try {
            $submission = TugasSubmission::findOrFail($id);

            if (!$submission->file_jawaban) {
                return redirect()->back()
                    ->with('error', 'Tidak ada file yang dapat diunduh');
            }

            $filePath = storage_path('app/public/' . $submission->file_jawaban);

            if (!file_exists($filePath)) {
                return redirect()->back()
                    ->with('error', 'File tidak ditemukan');
            }

            return response()->download($filePath);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Pengumpulan tugas tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error mengunduh file: ' . $e->getMessage());
        }
    }

    /**
     * Display submissions by peserta.
     *
     * @param  int  $pesertaId
     * @return \Illuminate\Contracts\View\View
     */
    public function byPeserta($pesertaId)
    {
        try {
            $peserta = \Modules\Peserta\Entities\Peserta::findOrFail($pesertaId);
            $submissions = TugasSubmission::with(['tugas', 'penilai'])
                ->where('peserta_id', $pesertaId)
                ->orderBy('tanggal_submit', 'desc')
                ->paginate(15);

            return view('tugas::submissions.by-peserta', compact('peserta', 'submissions'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Peserta tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Error menampilkan pengumpulan tugas peserta: ' . $e->getMessage());
        }
    }

    /**
     * Display submissions for a specific tugas.
     *
     * @param  int  $tugasId
     * @return \Illuminate\Contracts\View\View
     */
    public function byTugas($tugasId)
    {
        try {
            $tugas = Tugas::with('modul')->findOrFail($tugasId);
            $submissions = TugasSubmission::with(['peserta', 'penilai'])
                ->where('tugas_id', $tugasId)
                ->orderBy('tanggal_submit', 'desc')
                ->paginate(15);

            return view('tugas::submissions.by-tugas', compact('tugas', 'submissions'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Tugas tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('submission.index')
                ->with('error', 'Error menampilkan pengumpulan tugas: ' . $e->getMessage());
        }
    }
}
