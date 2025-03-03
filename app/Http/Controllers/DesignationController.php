<?php

namespace App\Http\Controllers;

use App\Http\Requests\DesignationRequest;
use App\Models\Designation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $pageSize = $request->input('page_size');
        $filter = $request->input('filter');
        $sortColumn = $request->input('sort_column', 'designation');
        $sortDesc = $request->input('sort_desc', false) ? 'desc' : 'asc';

        $query = Designation::query();

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('designation', 'like', "%{$filter}%")
                    ->orWhere('description', 'like', "%{$filter}%");
            });
        }

        if (in_array($sortColumn, ['designation', 'description'])) {
            $query->orderBy($sortColumn, $sortDesc);
        }

        if ($pageSize) {
            $designations = $query->paginate($pageSize);
        } else {
            $designations = $query->get();
        }

        return $this->success($designations);
    }

    public function show(Designation $designation)
    {
        return $this->success(['status' => true, 'data' => $designation]);
    }

    public function store(DesignationRequest $request)
    {
        $existingDesignation = Designation::withTrashed()
            ->where('designation', $request->designation)
            ->first();

        if ($existingDesignation) {
            $existingDesignation->restore();
            $existingDesignation->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => __('messages.success.restored'),
                'data' => $existingDesignation,
            ]);
        }

        $designation = Designation::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.created'),
            'data' => $designation,
        ]);
    }


    public function update(DesignationRequest $request, string $id)
    {
        $designation = Designation::findOrFail($id);

        $designation->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.updated'),
            'data' => $designation,
        ]);
    }

    public function destroy(string $id)
    {
        $designation = Designation::findOrFail($id);
        $designation->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.deleted'),
            'data' => $designation,
        ]);
    }
}
