<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'address',
        'initial_balance',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
}
