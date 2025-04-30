<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'amount',
        'type',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

}
