<?php

namespace App\Models;

use App\Models\BaseModel;
use Carbon\Carbon;

class Reminder extends BaseModel
{
    protected $fillable = [
        'date',
        'reminder_name',
        'purpose',
    ];

    // protected $casts = [
    //     'date' => 'date',
    // ];

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
