<?php

namespace App\Http\Controllers;

use App\Http\Requests\FolderRequest;
use App\Models\Folder;
use App\Models\User;
use App\Services\FileUploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use ZipArchive;
use PDF;

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
        $user = auth()->user();
        $pageSize = $request->input('page_size');
        $filter = $request->input('filter');
        $sortColumn = $request->input('sort_column', 'name');
        $sortDesc = $request->input('sort_desc', false) ? 'desc' : 'asc';
        $departmentId = $request->input('department_id');

        $query = Folder::with(['subfolders', 'fileUploads', 'departments']);

        if ($user->role === 'admin') {
        } else {
            if ($departmentId) {
                $query->whereHas('departments', function ($q) use ($departmentId) {
                    $q->where('departments.id', $departmentId);
                });
            } elseif ($user->departments && $user->departments->isNotEmpty()) {
                $query->whereHas('departments', function ($q) use ($user) {
                    $q->whereIn('departments.id', $user->departments->pluck('id')->toArray());
                });
            } else {
                $query->where('added_by', $user->id);
            }
        }

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('folder_name', 'like', "%{$filter}%")
                    ->orWhere('local_path', 'like', "%{$filter}%")
                    ->orWhereDate('created_at', $filter)
                    ->orWhereHas('departments', function ($q) use ($filter) {
                        $q->where('name', 'like', "%{$filter}%");
                    })
                    ->orWhereHas('subfolders', function ($q) use ($filter) {
                        $q->where('folder_name', 'like', "%{$filter}%");
                    });
            });
        }

        if ($sortColumn && $sortDesc) {
            $sortableFields = ['date_upload', 'folder_name', 'sub_folders', 'local_path'];

            if (in_array($sortColumn, $sortableFields)) {
                $sortOrder = in_array($sortDesc, ['asc', 'desc']) ? $sortDesc : 'asc';

                if ($sortColumn === 'date_upload') {
                    $query->orderByRaw("DATE(created_at) $sortOrder");
                } elseif ($sortColumn === 'folder_name') {
                    $query->orderByRaw("folder_name $sortOrder");
                } elseif ($sortColumn === 'sub_folders') {
                    $query->with(['subfolders' => function ($q) use ($sortOrder) {
                        $q->orderBy('folder_name', $sortOrder);
                    }])->orderBy('folder_name', $sortOrder);
                } elseif ($sortColumn === 'local_path') {
                    $query->orderBy('local_path', $sortOrder);
                }
            }
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

        $validatedData['added_by'] = auth()->id();

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

    public function downloadZip($id)
    {
        $folder = Folder::with(['fileUploads', 'subfolders.fileUploads'])->findOrFail($id);
        $zipFileName = $folder->folder_name . '.zip';
        $tempDir = storage_path('app/public/temp');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $zipPath = $tempDir . '/' . $zipFileName;
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($folder->fileUploads as $file) {
                $filePath = storage_path('app/public/uploads/' . basename($file->path));
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                }
            }

            $this->addSubfoldersToZip($folder, $zip, $folder->folder_name);
            $zip->close();

            return response()->json([
                'status' => 'success',
                'message' => __('messages.success.zip_created'),
                'data' => asset('storage/temp/' . $zipFileName),
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Failed to create ZIP file.'], 500);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function addSubfoldersToZip($folder, $zip, $zipPath)
    {
        foreach ($folder->subfolders as $subfolder) {
            $subfolderPath = $zipPath . '/' . $subfolder->folder_name;

            $zip->addEmptyDir($subfolderPath);

            foreach ($subfolder->fileUploads as $file) {
                $filePath = storage_path('app/public/uploads/' . basename($file->path));
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $subfolderPath . '/' . basename($filePath));
                }
            }

            // Recursively add subfolders
            if ($subfolder->subfolders->isNotEmpty()) {
                $this->addSubfoldersToZip($subfolder, $zip, $subfolderPath);
            }
        }
    }

    public function generateReport(Request $request)
    {
        $user = auth()->user();
        $user->load(['department', 'designation']);

        $checkedBy = User::with(['department', 'designation'])->where('id', $request->checked_by)->first();

        $folders = Folder::with(['departments', 'fileUploads', 'subfolders.fileUploads'])->whereIn('id', $request->selected_folders)->get();

        $effectiveDate = $request->effective_date;

        $reportData = [
            'user' => $user,
            'checkedBy' => $checkedBy,
            'folders' => $folders,
            'effectiveDate' => $effectiveDate,
        ];

        $pdf = PDF::loadView('report', ['reportData' => $reportData]);

        return $pdf->download('records_digitization_report.pdf');
    }
}
