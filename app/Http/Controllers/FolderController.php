<?php

namespace App\Http\Controllers;

use App\Http\Requests\FolderRequest;
use App\Models\Folder;
use App\Models\User;
use App\Services\FileUploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class FolderController extends Controller
{
    use ApiResponse;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request)
    {
        $pageSize = $request->input('page_size');
        $filter = $request->input('filter');
        $sortColumn = $request->input('sort_column', 'name');
        $sortDesc = $request->input('sort_desc', false) ? 'desc' : 'asc';

        $query = Folder::with(['subfolders', 'fileUploads', 'departments']);

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%");
            });
        }

        if (in_array($sortColumn, ['folder_name', 'local_path', 'start_date', 'end_date'])) {
            $query->orderBy($sortColumn, $sortDesc);
        }

        if ($pageSize) {
            $folders = $query->paginate($pageSize);

            $folders->getCollection()->transform(function ($folder) {
                $folder->files = $folder->fileUploads;
                unset($folder->fileUploads);
                return $folder;
            });
        } else {
            $folders = $query->get();
        }

        return $this->success($folders);
    }

    public function show(string $id)
    {
        $folder = Folder::with(['fileUploads'])->findOrFail($id);

        $folder->files = $folder->fileUploads;
        unset($folder->fileUploads);

        return response()->json([
            'status' => 'success',
            'data' => $folder,
        ]);
    }

    public function store(FolderRequest $request)
    {
        $validatedData = $request->validated();

        unset($validatedData['uploaded_files']);

        $folder = Folder::create($validatedData);

        if ($request->has('department_id')) {
            $folder->departments()->sync($request->department_id);
        }

        if ($request->hasFile('uploaded_files')) {
            $files = $request->file('uploaded_files');
            $this->fileUploadService->uploadFiles($folder, $files);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.created'),
            'data' => $folder,
        ]);
    }

    public function update(FolderRequest $request, string $id)
    {
        $folder = Folder::findOrFail($id);

        $oldFilenames = $folder->fileUploads->pluck('filename')->toArray();
        $newFilenames = $oldFilenames;

        if ($request->hasFile('uploaded_files')) {
            $files = $request->file('uploaded_files');
            $this->fileUploadService->uploadFiles($folder, $files);
            $newFilenames = $folder->fresh()->fileUploads->pluck('filename')->toArray();
        }

        if ($request->current_files) {
            $this->fileUploadService->deleteFiles($folder, json_decode($request->current_files));
            $newFilenames = $folder->fresh()->fileUploads->pluck('filename')->toArray();
        }

        if ($oldFilenames !== $newFilenames) {
            $logData = [
                'old' => ['filename' => $oldFilenames],
                'attributes' => ['filename' => $newFilenames],
            ];

            $activity = Activity::create([
                'log_name' => 'default',
                'description' => 'updated',
                'subject_type' => Folder::class,
                'event' => 'updated',
                'subject_id' => $folder->id,
                'causer_type' => User::class,
                'causer_id' => auth()->id(),
                'properties' => $logData,
            ]);

            $activity->save();
        }

        $folder->update($request->except('departments'));

        if ($request->has('department_id')) {
            $folder->departments()->sync($request->department_id);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.updated'),
            'data' => $folder,
        ]);
    }

    public function destroy(string $id)
    {
        $folder = Folder::findOrFail($id);
        $folder->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.deleted'),
            'data' => $folder,
        ]);
    }
}
