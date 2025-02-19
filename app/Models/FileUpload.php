<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileUpload extends Model
{
    use HasFactory, SoftDeletes, ActivityLoggable;

    protected $guarded = ['id'];

    protected $appends = ['activity_log'];

    public function uploadable()
    {
        return $this->morphTo();
    }
}
