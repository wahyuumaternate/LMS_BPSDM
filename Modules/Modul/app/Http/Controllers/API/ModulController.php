<?php

namespace Modules\Modul\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Modul\Entities\Modul;
use Modules\Modul\Transformers\ModulResource;
use Illuminate\Support\Facades\Validator;

class ModulController extends Controller
{
    public function index(Request $request)
    {
        $query = Modul::with(['kursus']);

        // Filter by kursus_id
        if ($request->has('kursus_id')) {
            $query->where('kursus_id', $request->kursus_id);
        }

        // Filter by is_published
        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        // Order by urutan
        $query->orderBy('urutan');

        $moduls = $query->get();

        return ModulResource::collection($moduls);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'nama_modul' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If urutan not provided, set it to the last position
        if (!$request->filled('urutan')) {
            $lastUrutan = Modul::where('kursus_id', $request->kursus_id)
                ->max('urutan');
            $request->merge(['urutan' => $lastUrutan + 1]);
        }

        $modul = Modul::create($request->all());

        return response()->json([
            'message' => 'Modul created successfully',
            'data' => new ModulResource($modul)
        ], 201);
    }

    public function show($id)
    {
        $modul = Modul::with(['kursus', 'materis'])->findOrFail($id);
        return new ModulResource($modul);
    }

    public function update(Request $request, $id)
    {
        $modul = Modul::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kursus_id' => 'sometimes|required|exists:kursus,id',
            'nama_modul' => 'sometimes|required|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $modul->update($request->all());

        return response()->json([
            'message' => 'Modul updated successfully',
            'data' => new ModulResource($modul)
        ]);
    }

    public function destroy($id)
    {
        $modul = Modul::findOrFail($id);

        // This will also delete all materis due to onDelete('cascade')
        $modul->delete();

        return response()->json([
            'message' => 'Modul deleted successfully'
        ]);
    }

    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kursus_id' => 'required|exists:kursus,id',
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:moduls,id',
            'modules.*.urutan' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if all modules belong to the specified kursus
        $moduleIds = collect($request->modules)->pluck('id')->toArray();
        $invalidModules = Modul::whereIn('id', $moduleIds)
            ->where('kursus_id', '!=', $request->kursus_id)
            ->exists();

        if ($invalidModules) {
            return response()->json([
                'message' => 'Some modules do not belong to the specified kursus.'
            ], 422);
        }

        // Update urutan for each module
        foreach ($request->modules as $module) {
            Modul::where('id', $module['id'])
                ->update(['urutan' => $module['urutan']]);
        }

        return response()->json([
            'message' => 'Modules reordered successfully'
        ]);
    }
}
