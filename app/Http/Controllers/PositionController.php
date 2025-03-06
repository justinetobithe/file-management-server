<?php

namespace App\Http\Controllers;

use App\Http\Requests\PositionRequest;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function store(PositionRequest $request)
    {
        $validated = $request->validated();

        if ($validated['section_head'] ?? false) {
            Position::where('department_id', $validated['department_id'])->update(['section_head' => false]);
        }

        $position = Position::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.created'),
            'data' => $position,
        ]);
    }

    public function update(PositionRequest $request, string $id)
    {
        $validated = $request->validated();
        $position = Position::findOrFail($id);

        if ($validated['section_head'] ?? false) {
            Position::where('department_id', $validated['department_id'])
                ->where('id', '!=', $id)
                ->update(['section_head' => false]);
        }

        $position->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.updated'),
            'data' => $position,
        ]);
    }

    public function destroy(string $id)
    {
        $position = Position::findOrFail($id);
        $position->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.deleted'),
            'data' => $position,
        ]);
    }
}
