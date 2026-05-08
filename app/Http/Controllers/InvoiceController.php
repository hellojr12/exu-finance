<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\EventCategory;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['eventCategory', 'creator'])->orderByDesc('invoice_date');

        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->where('invoice_date', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->where('invoice_date', '<=', $request->end_date);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('partner_name', 'like', "%{$s}%")
                  ->orWhere('invoice_number', 'like', "%{$s}%")
                  ->orWhere('event_name', 'like', "%{$s}%");
            });
        }

        // Refresh overdue status
        Invoice::unpaid()->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $invoices = $query->paginate(20)->withQueryString();
        $summary  = [
            'total_ar'   => Invoice::unpaid()->sum('balance_due'),
            'overdue'    => Invoice::overdue()->sum('balance_due'),
            'paid_month' => Invoice::where('status', 'paid')
                            ->whereMonth('updated_at', now()->month)->sum('total_amount'),
        ];

        return view('invoices.index', compact('invoices', 'summary'));
    }

    public function create()
    {
        $categories   = EventCategory::active()->get();
        $bankAccounts = BankAccount::active()->get();
        $number       = Invoice::generateNumber();
        return view('invoices.create', compact('categories', 'bankAccounts', 'number'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'partner_name'      => 'required|string|max:255',
            'invoice_date'      => 'required|date',
            'due_date'          => 'required|date|after_or_equal:invoice_date',
            'total_amount'      => 'required|numeric|min:0.01',
            'event_category_id' => 'nullable|exists:event_categories,id',
        ]);

        $data = $request->only([
            'invoice_number', 'partner_name', 'partner_email', 'partner_contact',
            'partner_address', 'invoice_date', 'due_date', 'event_name',
            'event_category_id', 'description', 'subtotal', 'tax_rate',
            'tax_amount', 'total_amount', 'notes',
        ]);
        $data['balance_due'] = $data['total_amount'];
        $data['created_by']  = auth()->id();

        Invoice::create($data);

        return redirect()->route('invoices.index')->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['eventCategory', 'payments.bankAccount', 'creator']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $categories   = EventCategory::active()->get();
        return view('invoices.edit', compact('invoice', 'categories'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'partner_name' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date'     => 'required|date|after_or_equal:invoice_date',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        $invoice->update(array_merge(
            $request->only([
                'partner_name', 'partner_email', 'partner_contact', 'partner_address',
                'invoice_date', 'due_date', 'event_name', 'event_category_id',
                'description', 'subtotal', 'tax_rate', 'tax_amount', 'total_amount', 'notes',
            ]),
            ['balance_due' => $request->total_amount - $invoice->amount_paid]
        ));
        $invoice->updateStatus();

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'payment_method' => 'required|string',
            'bank_account_id'=> 'nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $payment = InvoicePayment::create([
                'invoice_id'      => $invoice->id,
                'payment_date'    => $request->payment_date,
                'amount'          => $request->amount,
                'payment_method'  => $request->payment_method,
                'bank_account_id' => $request->bank_account_id,
                'reference_number'=> $request->reference_number,
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);

            $invoice->increment('amount_paid', $request->amount);
            $invoice->refresh();
            $invoice->updateStatus();

            // Post to bank if account provided
            if ($request->bank_account_id) {
                $account    = BankAccount::find($request->bank_account_id);
                $newBalance = $account->current_balance + $request->amount;
                BankTransaction::create([
                    'date'               => $request->payment_date,
                    'bank_account_id'    => $request->bank_account_id,
                    'transaction_type'   => 'deposit',
                    'description'        => "AR Payment: {$invoice->partner_name} - {$invoice->invoice_number}",
                    'credit'             => $request->amount,
                    'debit'              => 0,
                    'balance'            => $newBalance,
                    'reference_number'   => $invoice->invoice_number,
                    'transactionable_type' => InvoicePayment::class,
                    'transactionable_id'   => $payment->id,
                    'created_by'         => auth()->id(),
                ]);
                $account->update(['current_balance' => $newBalance]);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment recorded.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }
}
