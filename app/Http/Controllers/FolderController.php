<?php

namespace App\Http\Controllers;

use App\Http\Requests\FolderRequest;
use App\Models\Folder;
use App\Models\Position;
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
        $page = $request->input('page', 1);
        $pageSize = $request->input('page_size', 10);
        $search = $request->input('search');
        $filter = $request->input('filter');
        $sortBy = $request->input('sort_by', 'date_upload');
        $sortDesc = filter_var($request->input('sort_desc', false), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $departmentId = $request->input('department_id');
        $status = $request->input('status');  

        $query = Folder::with(['subfolders', 'fileUploads', 'department', 'addedBy']);

        if ($user->role !== 'admin') {
            $user->load('position.department');
            if ($user->position && $user->position->section_head === 1) {
                $query->where('department_id', $user->position->department_id);
            } else {
                $query->where('added_by', $user->id);
            }
        }

        $allFolders = filter_var($request->input('all_folders', false), FILTER_VALIDATE_BOOLEAN);
        if (!$allFolders) {
            $query->whereNull('parent_id');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('folder_name', 'like', "%{$search}%")
                    ->orWhere('local_path', 'like', "%{$search}%")
                    ->orWhereDate('created_at', $search)
                    ->orWhereHas('subfolders', function ($q) use ($search) {
                        $q->where('folder_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('fileUploads', function ($q) use ($search) {
                        $q->where('filename', 'like', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($sortBy && $sortDesc) {
            $sortableFields = ['date_upload', 'folder_name', 'sub_folders', 'local_path'];

            if (in_array($sortBy, $sortableFields)) {
                $sortOrder = in_array($sortDesc, ['asc', 'desc']) ? $sortDesc : 'asc';

                if ($sortBy === 'date_upload') {
                    $query->orderBy('created_at', $sortOrder);
                } elseif ($sortBy === 'folder_name') {
                    $query->orderByRaw("folder_name $sortOrder");
                } elseif ($sortBy === 'sub_folders') {
                    $query->with(['subfolders' => function ($q) use ($sortOrder) {
                        $q->orderBy('folder_name', $sortOrder);
                    }])->orderBy('folder_name', $sortOrder);
                } elseif ($sortBy === 'local_path') {
                    $query->orderBy('local_path', $sortOrder);
                }
            }
        }

        $folders = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        $folders->getCollection()->transform(function ($folder) {
            $folder->files = $folder->fileUploads;
            unset($folder->fileUploads);
            return $folder;
        });

        return $this->success([
            'data' => $folders->items(),
            'current_page' => $folders->currentPage(),
            'last_page' => $folders->lastPage(),
            'per_page' => $folders->perPage(),
            'total' => $folders->total(),
            'next_page_url' => $folders->nextPageUrl(),
            'prev_page_url' => $folders->previousPageUrl(),
        ]);
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

        $user = auth()->user();
        $user->load('position');

        $isSectionHead = $user->position && $user->position->section_head === 1;

        $validatedData['status'] = $isSectionHead ? 'approved' : 'pending';

        $folder = Folder::create($validatedData);

        // if ($request->has('department_id')) {
        //     $folder->departments()->sync($request->department_id);
        // }

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

        $folder->update($request->all());

        // if ($request->has('department_id')) {
        //     $folder->departments()->sync($request->department_id);
        // }

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
                    // $zip->addFile($filePath, basename($filePath));
                    $zip->addFile($filePath, $file->filename);
                }
            }

            $this->addSubfoldersToZip($folder, $zip, $folder->folder_name);
            $zip->close();

            return response()->json([
                'status' => 'success',
                'message' => __('messages.success.zip_created'),
                'data' => asset('storage/app/public/temp/' . $zipFileName),
                // 'data' => asset('storage/temp/' . $zipFileName),
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
                    // $zip->addFile($filePath, $subfolderPath . '/' . basename($filePath));
                    $zip->addFile($filePath, $subfolderPath . '/' . $file->filename);
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
        $user->load(['position.department', 'position.designation']);

        $departmentId = $user->position->department->id;

        $positions = Position::where('department_id', $departmentId)->get();

        $sectionHead = $positions->where('section_head', true)->first();

        if ($sectionHead) {
            $checkedBy = User::whereHas('position', function ($query) use ($sectionHead) {
                $query->where('id', $sectionHead->id);
            })->with(['position.department', 'position.designation'])->first();
        } else {
            $checkedBy = User::whereHas('position', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })->with(['position.department', 'position.designation'])->first();
        }

        $checkedBy = $checkedBy ?? null;

        $folders = Folder::with(['department', 'fileUploads', 'subfolders.fileUploads'])
            ->whereIn('id', $request->selected_folders)
            ->get();

        $effectiveDate = $request->effective_date;

        $reportData = [
            'user' => $user,
            'checkedBy' => $checkedBy,
            'folders' => $folders,
            'effectiveDate' => $effectiveDate,
        ];

        // return $reportData;

        $pdf = PDF::loadView('report', ['reportData' => $reportData]);

        return $pdf->download('records_digitization_report.pdf');
    }

    public function approve($id)
    {
        $folder = Folder::findOrFail($id);

        if ($folder->status === 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Folder is already approved.',
            ], 400);
        }

        $folder->status = 'approved';
        $folder->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Folder approved successfully.',
            'data' => $folder,
        ]);
    }

    public function reject($id)
    {
        $folder = Folder::findOrFail($id);

        if ($folder->status === 'rejected') {
            return response()->json([
                'status' => 'error',
                'message' => 'Folder is already rejected.',
            ], 400);
        }

        $folder->status = 'rejected';
        $folder->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Folder rejected successfully.',
            'data' => $folder,
        ]);
    }

    public function addFolder(FolderRequest $request)
    {
        $validatedData = $request->validated();

        unset($validatedData['uploaded_files']);

        $validatedData['added_by'] = auth()->id();
        $user = auth()->user();
        $user->load('position');

        $isSectionHead = $user->position && $user->position->section_head === 1;
        $validatedData['status'] = $isSectionHead ? 'approved' : 'pending';

        $existingFolder = Folder::where('folder_name', $validatedData['folder_name'])
            ->whereNull('department_id')
            ->first();

        if ($existingFolder) {
            return response()->json([
                'status' => 'error',
                'message' => 'A folder with this name already exists without a department.',
            ], 422);
        }

        $folder = Folder::create($validatedData);

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

    public function addSubfolder(FolderRequest $request, string $parentFolderId)
    {
        $validatedData = $request->validated();

        $existingFolder = Folder::where('department_id', $validatedData['department_id'])
            ->where('parent_id', $parentFolderId)
            ->where('folder_name', $validatedData['folder_name'])
            ->first();

        if ($existingFolder) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.error.folder_exists'),
            ], 400);
        }

        $parentFolder = Folder::findOrFail($parentFolderId);

        $validatedData['parent_id'] = $parentFolder->id;
        $validatedData['added_by'] = auth()->id();

        $subfolder = Folder::create($validatedData);

        if ($request->hasFile('uploaded_files')) {
            $files = $request->file('uploaded_files');
            $this->fileUploadService->uploadFiles($subfolder, $files);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.created'),
            'data' => $subfolder,
        ]);
    }


    public function updateSubfolder(FolderRequest $request, string $id)
    {
        $subfolder = Folder::findOrFail($id);

        $oldFilenames = $subfolder->fileUploads->pluck('filename')->toArray();
        $newFilenames = $oldFilenames;

        $subfolder->update($request->all());

        if ($request->hasFile('uploaded_files')) {
            $files = $request->file('uploaded_files');
            $this->fileUploadService->uploadFiles($subfolder, $files);
            $newFilenames = $subfolder->fresh()->fileUploads->pluck('filename')->toArray();
        }

        if ($request->current_files) {
            $this->fileUploadService->deleteFiles($subfolder, json_decode($request->current_files));
            $newFilenames = $subfolder->fresh()->fileUploads->pluck('filename')->toArray();
        }

        if ($oldFilenames !== $newFilenames) {
            $logData = [
                'old' => ['filename' => $oldFilenames],
                'attributes' => ['filename' => $newFilenames],
            ];

            Activity::create([
                'log_name' => 'default',
                'description' => 'updated',
                'subject_type' => Folder::class,
                'event' => 'updated',
                'subject_id' => $subfolder->id,
                'causer_type' => User::class,
                'causer_id' => auth()->id(),
                'properties' => $logData,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('messages.success.updated'),
            'data' => $subfolder,
        ]);
    }
}
