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

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }


    public function getExtractedParticularAttribute()
    {
        $parts = Str::of($this->particulars)->explode('/');
        $second = $parts->get(2);
        $third = $parts->get(3);

        if (is_null($second) && is_null($third)) {
            return $this->particulars;
        }

        return trim($this->particulars);
        // return trim("$second && $third");
    }
}
