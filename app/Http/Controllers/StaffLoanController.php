<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\LoanDeduction;
use App\Models\StaffLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffLoanController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffLoan::with('creator')->orderByDesc('date_issued');

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('employee_name', 'like', "%{$s}%")
                  ->orWhere('employee_id', 'like', "%{$s}%");
            });
        }

        $loans   = $query->paginate(20)->withQueryString();
        $summary = [
            'total_outstanding' => StaffLoan::active()->sum('outstanding_balance'),
            'total_loans'       => StaffLoan::count(),
            'active_count'      => StaffLoan::active()->count(),
        ];

        return view('staff-loans.index', compact('loans', 'summary'));
    }

    public function create()
    {
        $bankAccounts = BankAccount::active()->get();
        return view('staff-loans.create', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_name'    => 'required|string|max:255',
            'loan_amount'      => 'required|numeric|min:0.01',
            'date_issued'      => 'required|date',
            'deduction_type'   => 'required|in:monthly,manual,one_time',
            'monthly_deduction'=> 'nullable|numeric|min:0',
            'bank_account_id'  => 'nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request) {
            $loan = StaffLoan::create(array_merge(
                $request->only([
                    'employee_name', 'employee_id', 'department', 'position',
                    'loan_amount', 'date_issued', 'deduction_type',
                    'monthly_deduction', 'purpose', 'bank_account_id',
                ]),
                [
                    'outstanding_balance' => $request->loan_amount,
                    'created_by'         => auth()->id(),
                ]
            ));

            // Deduct from bank account (loan disbursement)
            if ($loan->bank_account_id) {
                $account    = BankAccount::find($loan->bank_account_id);
                $newBalance = $account->current_balance - $loan->loan_amount;
                BankTransaction::create([
                    'date'             => $loan->date_issued,
                    'bank_account_id'  => $loan->bank_account_id,
                    'transaction_type' => 'withdrawal',
                    'description'      => "Staff Loan Disbursed: {$loan->employee_name}",
                    'debit'            => $loan->loan_amount,
                    'credit'           => 0,
                    'balance'          => $newBalance,
                    'reference_number' => 'LOAN-' . strtoupper(Str::random(6)),
                    'created_by'       => auth()->id(),
                ]);
                $account->update(['current_balance' => $newBalance]);
            }
        });

        return redirect()->route('staff-loans.index')->with('success', 'Staff loan recorded.');
    }

    public function show(StaffLoan $staffLoan)
    {
        $staffLoan->load(['deductions.creator', 'bankAccount', 'creator']);
        return view('staff-loans.show', compact('staffLoan'));
    }

    public function edit(StaffLoan $staffLoan)
    {
        $bankAccounts = BankAccount::active()->get();
        return view('staff-loans.edit', compact('staffLoan', 'bankAccounts'));
    }

    public function update(Request $request, StaffLoan $staffLoan)
    {
        $request->validate([
            'employee_name'    => 'required|string|max:255',
            'deduction_type'   => 'required|in:monthly,manual,one_time',
            'monthly_deduction'=> 'nullable|numeric|min:0',
            'status'           => 'required|in:active,fully_paid,written_off',
        ]);

        $staffLoan->update($request->only([
            'employee_name', 'employee_id', 'department', 'position',
            'deduction_type', 'monthly_deduction', 'status', 'purpose',
        ]));

        return redirect()->route('staff-loans.show', $staffLoan)->with('success', 'Loan updated.');
    }

    public function recordDeduction(Request $request, StaffLoan $staffLoan)
    {
        $request->validate([
            'deduction_date' => 'required|date',
            'amount'         => 'required|numeric|min:0.01|max:' . $staffLoan->outstanding_balance,
            'deduction_type' => 'required|in:monthly,manual',
        ]);

        DB::transaction(function () use ($request, $staffLoan) {
            LoanDeduction::create([
                'staff_loan_id'  => $staffLoan->id,
                'deduction_date' => $request->deduction_date,
                'amount'         => $request->amount,
                'deduction_type' => $request->deduction_type,
                'reference_number'=> $request->reference_number,
                'notes'          => $request->notes,
                'created_by'     => auth()->id(),
            ]);
            $staffLoan->recalculateBalance();
        });

        return redirect()->route('staff-loans.show', $staffLoan)->with('success', 'Deduction recorded.');
    }
}
