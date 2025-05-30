<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Statement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalBalance' => $this->getTotalBalance(),
            'totalIncome' => $this->getTotalIncome(),
            'totalExpenses' => $this->getTotalExpenses(),
            'activeAccounts' => $this->getActiveAccounts(),
            'monthlyTrend' => $this->getMonthlyTrend(),
            'expensesByCategory' => $this->getExpensesByCategory(),
            'recentTransactions' => $this->getRecentTransactions(),
            'topExpenseCategories' => $this->getTopExpenseCategories(),
            'incomeVsExpense' => $this->getIncomeVsExpenseChart(),
            'accountBalances' => $this->getAccountBalances(),
            'cashFlow' => $this->getCashFlow(),
        ];

        return view('dashboard', compact('data'));
    }

    private function getTotalBalance()
    {
        return Bank::sum('current_balance');
    }

    private function getTotalIncome()
    {
        return Statement::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->where('deposit', '>', 0)
            ->sum('deposit');
    }

    private function getTotalExpenses()
    {
        return Statement::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->where('withdrawal', '>', 0)
            ->sum('withdrawal');
    }

    private function getActiveAccounts()
    {
        return Bank::count();
    }

    private function getMonthlyTrend()
    {
        $months = [];
        $income = [];
        $expenses = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');

            $monthIncome = Statement::whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->where('deposit', '>', 0)
                ->sum('deposit');

            $monthExpenses = Statement::whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->where('withdrawal', '>', 0)
                ->sum('withdrawal');

            $months[] = $monthName;
            $income[] = $monthIncome;
            $expenses[] = $monthExpenses;
        }

        return [
            'months' => $months,
            'income' => $income,
            'expenses' => $expenses
        ];
    }

    private function getExpensesByCategory()
    {
        // Fixed version using subquery approach
        $categories = DB::table(
            DB::raw('(SELECT
                withdrawal,
                CASE
                    WHEN LOWER(particulars) LIKE "%food%" OR LOWER(particulars) LIKE "%restaurant%" OR LOWER(particulars) LIKE "%grocery%" THEN "Food & Dining"
                    WHEN LOWER(particulars) LIKE "%fuel%" OR LOWER(particulars) LIKE "%petrol%" OR LOWER(particulars) LIKE "%transport%" THEN "Transportation"
                    WHEN LOWER(particulars) LIKE "%shopping%" OR LOWER(particulars) LIKE "%amazon%" OR LOWER(particulars) LIKE "%flipkart%" THEN "Shopping"
                    WHEN LOWER(particulars) LIKE "%medical%" OR LOWER(particulars) LIKE "%hospital%" OR LOWER(particulars) LIKE "%pharmacy%" THEN "Healthcare"
                    WHEN LOWER(particulars) LIKE "%electricity%" OR LOWER(particulars) LIKE "%water%" OR LOWER(particulars) LIKE "%gas%" THEN "Utilities"
                    ELSE "Others"
                END as category
                FROM statement_transactions
                WHERE withdrawal > 0
                    AND MONTH(date) = ' . Carbon::now()->month . '
                    AND YEAR(date) = ' . Carbon::now()->year . '
            ) as categorized_transactions')
        )
        ->select('category', DB::raw('SUM(withdrawal) as amount'))
        ->groupBy('category')
        ->orderBy('amount', 'desc')
        ->get();

        return $categories;
    }

    // Alternative cleaner approach using Eloquent collection methods
    private function getExpensesByCategoryAlternative()
    {
        $transactions = Statement::where('withdrawal', '>', 0)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->get();

        return $transactions->groupBy(function ($transaction) {
            $particulars = strtolower($transaction->particulars);

            if (str_contains($particulars, 'food') ||
                str_contains($particulars, 'restaurant') ||
                str_contains($particulars, 'grocery')) {
                return 'Food & Dining';
            }

            if (str_contains($particulars, 'fuel') ||
                str_contains($particulars, 'petrol') ||
                str_contains($particulars, 'transport')) {
                return 'Transportation';
            }

            if (str_contains($particulars, 'shopping') ||
                str_contains($particulars, 'amazon') ||
                str_contains($particulars, 'flipkart')) {
                return 'Shopping';
            }

            if (str_contains($particulars, 'medical') ||
                str_contains($particulars, 'hospital') ||
                str_contains($particulars, 'pharmacy')) {
                return 'Healthcare';
            }

            if (str_contains($particulars, 'electricity') ||
                str_contains($particulars, 'water') ||
                str_contains($particulars, 'gas')) {
                return 'Utilities';
            }

            return 'Others';
        })->map(function ($group, $category) {
            return (object) [
                'category' => $category,
                'amount' => $group->sum('withdrawal')
            ];
        })->sortByDesc('amount')->values();
    }

    private function getRecentTransactions()
    {
        return Statement::with('bank')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTopExpenseCategories()
    {
        return $this->getExpensesByCategory()->take(5);
    }

    private function getIncomeVsExpenseChart()
    {
        $last7Days = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $date->format('M d');

            $dayIncome = Statement::whereDate('date', $date)
                ->where('deposit', '>', 0)
                ->sum('deposit');

            $dayExpenses = Statement::whereDate('date', $date)
                ->where('withdrawal', '>', 0)
                ->sum('withdrawal');

            $last7Days[] = $dayName;
            $incomeData[] = $dayIncome;
            $expenseData[] = $dayExpenses;
        }

        return [
            'days' => $last7Days,
            'income' => $incomeData,
            'expenses' => $expenseData
        ];
    }

    private function getAccountBalances()
    {
        return Bank::select('name', 'current_balance', 'fa_icon', 'icon_color')
            ->orderBy('current_balance', 'desc')
            ->get();
    }

    private function getCashFlow()
    {
        $currentMonth = Statement::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year);

        $income = $currentMonth->where('deposit', '>', 0)->sum('deposit');
        $expenses = $currentMonth->where('withdrawal', '>', 0)->sum('withdrawal');
        $netCashFlow = $income - $expenses;

        $previousMonth = Statement::whereMonth('date', Carbon::now()->subMonth()->month)
            ->whereYear('date', Carbon::now()->subMonth()->year);

        $prevIncome = $previousMonth->where('deposit', '>', 0)->sum('deposit');
        $prevExpenses = $previousMonth->where('withdrawal', '>', 0)->sum('withdrawal');
        $prevNetCashFlow = $prevIncome - $prevExpenses;

        $changePercent = $prevNetCashFlow != 0 ? (($netCashFlow - $prevNetCashFlow) / abs($prevNetCashFlow)) * 100 : 0;

        return [
            'current' => $netCashFlow,
            'previous' => $prevNetCashFlow,
            'change_percent' => round($changePercent, 2)
        ];
    }
}
