<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::withCount('transactions')->get();
        return view('bank-accounts.index', compact('accounts'));
    }

    public function create()
    {
        $types = ['checking' => 'Checking', 'savings' => 'Savings', 'ewallet' => 'E-Wallet'];
        return view('bank-accounts.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'bank_name'       => 'required|string|max:255',
            'account_number'  => 'nullable|string|max:50',
            'type'            => 'required|in:checking,savings,ewallet',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $data = $request->only(['name', 'bank_name', 'account_number', 'type', 'opening_balance', 'notes']);
        $data['current_balance'] = $data['opening_balance'];
        BankAccount::create($data);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account created.');
    }

    public function show(BankAccount $bankAccount, Request $request)
    {
        $query = $bankAccount->transactions()->with('creator')->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('start_date')) $query->where('date', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->where('date', '<=', $request->end_date);

        $transactions  = $query->paginate(25)->withQueryString();
        $totalDebits   = $bankAccount->transactions()->sum('debit');
        $totalCredits  = $bankAccount->transactions()->sum('credit');

        return view('bank-accounts.show', compact('bankAccount', 'transactions', 'totalDebits', 'totalCredits'));
    }

    public function edit(BankAccount $bankAccount)
    {
        $types = ['checking' => 'Checking', 'savings' => 'Savings', 'ewallet' => 'E-Wallet'];
        return view('bank-accounts.edit', compact('bankAccount', 'types'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'bank_name'      => 'required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'type'           => 'required|in:checking,savings,ewallet',
        ]);

        $bankAccount->update($request->only(['name', 'bank_name', 'account_number', 'type', 'is_active', 'notes']));
        return redirect()->route('bank-accounts.index')->with('success', 'Bank account updated.');
    }
}
