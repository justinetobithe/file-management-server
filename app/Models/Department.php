<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes, ActivityLoggable;

    protected $guarded = ['id'];
 
    protected $appends = ['activity_log'];

    public function folders()
    {
        return $this->belongsToMany(Folder::class, 'folder_access_controls');
    }
}
