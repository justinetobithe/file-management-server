<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $guareded = ['id'];

    public function designation()
    {
        return $this->belongsTo(User::class);
    }
}
