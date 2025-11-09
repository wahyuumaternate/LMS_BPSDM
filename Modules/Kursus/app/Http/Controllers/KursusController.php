<?php

namespace Modules\Kursus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Kursus\Entities\Kursus;

class KursusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function table(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        // $perPage = max(5, min(100, (int)$perPage));

        $query = Kursus::with(['kategori', 'adminInstruktur']);

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['draft', 'aktif', 'nonaktif', 'selesai'])) {
            $query->where('status', $request->status);
        }

        // Filter by kategori
        if ($request->has('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter by level
        if ($request->has('level') && in_array($request->level, ['dasar', 'menengah', 'lanjut'])) {
            $query->where('level', $request->level);
        }

        // Filter by tipe
        if ($request->has('tipe') && in_array($request->tipe, ['daring', 'luring', 'hybrid'])) {
            $query->where('tipe', $request->tipe);
        }

        // Filter by instruktur
        if ($request->has('admin_instruktur_id')) {
            $query->where('admin_instruktur_id', $request->admin_instruktur_id);
        }

        // Search
        if ($request->search) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        $kursus = $query->paginate($perPage);

        return view('kursus::partial.table', compact('kursus'));
    }

    public function index()
    {
        return view('kursus::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kursus::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('kursus::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('kursus::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kursus = Kursus::findOrFail($id);
        $kursus->delete();
    }
}
