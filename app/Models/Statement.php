<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Statement extends BaseModel
{
    protected $table = 'statement_transactions';
    protected $appends = ['extracted_particular', 'formatted_amount', 'transaction_type_label'];

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
        'category',
        'subcategory',
        'description',
        'transaction_type',
    ];

    protected $casts = [
        'date' => 'date',
        'deposit' => 'decimal:2',
        'withdrawal' => 'decimal:2',
        'balance' => 'decimal:2',
        'imported_at' => 'datetime',
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    // Accessors
    public function getExtractedParticularAttribute()
    {
        if ($this->description) {
            return $this->description;
        }

        $parts = Str::of($this->particulars)->explode('/');
        $second = $parts->get(2);
        $third = $parts->get(3);

        if (is_null($second) && is_null($third)) {
            return $this->particulars;
        }

        return trim($this->particulars);
    }

    public function getFormattedAmountAttribute()
    {
        if ($this->deposit > 0) {
            return '+₹' . number_format($this->deposit, 2);
        }
        return '-₹' . number_format($this->withdrawal, 2);
    }

    public function getTransactionTypeLabelAttribute()
    {
        return $this->deposit > 0 ? 'Credit' : 'Debit';
    }

    public function getIsIncomeAttribute()
    {
        return $this->deposit > 0;
    }

    public function getAmountAttribute()
    {
        return $this->deposit > 0 ? $this->deposit : $this->withdrawal;
    }

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('deposit', '>', 0);
    }

    public function scopeExpense($query)
    {
        return $query->where('withdrawal', '>', 0);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereMonth('date', Carbon::now()->subMonth()->month)
                    ->whereYear('date', Carbon::now()->subMonth()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('date', Carbon::now()->year);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByBank($query, $bankId)
    {
        return $query->where('bank_id', $bankId);
    }

    // Helper methods for auto-categorization
    public function autoDetectCategory()
    {
        $particulars = strtolower($this->particulars);

        // Income patterns
        if (preg_match('/salary|wage|payroll|income/', $particulars)) {
            return 'salary';
        }

        // Expense patterns
        if (preg_match('/food|restaurant|zomato|swiggy|grocery/', $particulars)) {
            return 'food';
        }

        if (preg_match('/fuel|petrol|diesel|transport|uber|ola/', $particulars)) {
            return 'transport';
        }

        if (preg_match('/medical|hospital|pharmacy|doctor/', $particulars)) {
            return 'healthcare';
        }

        if (preg_match('/electricity|water|gas|internet|mobile/', $particulars)) {
            return 'utilities';
        }

        if (preg_match('/amazon|flipkart|shopping|mall/', $particulars)) {
            return 'shopping';
        }

        return 'others';
    }

    // Boot method to auto-categorize
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($statement) {
            if (!$statement->category) {
                $statement->category = $statement->autoDetectCategory();
            }

            if (!$statement->transaction_type) {
                $statement->transaction_type = $statement->deposit > 0 ? 'credit' : 'debit';
            }
        });
    }
}
