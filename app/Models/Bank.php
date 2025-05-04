<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'address',
        'initial_balance',
        'current_balance',
        'fa_icon',
        'icon_color',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
}
