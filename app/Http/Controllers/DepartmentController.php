<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $pageSize = $request->input('page_size');
        $filter = $request->input('filter');
        $sortColumn = $request->input('sort_column', 'folder_name');
        $sortDesc = $request->input('sort_desc', false) ? 'desc' : 'asc';

        $query = Department::query();

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%");
            });
        }

        if (in_array($sortColumn, ['name',])) {
            $query->orderBy($sortColumn, $sortDesc);
        }

        if ($pageSize) {
            $departments = $query->paginate($pageSize);
        } else {
            $departments = $query->get();
        }

        return $this->success($departments);
    }

    public function show(Department $department)
    {
        return $this->success(['status' => true, 'data' => $department]);
    }

    public function store(DepartmentRequest $request)
    {
        $department = Department::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.created'),
            'data' => $department,
        ]);
    }

    public function update(DepartmentRequest $request, string $id)
    {
        $department = Department::findOrFail($id);

        $department->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.updated'),
            'data' => $department,
        ]);
    }

    public function destroy(string $id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.deleted'),
            'data' => $department,
        ]);
    }
}
