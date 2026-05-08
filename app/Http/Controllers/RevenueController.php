<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\EventCategory;
use App\Models\RevenueEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $query = RevenueEntry::with(['eventCategory', 'bankAccount', 'creator'])
            ->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->filled('event_category_id')) {
            $query->where('event_category_id', $request->event_category_id);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $revenues = $query->paginate(20)->withQueryString();
        $total    = $query->sum('amount');
        $categories = EventCategory::active()->get();

        return view('revenue.index', compact('revenues', 'total', 'categories'));
    }

    public function create()
    {
        $categories   = EventCategory::active()->get();
        $bankAccounts = BankAccount::active()->get();
        $paymentMethods = $this->paymentMethods();
        return view('revenue.create', compact('categories', 'bankAccounts', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'              => 'required|date',
            'event_name'        => 'required|string|max:255',
            'event_category_id' => 'required|exists:event_categories,id',
            'amount'            => 'required|numeric|min:0.01',
            'payment_method'    => 'required|string',
            'bank_account_id'   => 'nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->only([
                'date', 'event_name', 'event_category_id', 'amount',
                'payment_method', 'bank_account_id', 'notes',
            ]);
            $data['created_by']        = auth()->id();
            $data['reference_number']  = 'REV-' . strtoupper(Str::random(8));

            $revenue = RevenueEntry::create($data);

            // Auto-post to bank transactions if bank account is set
            if ($revenue->bank_account_id) {
                $account = BankAccount::find($revenue->bank_account_id);
                $newBalance = $account->current_balance + $revenue->amount;

                BankTransaction::create([
                    'date'               => $revenue->date,
                    'bank_account_id'    => $revenue->bank_account_id,
                    'transaction_type'   => 'revenue',
                    'description'        => "Revenue: {$revenue->event_name}",
                    'credit'             => $revenue->amount,
                    'debit'              => 0,
                    'balance'            => $newBalance,
                    'reference_number'   => $revenue->reference_number,
                    'transactionable_type' => RevenueEntry::class,
                    'transactionable_id'   => $revenue->id,
                    'created_by'         => auth()->id(),
                ]);

                $account->update(['current_balance' => $newBalance]);
            }
        });

        return redirect()->route('revenue.index')
            ->with('success', 'Revenue entry recorded successfully.');
    }

    public function show(RevenueEntry $revenue)
    {
        $revenue->load(['eventCategory', 'bankAccount', 'creator', 'bankTransaction']);
        return view('revenue.show', compact('revenue'));
    }

    public function edit(RevenueEntry $revenue)
    {
        $categories   = EventCategory::active()->get();
        $bankAccounts = BankAccount::active()->get();
        $paymentMethods = $this->paymentMethods();
        return view('revenue.edit', compact('revenue', 'categories', 'bankAccounts', 'paymentMethods'));
    }

    public function update(Request $request, RevenueEntry $revenue)
    {
        $request->validate([
            'date'              => 'required|date',
            'event_name'        => 'required|string|max:255',
            'event_category_id' => 'required|exists:event_categories,id',
            'amount'            => 'required|numeric|min:0.01',
            'payment_method'    => 'required|string',
            'bank_account_id'   => 'nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request, $revenue) {
            $oldAmount    = $revenue->amount;
            $oldAccountId = $revenue->bank_account_id;

            $revenue->update(array_merge(
                $request->only(['date', 'event_name', 'event_category_id', 'amount', 'payment_method', 'bank_account_id', 'notes']),
                ['updated_by' => auth()->id()]
            ));

            // Update the linked bank transaction if exists
            $txn = $revenue->bankTransaction;
            if ($txn) {
                // Reverse old balance on old account
                if ($oldAccountId) {
                    $oldAccount = BankAccount::find($oldAccountId);
                    $oldAccount->update(['current_balance' => $oldAccount->current_balance - $oldAmount]);
                }

                if ($revenue->bank_account_id) {
                    $account = BankAccount::find($revenue->bank_account_id);
                    $newBalance = $account->current_balance + $revenue->amount;
                    $txn->update([
                        'date'           => $revenue->date,
                        'bank_account_id'=> $revenue->bank_account_id,
                        'description'    => "Revenue: {$revenue->event_name}",
                        'credit'         => $revenue->amount,
                        'balance'        => $newBalance,
                    ]);
                    $account->update(['current_balance' => $newBalance]);
                } else {
                    $txn->delete();
                }
            }
        });

        return redirect()->route('revenue.index')
            ->with('success', 'Revenue entry updated.');
    }

    public function destroy(RevenueEntry $revenue)
    {
        DB::transaction(function () use ($revenue) {
            if ($revenue->bank_account_id && $revenue->bankTransaction) {
                $account = BankAccount::find($revenue->bank_account_id);
                $account->update(['current_balance' => $account->current_balance - $revenue->amount]);
                $revenue->bankTransaction->delete();
            }
            $revenue->delete();
        });

        return redirect()->route('revenue.index')
            ->with('success', 'Revenue entry deleted.');
    }

    private function paymentMethods(): array
    {
        return [
            'cash'          => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'paymongo'      => 'PayMongo',
            'gcash'         => 'GCash',
            'maya'          => 'Maya',
            'check'         => 'Check',
        ];
    }
}
