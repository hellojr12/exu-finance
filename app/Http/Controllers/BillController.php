<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::with(['expenseCategory', 'creator'])->orderByDesc('bill_date');

        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->where('bill_date', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->where('bill_date', '<=', $request->end_date);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('supplier_name', 'like', "%{$s}%")
                  ->orWhere('bill_number', 'like', "%{$s}%")
                  ->orWhere('event_name', 'like', "%{$s}%");
            });
        }

        Bill::unpaid()->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $bills   = $query->paginate(20)->withQueryString();
        $summary = [
            'total_ap'   => Bill::unpaid()->sum('balance_due'),
            'overdue'    => Bill::overdue()->sum('balance_due'),
            'paid_month' => Bill::where('status', 'paid')
                            ->whereMonth('updated_at', now()->month)->sum('total_amount'),
        ];

        $categories = ExpenseCategory::active()->get();
        return view('bills.index', compact('bills', 'summary', 'categories'));
    }

    public function create()
    {
        $categories   = ExpenseCategory::active()->get();
        $bankAccounts = BankAccount::active()->get();
        $number       = Bill::generateNumber();
        return view('bills.create', compact('categories', 'bankAccounts', 'number'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name'       => 'required|string|max:255',
            'bill_date'           => 'required|date',
            'due_date'            => 'required|date|after_or_equal:bill_date',
            'total_amount'        => 'required|numeric|min:0.01',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'attachment'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->only([
            'bill_number', 'supplier_name', 'supplier_email', 'supplier_contact',
            'bill_date', 'due_date', 'event_name', 'expense_category_id',
            'description', 'total_amount', 'notes',
        ]);
        $data['balance_due'] = $data['total_amount'];
        $data['created_by']  = auth()->id();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('bills', 'public');
        }

        Bill::create($data);
        return redirect()->route('bills.index')->with('success', 'Bill created.');
    }

    public function show(Bill $bill)
    {
        $bill->load(['expenseCategory', 'payments.bankAccount', 'creator']);
        $bankAccounts = BankAccount::active()->get();
        return view('bills.show', compact('bill', 'bankAccounts'));
    }

    public function edit(Bill $bill)
    {
        $categories = ExpenseCategory::active()->get();
        return view('bills.edit', compact('bill', 'categories'));
    }

    public function update(Request $request, Bill $bill)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'bill_date'     => 'required|date',
            'due_date'      => 'required|date|after_or_equal:bill_date',
            'total_amount'  => 'required|numeric|min:0.01',
        ]);

        $data = $request->only([
            'supplier_name', 'supplier_email', 'supplier_contact',
            'bill_date', 'due_date', 'event_name', 'expense_category_id',
            'description', 'total_amount', 'notes',
        ]);
        $data['balance_due'] = $data['total_amount'] - $bill->amount_paid;
        $bill->update($data);
        $bill->updateStatus();

        return redirect()->route('bills.show', $bill)->with('success', 'Bill updated.');
    }

    public function recordPayment(Request $request, Bill $bill)
    {
        $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01|max:' . $bill->balance_due,
            'payment_method' => 'required|string',
            'bank_account_id'=> 'nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request, $bill) {
            $payment = BillPayment::create([
                'bill_id'         => $bill->id,
                'payment_date'    => $request->payment_date,
                'amount'          => $request->amount,
                'payment_method'  => $request->payment_method,
                'bank_account_id' => $request->bank_account_id,
                'reference_number'=> $request->reference_number,
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);

            $bill->increment('amount_paid', $request->amount);
            $bill->refresh();
            $bill->updateStatus();

            if ($request->bank_account_id) {
                $account    = BankAccount::find($request->bank_account_id);
                $newBalance = $account->current_balance - $request->amount;
                BankTransaction::create([
                    'date'               => $request->payment_date,
                    'bank_account_id'    => $request->bank_account_id,
                    'transaction_type'   => 'withdrawal',
                    'description'        => "AP Payment: {$bill->supplier_name} - {$bill->bill_number}",
                    'debit'              => $request->amount,
                    'credit'             => 0,
                    'balance'            => $newBalance,
                    'reference_number'   => $bill->bill_number,
                    'transactionable_type' => BillPayment::class,
                    'transactionable_id'   => $payment->id,
                    'created_by'         => auth()->id(),
                ]);
                $account->update(['current_balance' => $newBalance]);
            }
        });

        return redirect()->route('bills.show', $bill)->with('success', 'Payment recorded.');
    }

    public function destroy(Bill $bill)
    {
        if ($bill->attachment_path) Storage::disk('public')->delete($bill->attachment_path);
        $bill->delete();
        return redirect()->route('bills.index')->with('success', 'Bill deleted.');
    }
}
