<?php

namespace Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Forum\Entities\Forum;
use Modules\Kursus\Entities\Kursus;

class ForumController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kursus_id' => 'required|exists:kursus,id',
                'judul' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'platform' => 'required|in:telegram,whatsapp,discord,other',
                'link_grup' => 'required|url',
                'is_aktif' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $data = $request->except(['_token']);
            $data['is_aktif'] = $request->has('is_aktif');

            Forum::create($data);

            return redirect()->route('course.forum', $request->kursus_id)->with('success', 'Forum berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error membuat forum: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $forum = Forum::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'kursus_id' => 'required|exists:kursus,id',
                'judul' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'platform' => 'required|in:telegram,whatsapp,discord,other',
                'link_grup' => 'required|url',
                'is_aktif' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $data = $request->except(['_token', '_method']);
            $data['is_aktif'] = $request->has('is_aktif');

            $forum->update($data);

            return redirect()->route('course.forum', $forum->kursus_id)->with('success', 'Forum berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error mengupdate forum: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $forum = Forum::with('kursus')->findOrFail($id);
            $kursusId = $forum->kursus_id;

            $forum->delete();

            return response()->json([
                'success' => true,
                'message' => 'Forum berhasil dihapus',
                'redirect' => route('course.forum', $kursusId),
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error menghapus forum: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
