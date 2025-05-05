<?php

namespace App\Models;

use Carbon\Carbon;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends BaseModel
{
    use HasFactory;
    protected $fillable = [
        'title',
        'amount',
        'type',
        'date',
        'bank_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    // Accessor to format the date as dd-mm-YYYY
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function getDateForUiAttribute()
    {
        return Carbon::parse($this->attributes['date'])->format('d-m-Y');
    }

}
