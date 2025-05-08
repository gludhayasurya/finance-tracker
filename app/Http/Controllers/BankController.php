<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Models\Bank;
use App\Models\Transaction;
use Illuminate\Http\Request;


class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::get();
        return view('banks.index', compact('banks'));
    }

    public function create()
    {
        return view('banks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric|min:0',
            'fa_icon' => 'required|string|max:255',
            'icon_color' => 'required|string|max:255',

        ]);

        Bank::create($request->all());

        $post = Transaction::latest()->first();

        // event(new PostCreated($post, 1));

        return redirect()->route('banks.index')->with('toast', [
            'type' => 'success',
            'message' => 'Bank created successfully.'
        ]);
    }

    public function edit(Bank $bank)
    {
        return view('banks.edit', compact('bank'));
    }

    public function update(Request $request, Bank $bank)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric|min:0',
            'fa_icon' => 'required|string|max:255',
            'icon_color' => 'required|string|max:255',
        ]);

        $bank->update($request->all());

        return redirect()->route('banks.index')->with('toast', [
            'type' => 'success',
            'message' => 'Bank updated successfully.'
        ]);
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return redirect()->route('banks.index')->with('toast', [
            'type' => 'success',
            'message' => 'Bank deleted successfully.'
        ]);
    }
}
