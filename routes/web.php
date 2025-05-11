<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\BankStatementController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ImportController;
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

    Route::get('/upload-bank-statement', [BankStatementController::class, 'uploadForm'])->name('bank.upload.form');
    Route::post('/parse-bank-statement', [BankStatementController::class, 'parseAndStore'])->name('bank.parse.store');
    Route::get('/bank-statements', [BankStatementController::class, 'index'])->name('statements.index');

    Route::get('/import-bank-transactions', [ImportController::class, 'importBankTransactions'])->name('bank.import.transactions');
    Route::get('/import-bank-transactions/{bank}', [ImportController::class, 'importBankTransactions'])->name('bank.import.transactions.bank');



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
