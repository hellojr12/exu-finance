<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BankTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = BankTransaction::with(['bankAccount', 'creator'])
            ->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('bank_account_id')) $query->where('bank_account_id', $request->bank_account_id);
        if ($request->filled('start_date'))      $query->where('date', '>=', $request->start_date);
        if ($request->filled('end_date'))        $query->where('date', '<=', $request->end_date);
        if ($request->filled('type'))            $query->where('transaction_type', $request->type);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('reference_number', 'like', "%{$s}%");
            });
        }

        $transactions = $query->paginate(25)->withQueryString();
        $bankAccounts = BankAccount::active()->get();

        return view('bank-transactions.index', compact('transactions', 'bankAccounts'));
    }

    public function create()
    {
        $bankAccounts = BankAccount::active()->get();
        $types = [
            'deposit'      => 'Deposit',
            'withdrawal'   => 'Withdrawal',
            'transfer_in'  => 'Transfer In',
            'transfer_out' => 'Transfer Out',
        ];
        return view('bank-transactions.create', compact('bankAccounts', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'            => 'required|date',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_type'=> 'required|string',
            'description'     => 'required|string|max:500',
            'amount'          => 'required|numeric|min:0.01',
            'transfer_to_account_id' => 'nullable|exists:bank_accounts,id|different:bank_account_id',
        ]);

        DB::transaction(function () use ($request) {
            $account = BankAccount::find($request->bank_account_id);
            $amount  = (float) $request->amount;
            $type    = $request->transaction_type;

            $isDebit  = in_array($type, ['withdrawal', 'transfer_out', 'expense']);
            $debit    = $isDebit ? $amount : 0;
            $credit   = $isDebit ? 0 : $amount;
            $newBal   = $account->current_balance + $credit - $debit;
            $ref      = 'TXN-' . strtoupper(Str::random(8));

            BankTransaction::create([
                'date'               => $request->date,
                'bank_account_id'    => $account->id,
                'transaction_type'   => $type,
                'description'        => $request->description,
                'debit'              => $debit,
                'credit'             => $credit,
                'balance'            => $newBal,
                'reference_number'   => $ref,
                'transfer_to_account_id' => $request->transfer_to_account_id,
                'is_manual'          => true,
                'created_by'         => auth()->id(),
                'notes'              => $request->notes,
            ]);
            $account->update(['current_balance' => $newBal]);

            // Mirror transfer to destination account
            if ($type === 'transfer_out' && $request->transfer_to_account_id) {
                $toAccount  = BankAccount::find($request->transfer_to_account_id);
                $toNewBal   = $toAccount->current_balance + $amount;
                BankTransaction::create([
                    'date'               => $request->date,
                    'bank_account_id'    => $toAccount->id,
                    'transaction_type'   => 'transfer_in',
                    'description'        => "Transfer from {$account->name}: {$request->description}",
                    'debit'              => 0,
                    'credit'             => $amount,
                    'balance'            => $toNewBal,
                    'reference_number'   => $ref,
                    'is_manual'          => true,
                    'created_by'         => auth()->id(),
                ]);
                $toAccount->update(['current_balance' => $toNewBal]);
            }
        });

        return redirect()->route('bank-transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    public function show(BankTransaction $bankTransaction)
    {
        $bankTransaction->load(['bankAccount', 'transferToAccount', 'creator', 'transactionable']);
        return view('bank-transactions.show', compact('bankTransaction'));
    }

    public function destroy(BankTransaction $bankTransaction)
    {
        if (!$bankTransaction->is_manual) {
            return back()->with('error', 'Auto-generated transactions cannot be deleted here.');
        }

        DB::transaction(function () use ($bankTransaction) {
            $account = $bankTransaction->bankAccount;
            if ($bankTransaction->debit > 0) {
                $account->update(['current_balance' => $account->current_balance + $bankTransaction->debit]);
            } else {
                $account->update(['current_balance' => $account->current_balance - $bankTransaction->credit]);
            }
            $bankTransaction->delete();
        });

        return redirect()->route('bank-transactions.index')->with('success', 'Transaction deleted.');
    }
}
