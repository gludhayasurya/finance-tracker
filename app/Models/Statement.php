<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Str;


class Statement extends BaseModel
{

    protected $table = 'statement_transactions';
    protected $appends = ['extracted_particular'];


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


    public function getExtractedParticularAttribute()
    {
        $parts = Str::of($this->particulars)->explode('/');
        $second = $parts->get(2);
        $third = $parts->get(3);

        return trim($second);
        // return trim("$second && $third");
    }
}
