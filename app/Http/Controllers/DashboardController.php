<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
        $totalBalance = $totalIncome - $totalExpenses;

        $recentTransactions = Transaction::latest()->take(5)->get();

        // Monthly Labels (last 6 months)
        $months = collect(Carbon::now()->subMonths(5)->monthsUntil(Carbon::now()))
            ->map(fn($d) => $d->format('M Y'));

        $chartIncome = $months->map(function ($label) {
            return Transaction::where('type', 'income')
                ->whereMonth('date', Carbon::parse($label)->month)
                ->whereYear('date', Carbon::parse($label)->year)
                ->sum('amount');
        });

        $chartExpenses = $months->map(function ($label) {
            return Transaction::where('type', 'expense')
                ->whereMonth('date', Carbon::parse($label)->month)
                ->whereYear('date', Carbon::parse($label)->year)
                ->sum('amount');
        });

        // Category Breakdown (assume titles contain category name)
        $categories = ['Food', 'Rent', 'Utilities', 'Travel', 'Other'];
        $categoryData = collect($categories)->map(fn($cat) =>
            Transaction::where('type', 'expense')
                ->where('title', 'like', "%$cat%")
                ->sum('amount')
        );

        return view('dashboard', [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'totalBalance' => $totalBalance,
            'recentTransactions' => $recentTransactions,
            'chartLabels' => $months,
            'chartIncome' => $chartIncome,
            'chartExpenses' => $chartExpenses,
            'categoryLabels' => $categories,
            'categoryData' => $categoryData
        ]);
    }
}

