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

    protected $appends = ['activity_log'];

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
}
