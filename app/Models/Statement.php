<?php

namespace App\Models;

use App\Models\BaseModel;

class Statement extends BaseModel
{

    protected $table = 'statement_transactions';

    protected $fillable = [
        'date',
        'mode',
        'particulars',
        'deposit',
        'withdrawal',
        'balance',
        'bank_id',
        'imported_id',
        'imported_at',

    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
