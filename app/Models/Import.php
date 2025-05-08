<?php

namespace App\Models;

use App\Models\BaseModel;

class Import extends BaseModel
{
    protected $fillable = [
        'filename',
        'filepath',
        'status',
    ];



}
