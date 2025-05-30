<?php

namespace App\Models;

use App\Models\BaseModel;

class FinancialGoal extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'target_amount',
        'current_amount',
        'target_date',
        'priority',
        'is_achieved',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
        'is_achieved' => 'boolean',
    ];

    public function getRemainingAmountAttribute()
    {
        return $this->target_amount - $this->current_amount;
    }

    public function getProgressPercentageAttribute()
    {
        return $this->target_amount > 0 ? ($this->current_amount / $this->target_amount) * 100 : 0;
    }

    public function getDaysRemainingAttribute()
    {
        return now()->diffInDays($this->target_date, false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_achieved', false);
    }

    public function scopeAchieved($query)
    {
        return $query->where('is_achieved', true);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}
