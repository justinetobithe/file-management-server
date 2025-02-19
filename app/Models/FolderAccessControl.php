<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FolderAccessControl extends Model
{
    use HasFactory, SoftDeletes, ActivityLoggable;

    protected $appends = ['activity_log'];

    protected $guarded = ['id'];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
