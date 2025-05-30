<?php

namespace App\Models;

use App\Models\BaseModel;
use Carbon\Carbon;

class Budget extends BaseModel
{
    protected $fillable = [
        'name',
        'amount',
        'spent',
        'period',
        'start_date',
        'end_date',
        'category_id',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getRemainingAttribute()
    {
        return $this->amount - $this->spent;
    }

    public function getPercentageUsedAttribute()
    {
        return $this->amount > 0 ? ($this->spent / $this->amount) * 100 : 0;
    }

    public function isOverBudget()
    {
        return $this->spent > $this->amount;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now());
    }
}
