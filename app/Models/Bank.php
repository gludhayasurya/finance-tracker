<?php

namespace App\Models;

use App\Models\BaseModel;

class Bank extends BaseModel
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
    public function imports()
    {
        return $this->hasMany(Import::class);
    }
    public function statements()
    {
        return $this->hasMany(Statement::class);
    }


    // public function transformAudit(array $data): array
    // {
    //     $data['new_values']['location_name'] = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
    //     $data['old_values']['location_name'] = "gggggggggggggggggggggggggggg";

    //     return $data;
    // }
}
