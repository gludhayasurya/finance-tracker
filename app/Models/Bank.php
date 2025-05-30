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
        'bank_type',
        'account_number',
        'status',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('bank_type', $type);
    }

    // Helper methods
    public function getBalanceChangeAttribute()
    {
        return $this->current_balance - $this->initial_balance;
    }

    public function getBalanceChangePercentageAttribute()
    {
        if ($this->initial_balance == 0) return 0;
        return (($this->current_balance - $this->initial_balance) / abs($this->initial_balance)) * 100;
    }

    public function getTotalDepositsAttribute()
    {
        return $this->statements()->sum('deposit');
    }

    public function getTotalWithdrawalsAttribute()
    {
        return $this->statements()->sum('withdrawal');
    }
}
