<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\ExpenseCategory;
use App\Models\ExpenseEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpenseEntry::with(['expenseCategory', 'bankAccount', 'creator'])
            ->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('start_date'))         $query->where('date', '>=', $request->start_date);
        if ($request->filled('end_date'))           $query->where('date', '<=', $request->end_date);
        if ($request->filled('expense_category_id'))$query->where('expense_category_id', $request->expense_category_id);
        if ($request->filled('payment_method'))     $query->where('payment_method', $request->payment_method);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $expenses   = $query->paginate(20)->withQueryString();
        $total      = $query->sum('amount');
        $categories = ExpenseCategory::active()->get();

        return view('expenses.index', compact('expenses', 'total', 'categories'));
    }

    public function create()
    {
        $categories     = ExpenseCategory::active()->get();
        $bankAccounts   = BankAccount::active()->get();
        $paymentMethods = $this->paymentMethods();
        return view('expenses.create', compact('categories', 'bankAccounts', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'                => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description'         => 'required|string|max:500',
            'amount'              => 'required|numeric|min:0.01',
            'payment_method'      => 'nullable|string',
            'bank_account_id'     => 'nullable|exists:bank_accounts,id',
            'receipt'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->only([
                'date', 'expense_category_id', 'description', 'amount',
                'payment_method', 'bank_account_id', 'vendor', 'notes',
            ]);
            $data['created_by']       = auth()->id();
            $data['reference_number'] = 'EXP-' . strtoupper(Str::random(8));

            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $path = $file->store('receipts', 'public');
                $data['receipt_path']          = $path;
                $data['receipt_original_name'] = $file->getClientOriginalName();
            }

            $expense = ExpenseEntry::create($data);

            if ($expense->bank_account_id) {
                $account    = BankAccount::find($expense->bank_account_id);
                $newBalance = $account->current_balance - $expense->amount;

                BankTransaction::create([
                    'date'               => $expense->date,
                    'bank_account_id'    => $expense->bank_account_id,
                    'transaction_type'   => 'expense',
                    'description'        => "Expense: {$expense->description}",
                    'debit'              => $expense->amount,
                    'credit'             => 0,
                    'balance'            => $newBalance,
                    'reference_number'   => $expense->reference_number,
                    'transactionable_type' => ExpenseEntry::class,
                    'transactionable_id'   => $expense->id,
                    'created_by'         => auth()->id(),
                ]);

                $account->update(['current_balance' => $newBalance]);
            }
        });

        return redirect()->route('expenses.index')
            ->with('success', 'Expense entry recorded successfully.');
    }

    public function show(ExpenseEntry $expense)
    {
        $expense->load(['expenseCategory', 'bankAccount', 'creator', 'bankTransaction']);
        return view('expenses.show', compact('expense'));
    }

    public function edit(ExpenseEntry $expense)
    {
        $categories     = ExpenseCategory::active()->get();
        $bankAccounts   = BankAccount::active()->get();
        $paymentMethods = $this->paymentMethods();
        return view('expenses.edit', compact('expense', 'categories', 'bankAccounts', 'paymentMethods'));
    }

    public function update(Request $request, ExpenseEntry $expense)
    {
        $request->validate([
            'date'                => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description'         => 'required|string|max:500',
            'amount'              => 'required|numeric|min:0.01',
            'payment_method'      => 'nullable|string',
            'bank_account_id'     => 'nullable|exists:bank_accounts,id',
            'receipt'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        DB::transaction(function () use ($request, $expense) {
            $oldAmount    = $expense->amount;
            $oldAccountId = $expense->bank_account_id;

            $data = $request->only([
                'date', 'expense_category_id', 'description', 'amount',
                'payment_method', 'bank_account_id', 'vendor', 'notes',
            ]);
            $data['updated_by'] = auth()->id();

            if ($request->hasFile('receipt')) {
                if ($expense->receipt_path) Storage::disk('public')->delete($expense->receipt_path);
                $file = $request->file('receipt');
                $data['receipt_path']          = $file->store('receipts', 'public');
                $data['receipt_original_name'] = $file->getClientOriginalName();
            }

            $expense->update($data);

            $txn = $expense->bankTransaction;
            if ($txn) {
                if ($oldAccountId) {
                    $oldAccount = BankAccount::find($oldAccountId);
                    $oldAccount->update(['current_balance' => $oldAccount->current_balance + $oldAmount]);
                }
                if ($expense->bank_account_id) {
                    $account    = BankAccount::find($expense->bank_account_id);
                    $newBalance = $account->current_balance - $expense->amount;
                    $txn->update([
                        'date'           => $expense->date,
                        'bank_account_id'=> $expense->bank_account_id,
                        'description'    => "Expense: {$expense->description}",
                        'debit'          => $expense->amount,
                        'balance'        => $newBalance,
                    ]);
                    $account->update(['current_balance' => $newBalance]);
                } else {
                    $txn->delete();
                }
            }
        });

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(ExpenseEntry $expense)
    {
        DB::transaction(function () use ($expense) {
            if ($expense->bank_account_id && $expense->bankTransaction) {
                $account = BankAccount::find($expense->bank_account_id);
                $account->update(['current_balance' => $account->current_balance + $expense->amount]);
                $expense->bankTransaction->delete();
            }
            if ($expense->receipt_path) Storage::disk('public')->delete($expense->receipt_path);
            $expense->delete();
        });

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    private function paymentMethods(): array
    {
        return [
            'cash'          => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'gcash'         => 'GCash',
            'maya'          => 'Maya',
            'check'         => 'Check',
        ];
    }
}
