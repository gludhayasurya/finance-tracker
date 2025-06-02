<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Statement;
use App\Models\Transaction;
use App\Models\Bank;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with financial data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $data = $this->getDashboardData($request);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($data);
            }

            return view('dashboard', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error fetching dashboard data: ' . $e->getMessage());
            return $request->ajax()
                ? response()->json(['error' => 'Failed to load dashboard data'], 500)
                : redirect()->back()->with('error', 'Failed to load dashboard data');
        }
    }

    /**
     * Gather dashboard data based on filters.
     *
     * @param Request $request
     * @return array
     */
    private function getDashboardData(Request $request)
    {
        $selectedBankId = $request->get('bank_id', 'all');
        $entryType = $request->get('entry_type', 'statement');

        // Validate entry type
        if (!in_array($entryType, ['statement', 'manual'])) {
            $entryType = 'statement';
        }

        // Get all banks for dropdown
        $banks = Bank::select('id', 'name', 'fa_icon', 'icon_color')
            ->orderBy('name')
            ->get();

        // Initialize base query
        $model = $entryType === 'manual' ? Transaction::class : Statement::class;
        $baseQuery = $model::with('bank');

        // Apply bank filter
        if ($selectedBankId !== 'all') {
            $baseQuery->where('bank_id', $selectedBankId);
        }

        // Define date ranges
        $currentMonth = Carbon::now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        $previousMonth = $currentMonth->copy()->subMonth();
        $startOfPrevMonth = $previousMonth->copy()->startOfMonth();
        $endOfPrevMonth = $previousMonth->copy()->endOfMonth();

        // Current month transactions
        $currentMonthTransactions = (clone $baseQuery)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        // Previous month transactions
        $prevMonthTransactions = (clone $baseQuery)
            ->whereBetween('date', [$startOfPrevMonth, $endOfPrevMonth])
            ->get();

        // Calculate totals
        $totalIncome = $currentMonthTransactions->sum('deposit') ?? 0;
        $totalExpenses = $currentMonthTransactions->sum('withdrawal') ?? 0;
        $totalBalance = $totalIncome - $totalExpenses;

        $prevTotalIncome = $prevMonthTransactions->sum('deposit') ?? 0;
        $prevTotalExpenses = $prevMonthTransactions->sum('withdrawal') ?? 0;
        $prevTotalBalance = $prevTotalIncome - $prevTotalExpenses;

        // Calculate cash flow change
        $currentCashFlow = $totalBalance;
        $previousCashFlow = $prevTotalBalance;
        $changePercent = $previousCashFlow != 0
            ? round((($currentCashFlow - $previousCashFlow) / abs($previousCashFlow)) * 100, 1)
            : 0;

        return [
            'selectedBankId' => $selectedBankId,
            'entryType' => $entryType,
            'banks' => $banks,
            'totalBalance' => $totalBalance,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'activeAccounts' => $banks->count(),
            'cashFlow' => [
                'current' => $currentCashFlow,
                'previous' => $previousCashFlow,
                'change_percent' => $changePercent,
            ],
            'accountBalances' => $this->getAccountBalances($selectedBankId),
            'recentTransactions' => $this->getRecentTransactions($baseQuery),
            'monthlyTrend' => $this->getMonthlyTrend($selectedBankId, $entryType),
            'expensesByCategory' => $this->getExpensesByCategory($selectedBankId, $entryType, $startOfMonth, $endOfMonth),
            'topExpenseCategories' => $this->getExpensesByCategory($selectedBankId, $entryType, $startOfMonth, $endOfMonth)->take(5),
            'incomeVsExpense' => $this->getWeeklyCashFlow($selectedBankId, $entryType),
        ];
    }

    /**
     * Get account balances for banks.
     *
     * @param string $selectedBankId
     * @return \Illuminate\Support\Collection
     */
    private function getAccountBalances($selectedBankId)
    {
        $query = Bank::select('id', 'name', 'fa_icon', 'icon_color')
            ->selectRaw('COALESCE(current_balance, 0) as current_balance');

        if ($selectedBankId !== 'all') {
            $query->where('id', $selectedBankId);
        }

        return $query->orderBy('current_balance', 'desc')->get();
    }

    /**
     * Get recent transactions (last 10).
     *
     * @param \Illuminate\Database\Eloquent\Builder $baseQuery
     * @return \Illuminate\Support\Collection
     */
    private function getRecentTransactions($baseQuery)
    {
        return (clone $baseQuery)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get monthly trend data for the last 6 months.
     *
     * @param string $selectedBankId
     * @param string $entryType
     * @return array
     */
    private function getMonthlyTrend($selectedBankId, $entryType)
    {
        $months = [];
        $incomeData = [];
        $expenseData = [];
        $model = $entryType === 'manual' ? Transaction::class : Statement::class;

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $months[] = $date->format('M Y');

            $query = $model::whereBetween('date', [$startOfMonth, $endOfMonth]);

            if ($selectedBankId !== 'all') {
                $query->where('bank_id', $selectedBankId);
            }

            $monthlyTransactions = $query->get();

            $incomeData[] = $monthlyTransactions->sum('deposit') ?? 0;
            $expenseData[] = $monthlyTransactions->sum('withdrawal') ?? 0;
        }

        return [
            'months' => $months,
            'income' => $incomeData,
            'expenses' => $expenseData,
        ];
    }

    /**
     * Get expenses by category for the specified period.
     *
     * @param string $selectedBankId
     * @param string $entryType
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getExpensesByCategory($selectedBankId, $entryType, $startDate, $endDate)
    {
        $model = $entryType === 'manual' ? Transaction::class : Statement::class;

        $query = $model::select('category', DB::raw('SUM(withdrawal) as amount'))
            ->whereBetween('date', [$startDate, $endDate])
            ->where('withdrawal', '>', 0)
            ->whereNotNull('category');

        if ($selectedBankId !== 'all') {
            $query->where('bank_id', $selectedBankId);
        }

        return $query->groupBy('category')
            ->orderBy('amount', 'desc')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'category' => $item->category ?: 'Uncategorized',
                    'amount' => floatval($item->amount),
                ];
            });
    }

    /**
     * Get weekly cash flow for the last 7 days.
     *
     * @param string $selectedBankId
     * @param string $entryType
     * @return array
     */
    private function getWeeklyCashFlow($selectedBankId, $entryType)
    {
        $days = [];
        $incomeData = [];
        $expenseData = [];
        $model = $entryType === 'manual' ? Transaction::class : Statement::class;

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            $days[] = $date->format('M j');

            $query = $model::whereBetween('date', [$startOfDay, $endOfDay]);

            if ($selectedBankId !== 'all') {
                $query->where('bank_id', $selectedBankId);
            }

            $dailyTransactions = $query->get();

            $incomeData[] = $dailyTransactions->sum('deposit') ?? 0;
            $expenseData[] = $dailyTransactions->sum('withdrawal') ?? 0;
        }

        return [
            'days' => $days,
            'income' => $incomeData,
            'expenses' => $expenseData,
        ];
    }

    /**
     * Get summary stats for the dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummaryStats(Request $request)
    {
        try {
            $selectedBankId = $request->get('bank_id', 'all');
            $entryType = $request->get('entry_type', 'statement');

            $model = $entryType === 'manual' ? Transaction::class : Statement::class;
            $query = $model::query();

            if ($selectedBankId !== 'all') {
                $query->where('bank_id', $selectedBankId);
            }

            $currentMonth = Carbon::now();
            $startOfMonth = $currentMonth->copy()->startOfMonth();
            $endOfMonth = $currentMonth->copy()->endOfMonth();

            $monthlyData = (clone $query)->whereBetween('date', [$startOfMonth, $endOfMonth])->get();
            $allTimeData = $query->get();

            return response()->json([
                'monthly' => [
                    'income' => $monthlyData->sum('deposit') ?? 0,
                    'expenses' => $monthlyData->sum('withdrawal') ?? 0,
                    'balance' => ($monthlyData->sum('deposit') ?? 0) - ($monthlyData->sum('withdrawal') ?? 0),
                    'transactions_count' => $monthlyData->count(),
                ],
                'all_time' => [
                    'income' => $allTimeData->sum('deposit') ?? 0,
                    'expenses' => $allTimeData->sum('withdrawal') ?? 0,
                    'balance' => ($allTimeData->sum('deposit') ?? 0) - ($allTimeData->sum('withdrawal') ?? 0),
                    'transactions_count' => $allTimeData->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching summary stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch summary stats'], 500);
        }
    }

    /**
     * Get transaction trends for charts.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionTrends(Request $request)
    {
        try {
            $selectedBankId = $request->get('bank_id', 'all');
            $entryType = $request->get('entry_type', 'statement');
            $period = $request->get('period', 'monthly');

            switch ($period) {
                case 'daily':
                    return response()->json($this->getDailyTrends($selectedBankId, $entryType));
                case 'weekly':
                    return response()->json($this->getWeeklyTrends($selectedBankId, $entryType));
                default:
                    return response()->json($this->getMonthlyTrends($selectedBankId, $entryType));
            }
        } catch (\Exception $e) {
            Log::error('Error fetching transaction trends: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch transaction trends'], 500);
        }
    }

    /**
     * Get daily trends for the last 30 days.
     *
     * @param string $selectedBankId
     * @param string $entryType
     * @return array
     */
    private function getDailyTrends($selectedBankId, $entryType)
    {
        $data = [];
        $model = $entryType === 'manual' ? Transaction::class : Statement::class;

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            $query = $model::whereBetween('date', [$startOfDay, $endOfDay]);

            if ($selectedBankId !== 'all') {
                $query->where('bank_id', $selectedBankId);
            }

            $dayData = $query->get();

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M j'),
                'income' => $dayData->sum('deposit') ?? 0,
                'expenses' => $dayData->sum('withdrawal') ?? 0,
                'balance' => ($dayData->sum('deposit') ?? 0) - ($dayData->sum('withdrawal') ?? 0),
            ];
        }

        return $data;
    }

    /**
     * Get weekly trends for the last 12 weeks.
     *
     * @param string $selectedBankId
     * @param string $entryType
     * @return array
     */
    private function getWeeklyTrends($selectedBankId, $entryType)
    {
        $data = [];
        $model = $entryType === 'manual' ? Transaction::class : Statement::class;

        for ($i = 11; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            $query = $model::whereBetween('date', [$startOfWeek, $endOfWeek]);

            if ($selectedBankId !== 'all') {
                $query->where('bank_id', $selectedBankId);
            }

            $weekData = $query->get();

            $data[] = [
                'date' => $startOfWeek->format('Y-m-d'),
                'label' => $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j'),
                'income' => $weekData->sum('deposit') ?? 0,
                'expenses' => $weekData->sum('withdrawal') ?? 0,
                'balance' => ($weekData->sum('deposit') ?? 0) - ($weekData->sum('withdrawal') ?? 0),
            ];
        }

        return $data;
    }

    /**
     * Get monthly trends for the last 12 months.
     *
     * @param string $selectedBankId
     * @param string $entryType
     * @return array
     */
    private function getMonthlyTrends($selectedBankId, $entryType)
    {
        $data = [];
        $model = $entryType === 'manual' ? Transaction::class : Statement::class;

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $query = $model::whereBetween('date', [$startOfMonth, $endOfMonth]);

            if ($selectedBankId !== 'all') {
                $query->where('bank_id', $selectedBankId);
            }

            $monthData = $query->get();

            $data[] = [
                'date' => $date->format('Y-m-01'),
                'label' => $date->format('M Y'),
                'income' => $monthData->sum('deposit') ?? 0,
                'expenses' => $monthData->sum('withdrawal') ?? 0,
                'balance' => ($monthData->sum('deposit') ?? 0) - ($monthData->sum('withdrawal') ?? 0),
            ];
        }

        return $data;
    }

    /**
     * Export dashboard data in the requested format.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportData(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $data = $this->getDashboardData($request);

            switch ($format) {
                case 'excel':
                    return $this->exportToExcel($data);
                case 'pdf':
                    return $this->exportToPdf($data);
                default:
                    return $this->exportToCsv($data);
            }
        } catch (\Exception $e) {
            Log::error('Error exporting dashboard data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export data'], 500);
        }
    }

    /**
     * Export dashboard data to CSV.
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    private function exportToCsv($data)
    {
        $filename = 'dashboard-data-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['Metric', 'Value']);

            // Summary data
            fputcsv($file, ['Total Income', '₹' . number_format($data['totalIncome'], 2)]);
            fputcsv($file, ['Total Expenses', '₹' . number_format($data['totalExpenses'], 2)]);
            fputcsv($file, ['Total Balance', '₹' . number_format($data['totalBalance'], 2)]);
            fputcsv($file, ['Active Accounts', $data['activeAccounts']]);

            // Recent Transactions
            fputcsv($file, []); // Empty row for spacing
            fputcsv($file, ['Recent Transactions']);
            fputcsv($file, ['Date', 'Bank', 'Particulars', 'Type', 'Amount']);

            foreach ($data['recentTransactions'] as $transaction) {
                $isIncome = ($transaction->deposit ?? 0) > 0;
                $amount = $isIncome ? $transaction->deposit : $transaction->withdrawal;
                fputcsv($file, [
                    $transaction->date ? $transaction->date->format('M d, Y') : 'N/A',
                    $transaction->bank->name ?? 'Unknown',
                    $transaction->particulars ?? $transaction->extracted_particular ?? 'Unknown',
                    $isIncome ? 'Income' : 'Expense',
                    '₹' . number_format($amount ?? 0, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export dashboard data to Excel (placeholder).
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    private function exportToExcel($data)
    {
        // Placeholder: Implement using a package like Maatwebsite\Excel
        Log::warning('Excel export not implemented. Falling back to CSV.');
        return $this->exportToCsv($data);
    }

    /**
     * Export dashboard data to PDF (placeholder).
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    private function exportToPdf($data)
    {
        // Placeholder: Implement using a package like DomPDF
        Log::warning('PDF export not implemented. Falling back to CSV.');
        return $this->exportToCsv($data);
    }
}