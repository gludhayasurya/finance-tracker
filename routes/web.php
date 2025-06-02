<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\BankStatementController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ImportsController;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/posts', [PostController::class, 'index']);

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


// Additional routes you might want to add:
Route::get('/dashboard/transactions', [DashboardController::class, 'transactions'])->name('dashboard.transactions');
Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');


Route::get('/dashboard/stats', [DashboardController::class, 'getSummaryStats']);
Route::get('/dashboard/trends', [DashboardController::class, 'getTransactionTrends']);
Route::get('/dashboard/export', [DashboardController::class, 'exportData']);

    // Bank routes
    Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
    Route::get('/banks/create', [BankController::class, 'create'])->name('banks.create');
    Route::post('/banks', [BankController::class, 'store'])->name('banks.store');
    Route::get('/banks/{bank}', [BankController::class, 'show'])->name('banks.show');
    Route::get('/banks/{bank}/edit', [BankController::class, 'edit'])->name('banks.edit');
    Route::put('/banks/{bank}', [BankController::class, 'update'])->name('banks.update');
    Route::delete('/banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');

    // Transaction routes
    Route::get('/transactions/{bank_id}', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    Route::get('/upload-bank-statement/{bank_id}', [BankStatementController::class, 'uploadForm'])->name('bank.upload.form');
    Route::post('/parse-bank-statement/{bank_id}', [BankStatementController::class, 'parseAndStore'])->name('bank.parse.store');
    Route::get('/bank-statements/{bank_id}', [BankStatementController::class, 'index'])->name('statements.index');
    Route::put('/bank-statements/{id}', [BankStatementController::class, 'update'])->name('statements.update');


    Route::get('/imports', [ImportsController::class, 'index'])->name('imports.index');
    Route::get('/view-statements/{bank}/{import}', [ImportsController::class, 'viewStatements'])->name('imports.view.statements');



Route::get('/budgets',[BankStatementController::class, 'budgets'])->name('budgets.index');
    Route::get('/budgets/create', [BankStatementController::class, 'createBudget'])->name('budgets.create');
    Route::post('/budgets', [BankStatementController::class, 'storeBudget'])->name('budgets.store');
    Route::get('/budgets/{budget}/edit', [BankStatementController::class, 'editBudget'])->name('budgets.edit');
    Route::put('/budgets/{budget}', [BankStatementController::class, 'updateBudget'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BankStatementController::class, 'destroyBudget'])->name('budgets.destroy');




});


Route::resource('reminders', \App\Http\Controllers\ReminderController::class)->except(['create', 'edit', 'show']);


Route::get('/send-test-mail', function () {
    $details = [
        'title' => 'Mail from Laravel',
        'body' => 'This is a test email sent using Gmail SMTP in Laravel 12.'
    ];

    Mail::to('udhayakumar.g@sq1.security')->send(new TestMail($details));

    return 'Email sent successfully!';
});
