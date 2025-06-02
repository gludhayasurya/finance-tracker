<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Statement;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $bankId = $request->get('bank_id', 'all');
        $entryType = $request->get('entry_type', 'statement'); // statement or manual

        // Get all banks for dropdown
        $banks = Bank::where('status', 'active')->get();

        // Build the data array
        $data = [
            'banks' => $banks,
            'selectedBankId' => $bankId,
            'entryType' => $entryType,
            'totalBalance' => $this->getTotalBalance($bankId),
            'totalIncome' => $this->getTotalIncome($bankId, $entryType),
            'totalExpenses' => $this->getTotalExpenses($bankId, $entryType),
            'activeAccounts' => $this->getActiveAccountsCount($bankId),
            'cashFlow' => $this->getCashFlowData($bankId, $entryType),
            'monthlyTrend' => $this->getMonthlyTrend($bankId, $entryType),
            'expensesByCategory' => $this->getExpensesByCategory($bankId, $entryType),
            'topExpenseCategories' => $this->getTopExpenseCategories($bankId, $entryType),
            'recentTransactions' => $this->getRecentTransactions($bankId, $entryType),
            'accountBalances' => $this->getAccountBalances($bankId),
            'incomeVsExpense' => $this->getIncomeVsExpenseData($bankId, $entryType),
        ];

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        return view('dashboard', compact('data'));
    }

    /**
     * Get total balance across all accounts or specific bank
     */
    private function getTotalBalance($bankId)
    {
        $query = Bank::where('status', 'active');

        if ($bankId !== 'all') {
            $query->where('id', $bankId);
        }

        return $query->sum('current_balance') ?? 0;
    }

    /**
     * Get total income for current month
     */
    private function getTotalIncome($bankId, $entryType)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        if ($entryType === 'manual') {
            $query = Transaction::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where(function($q) {
                    $q->where('type', 'credit')
                      ->orWhere('deposit', '>', 0);
                });
        } else {
            $query = Statement::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('deposit', '>', 0);
        }

        if ($bankId !== 'all') {
            $query->where('bank_id', $bankId);
        }

        return $query->sum('deposit') ?? 0;
    }

    /**
     * Get total expenses for current month
     */
    private function getTotalExpenses($bankId, $entryType)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        if ($entryType === 'manual') {
            $query = Transaction::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where(function($q) {
                    $q->where('type', 'debit')
                      ->orWhere('withdrawal', '>', 0);
                });
        } else {
            $query = Statement::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('withdrawal', '>', 0);
        }

        if ($bankId !== 'all') {
            $query->where('bank_id', $bankId);
        }

        return $query->sum('withdrawal') ?? 0;
    }

    /**
     * Get count of active accounts
     */
    private function getActiveAccountsCount($bankId)
    {
        $query = Bank::where('status', 'active');

        if ($bankId !== 'all') {
            $query->where('id', $bankId);
        }

        return $query->count();
    }

    /**
     * Get cash flow comparison data
     */
    private function getCashFlowData($bankId, $entryType)
    {
        // Current month
        $currentStart = Carbon::now()->startOfMonth();
        $currentEnd = Carbon::now()->endOfMonth();

        // Previous month
        $previousStart = Carbon::now()->subMonth()->startOfMonth();
        $previousEnd = Carbon::now()->subMonth()->endOfMonth();

        // Get current month data
        $currentIncome = $this->getIncomeForPeriod($bankId, $entryType, $currentStart, $currentEnd);
        $currentExpenses = $this->getExpensesForPeriod($bankId, $entryType, $currentStart, $currentEnd);
        $currentFlow = $currentIncome - $currentExpenses;

        // Get previous month data
        $previousIncome = $this->getIncomeForPeriod($bankId, $entryType, $previousStart, $previousEnd);
        $previousExpenses = $this->getExpensesForPeriod($bankId, $entryType, $previousStart, $previousEnd);
        $previousFlow = $previousIncome - $previousExpenses;

        // Calculate change percentage
        $changePercent = 0;
        if ($previousFlow != 0) {
            $changePercent = (($currentFlow - $previousFlow) / abs($previousFlow)) * 100;
        } elseif ($currentFlow != 0) {
            $changePercent = 100;
        }

        return [
            'current' => $currentFlow,
            'previous' => $previousFlow,
            'change_percent' => round($changePercent, 1),
        ];
    }

    /**
     * Get income for specific period
     */
    private function getIncomeForPeriod($bankId, $entryType, $startDate, $endDate)
    {
        if ($entryType === 'manual') {
            $query = Transaction::whereBetween('date', [$startDate, $endDate])
                ->where(function($q) {
                    $q->where('type', 'credit')
                      ->orWhere('deposit', '>', 0);
                });
        } else {
            $query = Statement::whereBetween('date', [$startDate, $endDate])
                ->where('deposit', '>', 0);
        }

        if ($bankId !== 'all') {
            $query->where('bank_id', $bankId);
        }

        return $query->sum('deposit') ?? 0;
    }

    /**
     * Get expenses for specific period
     */
    private function getExpensesForPeriod($bankId, $entryType, $startDate, $endDate)
    {
        if ($entryType === 'manual') {
            $query = Transaction::whereBetween('date', [$startDate, $endDate])
                ->where(function($q) {
                    $q->where('type', 'debit')
                      ->orWhere('withdrawal', '>', 0);
                });
        } else {
            $query = Statement::whereBetween('date', [$startDate, $endDate])
                ->where('withdrawal', '>', 0);
        }

        if ($bankId !== 'all') {
            $query->where('bank_id', $bankId);
        }

        return $query->sum('withdrawal') ?? 0;
    }

    /**
     * Get monthly trend data for the last 6 months
     */
    private function getMonthlyTrend($bankId, $entryType)
    {
        $months = [];
        $income = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $months[] = $date->format('M Y');

            // Get income for this month
            $monthlyIncome = $this->getIncomeForPeriod($bankId, $entryType, $startOfMonth, $endOfMonth);
            $income[] = $monthlyIncome;

            // Get expenses for this month
            $monthlyExpenses = $this->getExpensesForPeriod($bankId, $entryType, $startOfMonth, $endOfMonth);
            $expenses[] = $monthlyExpenses;
        }

        return [
            'months' => $months,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }

    /**
     * Get expenses grouped by category
     */
    private function getExpensesByCategory($bankId, $entryType)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        if ($entryType === 'manual') {
            $query = Transaction::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where(function($q) {
                    $q->where('type', 'debit')
                      ->orWhere('withdrawal', '>', 0);
                })
                ->select('category', DB::raw('SUM(COALESCE(withdrawal, amount)) as amount'))
                ->groupBy('category');
        } else {
            $query = Statement::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('withdrawal', '>', 0)
                ->select('category', DB::raw('SUM(withdrawal) as amount'))
                ->groupBy('category');
        }

        if ($bankId !== 'all') {
            $query->where('bank_id', $bankId);
        }

        return $query->orderBy('amount', 'desc')
            ->limit(7)
            ->get()
            ->map(function($item) {
                return [
                    'category' => $item->category ?: 'Uncategorized',
                    'amount' => $item->amount,
                ];
            });
    }

    /**
     * Get top expense categories for the sidebar
     */
    private function getTopExpenseCategories($bankId, $entryType)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        if ($entryType === 'manual') {
            $query = Transaction::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where(function($q) {
                    $q->where('type', 'debit')
                      ->orWhere('withdrawal', '>', 0);
                })
                ->select('category', DB::raw('SUM(COALESCE(withdrawal, amount)) as amount'))
                ->groupBy('category');
        } else {
            $query = Statement::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('withdrawal', '>', 0)
                ->select('category', DB::raw('SUM(withdrawal) as amount'))
                ->groupBy('category');
        }

        if ($bankId !== 'all') {
            $query->where('bank_id', $bankId);
        }

        return $query->orderBy('amount', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return (object)[
                    'category' => $item->category ?: 'Uncategorized',
                    'amount' => $item->amount,
                ];
            });
    }

    /**
     * Get recent transactions
     */
    private function getRecentTransactions($bankId, $entryType)
    {
        if ($entryType === 'manual') {
            $query = Transaction::with('bank')
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc');
        } else {
            $query = Statement::with('bank')
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc');
        }

        if ($bankId !== 'all') {
            $query->where('bank_id', $bankId);
        }

        return $query->limit(10)->get();
    }

    /**
     * Get account balances
     */
    private function getAccountBalances($bankId)
    {
        $query = Bank::where('status', 'active')
            ->orderBy('current_balance', 'desc');

        if ($bankId !== 'all') {
            $query->where('id', $bankId);
        }

        return $query->get();
    }

    /**
     * Get income vs expense data for the last 7 days
     */
    private function getIncomeVsExpenseData($bankId, $entryType)
    {
        $days = [];
        $income = [];
        $expenses = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            $days[] = $date->format('M j');

            // Get income for this day
            $dailyIncome = $this->getIncomeForPeriod($bankId, $entryType, $startOfDay, $endOfDay);
            $income[] = $dailyIncome;

            // Get expenses for this day
            $dailyExpenses = $this->getExpensesForPeriod($bankId, $entryType, $startOfDay, $endOfDay);
            $expenses[] = $dailyExpenses;
        }

        return [
            'days' => $days,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }
}
