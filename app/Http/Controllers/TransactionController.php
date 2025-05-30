<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($bankId)
{
    $bank = Bank::findOrFail($bankId);

    // Fetch all transactions for this bank
    $transactions = Transaction::where('bank_id', $bankId)
        ->latest()
        ->get();

    // Optimized totals directly from DB
    $totals = Transaction::selectRaw("
            SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_expense
        ")
        ->where('bank_id', $bankId)
        ->first();

    $totalIncome = $totals->total_income ?? 0;
    $totalExpense = $totals->total_expense ?? 0;
    $netAmount = $totalIncome - $totalExpense;

    $categories = [
        'salary', 'food', 'transport', 'healthcare',
        'utilities', 'shopping', 'housing', 'entertainment',
        'education', 'others'
    ];

    return view('transactions.index', compact(
        'transactions',
        'bank',
        'totalIncome',
        'totalExpense',
        'netAmount',
        'categories'
    ));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            // 'type' => 'required|in:credit,debit',
            'date' => 'required|date',
        ]);

        // dd($request->all());

        Transaction::create($request->except('token'));

        return redirect()->route('transactions.index', ['bank_id' => $request->bank_id])
        ->with('toast', [
            'type' => 'success',
            'message' => 'Transaction created successfully.'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            // 'type' => 'required|in:credit,debit',
            'date' => 'required|date',
        ]);

        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->except('token'));

        return redirect()->route('transactions.index', ['bank_id' => $request->bank_id])
        ->with('toast', [
            'type' => 'success',
            'message' => 'Transaction updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return redirect()->route('transactions.index', ['bank_id' => $transaction->bank_id])
            ->with('toast', [
                'type' => 'success',
                'message' => 'Transaction deleted successfully.'
            ]);
    }
}
