<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinancialStatementController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffLoanController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});
Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')->name('logout');

// ── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // ── Revenue ───────────────────────────────────────────────────────────────
    Route::middleware(['role:admin|finance'])->group(function () {
        Route::resource('revenue', RevenueController::class);
        Route::resource('expenses', ExpenseController::class);
        Route::resource('bank-accounts', BankAccountController::class)->except(['destroy']);
        Route::resource('bank-transactions', BankTransactionController::class)->except(['edit', 'update']);

        // Invoices (AR)
        Route::resource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])
            ->name('invoices.payments.store');

        // Bills (AP)
        Route::resource('bills', BillController::class);
        Route::post('bills/{bill}/payments', [BillController::class, 'recordPayment'])
            ->name('bills.payments.store');

        // Staff Loans
        Route::resource('staff-loans', StaffLoanController::class);
        Route::post('staff-loans/{staffLoan}/deductions', [StaffLoanController::class, 'recordDeduction'])
            ->name('staff-loans.deductions.store');
    });

    // ── View-only routes (all authenticated roles can view) ───────────────────
    Route::get('revenue', [RevenueController::class, 'index'])->name('revenue.index');
    Route::get('revenue/{revenue}', [RevenueController::class, 'show'])->name('revenue.show');
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index');
    Route::get('bank-accounts/{bankAccount}', [BankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::get('bank-transactions', [BankTransactionController::class, 'index'])->name('bank-transactions.index');
    Route::get('bank-transactions/{bankTransaction}', [BankTransactionController::class, 'show'])->name('bank-transactions.show');
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('bills/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::get('staff-loans', [StaffLoanController::class, 'index'])->name('staff-loans.index');
    Route::get('staff-loans/{staffLoan}', [StaffLoanController::class, 'show'])->name('staff-loans.show');

    // ── Financial Statements ──────────────────────────────────────────────────
    Route::prefix('financial-statements')->name('financial-statements.')->group(function () {
        Route::get('income-statement', [FinancialStatementController::class, 'incomeStatement'])
            ->name('income-statement');
        Route::get('balance-sheet', [FinancialStatementController::class, 'balanceSheet'])
            ->name('balance-sheet');
        Route::get('cash-flow', [FinancialStatementController::class, 'cashFlow'])
            ->name('cash-flow');

        // Exports
        Route::get('income-statement/export', [FinancialStatementController::class, 'exportIncomeStatement'])
            ->name('income-statement.export');
        Route::get('balance-sheet/export', [FinancialStatementController::class, 'exportBalanceSheet'])
            ->name('balance-sheet.export');
        Route::get('cash-flow/export', [FinancialStatementController::class, 'exportCashFlow'])
            ->name('cash-flow.export');
    });

    // ── Settings (Admin only) ─────────────────────────────────────────────────
    Route::middleware(['role:admin'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('general', [SettingsController::class, 'updateGeneral'])->name('general.update');

        Route::post('event-categories', [SettingsController::class, 'storeEventCategory'])->name('event-categories.store');
        Route::put('event-categories/{eventCategory}', [SettingsController::class, 'updateEventCategory'])->name('event-categories.update');
        Route::delete('event-categories/{eventCategory}', [SettingsController::class, 'destroyEventCategory'])->name('event-categories.destroy');

        Route::post('expense-categories', [SettingsController::class, 'storeExpenseCategory'])->name('expense-categories.store');
        Route::put('expense-categories/{expenseCategory}', [SettingsController::class, 'updateExpenseCategory'])->name('expense-categories.update');
        Route::delete('expense-categories/{expenseCategory}', [SettingsController::class, 'destroyExpenseCategory'])->name('expense-categories.destroy');

        Route::post('users', [SettingsController::class, 'storeUser'])->name('users.store');
        Route::put('users/{user}', [SettingsController::class, 'updateUser'])->name('users.update');
        Route::delete('users/{user}', [SettingsController::class, 'destroyUser'])->name('users.destroy');
    });
});
