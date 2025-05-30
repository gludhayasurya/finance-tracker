<?php

namespace App\Models;

use App\Models\BaseModel;

class Category extends BaseModel
{
    protected $fillable = [
        'name',
        'type',
        'icon',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
