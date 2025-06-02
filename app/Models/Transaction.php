<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends BaseModel
{
    use HasFactory;

    protected $table = 'daily_manual_transactions';
    protected $appends = ['formatted_amount', 'transaction_type_label', 'is_income', 'date_for_ui'];


    protected $fillable = [
        'title',
        'amount',
        'deposit',
        'withdrawal',
        'balance',
        'type', // credit or debit
        'mode', // cash, bank transfer, etc.
        'particulars', // details of the transaction
        'bank_id', // foreign key to Bank model
        'imported_id', // ID from imported data if applicable
        'imported_at', // timestamp when imported
        'category', // auto-detected category
        'subcategory', // optional subcategory
        'description', // additional description
        'transaction_type', // manual or imported
        'date', // date of the transaction
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    // Accessors
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function getDateForUiAttribute()
    {
        return Carbon::parse($this->attributes['date'])->format('d-m-Y');
    }

    public function getFormattedAmountAttribute()
    {
        $prefix = $this->type === 'credit' ? '+' : '-';
        return $prefix . '₹' . number_format($this->amount, 2);
    }

    public function getTransactionTypeLabelAttribute()
    {
        return $this->type === 'credit' ? 'Credit' : 'Debit';
    }

    public function getIsIncomeAttribute()
    {
        return $this->type === 'credit';
    }

    public function getExtractedTitleAttribute()
    {
        if ($this->description) {
            return $this->description;
        }
        return $this->title;
    }

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'debit');
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

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods for auto-categorization
    public function autoDetectCategory()
    {
        $title = strtolower($this->title);

        // Income patterns
        if (preg_match('/salary|wage|payroll|income|freelance|bonus/', $title)) {
            return 'salary';
        }

        if (preg_match('/interest|dividend|investment|return/', $title)) {
            return 'investment';
        }

        // Expense patterns
        if (preg_match('/food|restaurant|zomato|swiggy|grocery|meal|lunch|dinner/', $title)) {
            return 'food';
        }

        if (preg_match('/fuel|petrol|diesel|transport|uber|ola|taxi|bus|metro/', $title)) {
            return 'transport';
        }

        if (preg_match('/medical|hospital|pharmacy|doctor|medicine|health/', $title)) {
            return 'healthcare';
        }

        if (preg_match('/electricity|water|gas|internet|mobile|phone|broadband/', $title)) {
            return 'utilities';
        }

        if (preg_match('/amazon|flipkart|shopping|mall|clothing|shoes/', $title)) {
            return 'shopping';
        }

        if (preg_match('/rent|house|home|maintenance|repair/', $title)) {
            return 'housing';
        }

        if (preg_match('/movie|entertainment|games|subscription|netflix|spotify/', $title)) {
            return 'entertainment';
        }

        if (preg_match('/education|course|book|training|certification/', $title)) {
            return 'education';
        }

        return 'others';
    }

    // Boot method to auto-categorize
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->category) {
                $transaction->category = $transaction->autoDetectCategory();
            }

            if (!$transaction->transaction_type) {
                $transaction->transaction_type = $transaction->type;
            }
        });

        static::updating(function ($transaction) {
            if ($transaction->isDirty('title') && !$transaction->isDirty('category')) {
                $transaction->category = $transaction->autoDetectCategory();
            }
        });
    }

    // Helper methods for statistics
    public static function getTotalIncome($startDate = null, $endDate = null)
    {
        $query = self::income();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->sum('amount');
    }

    public static function getTotalExpense($startDate = null, $endDate = null)
    {
        $query = self::expense();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->sum('amount');
    }

    public static function getNetAmount($startDate = null, $endDate = null)
    {
        return self::getTotalIncome($startDate, $endDate) - self::getTotalExpense($startDate, $endDate);
    }

    public static function getCategoryWiseExpenses($startDate = null, $endDate = null)
    {
        $query = self::expense()->selectRaw('category, SUM(amount) as total')
                    ->groupBy('category');

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->get();
    }
}
