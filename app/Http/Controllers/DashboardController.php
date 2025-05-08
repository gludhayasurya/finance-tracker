<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = $request->input('year', now()->year);
        $selectedMonth = $request->input('month', null); // null = all months

        $availableYears = range(now()->year, now()->year - 5);
        $availableMonths = [
            '01' => 'January', '02' => 'February', '03' => 'March',
            '04' => 'April',   '05' => 'May',      '06' => 'June',
            '07' => 'July',    '08' => 'August',   '09' => 'September',
            '10' => 'October', '11' => 'November', '12' => 'December',
        ];

        // Dummy data – replace with actual DB query based on filters
        $monthlyIncomes = [10000, 12000, 9000, 14000, 11000, 13000];
        $monthlyExpenses = [8000, 7000, 9500, 9000, 8500, 10000];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

        if ($selectedMonth) {
            // Simulate filtering for one month (for demo only)
            $monthIndex = intval($selectedMonth) - 1;
            $monthlyIncomes = [$monthlyIncomes[$monthIndex]];
            $monthlyExpenses = [$monthlyExpenses[$monthIndex]];
            $months = [$months[$monthIndex]];
        }

        return view('dashboard', [
            'totalBalance' => 30000,
            'totalIncome' => array_sum($monthlyIncomes),
            'totalExpenses' => array_sum($monthlyExpenses),
            'months' => $months,
            'monthlyIncomes' => $monthlyIncomes,
            'monthlyExpenses' => $monthlyExpenses,
            'availableYears' => $availableYears,
            'availableMonths' => $availableMonths,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
        ]);
    }


    // public function index()
    // {
    //     $transactions = Transaction::all();

    //     $totalIncome = $transactions->where('type', 'income')->sum('amount');
    //     $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
    //     $totalBalance = $totalIncome - $totalExpenses;

    //     $recentTransactions = Transaction::latest()->take(5)->get();

    //     // Monthly Labels (last 6 months)
    //     $months = collect(Carbon::now()->subMonths(5)->monthsUntil(Carbon::now()))
    //         ->map(fn($d) => $d->format('M Y'));

    //     $chartIncome = $months->map(function ($label) {
    //         return Transaction::where('type', 'income')
    //             ->whereMonth('date', Carbon::parse($label)->month)
    //             ->whereYear('date', Carbon::parse($label)->year)
    //             ->sum('amount');
    //     });

    //     $chartExpenses = $months->map(function ($label) {
    //         return Transaction::where('type', 'expense')
    //             ->whereMonth('date', Carbon::parse($label)->month)
    //             ->whereYear('date', Carbon::parse($label)->year)
    //             ->sum('amount');
    //     });

    //     // Category Breakdown (assume titles contain category name)
    //     $categories = ['Food', 'Rent', 'Utilities', 'Travel', 'Other'];
    //     $categoryData = collect($categories)->map(fn($cat) =>
    //         Transaction::where('type', 'expense')
    //             ->where('title', 'like', "%$cat%")
    //             ->sum('amount')
    //     );

    //     return view('dashboard', [
    //         'totalIncome' => $totalIncome,
    //         'totalExpenses' => $totalExpenses,
    //         'totalBalance' => $totalBalance,
    //         'recentTransactions' => $recentTransactions,
    //         'chartLabels' => $months,
    //         'chartIncome' => $chartIncome,
    //         'chartExpenses' => $chartExpenses,
    //         'categoryLabels' => $categories,
    //         'categoryData' => $categoryData
    //     ]);
    // }
}
