<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, SoftDeletes, ActivityLoggable;

    protected $guarded = ['id'];

    protected $appends = ['activity_log', 'total_size'];

    public function subfolders()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function fileUploads()
    {
        return $this->morphMany(FileUpload::class, 'uploadable');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'folder_access_controls')->withTimestamps();
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by', 'id');
    }

    public function getTotalSizeAttribute()
    {
        $totalSize = 0;

        foreach ($this->fileUploads as $file) {
            $totalSize += $file->size ?? 0;
        }

        if ($totalSize < 1024) {
            return number_format($totalSize, 2) . " bytes";
        } elseif ($totalSize < 1048576) {
            return number_format($totalSize / 1024, 2) . " KB";
        } elseif ($totalSize < 1073741824) {
            return number_format($totalSize / 1048576, 2) . " MB";
        } elseif ($totalSize < 1099511627776) {
            return number_format($totalSize / 1073741824, 2) . " GB";
        } else {
            return number_format($totalSize / 1099511627776, 2) . " TB";
        }
    }
}
