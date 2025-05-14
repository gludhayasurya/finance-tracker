<?php

namespace App\Models;

use App\Models\BaseModel;

class Import extends BaseModel
{
    protected $table = 'imports';

    protected $fillable = [

        'filename',
        'filepath',
        'total_withdrawal',
        'total_deposit',
        'total_balance',
        'status',
        'bank_id',
    ];


    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function transactions()
    {
        return $this->hasMany(Statement::class, 'imported_id', 'id');
    }
}
