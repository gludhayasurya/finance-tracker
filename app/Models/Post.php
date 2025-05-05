<?php

namespace App\Models;

use App\Models\BaseModel;

class Post extends BaseModel
{
    protected $fillable = ['title', 'content','is_admin'];
}
