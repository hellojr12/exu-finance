<?php

namespace App\Http\Controllers;

use App\Exports\BalanceSheetExport;
use App\Exports\CashFlowExport;
use App\Exports\IncomeStatementExport;
use App\Models\BankAccount;
use App\Models\ExpenseEntry;
use App\Models\Invoice;
use App\Models\Bill;
use App\Models\RevenueEntry;
use App\Models\StaffLoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class FinancialStatementController extends Controller
{
    // ─── Income Statement ────────────────────────────────────────────────────

    public function incomeStatement(Request $request)
    {
        [$start, $end, $period, $year] = $this->resolvePeriod($request);
        $compareMode = $request->boolean('compare');

        $data = $this->buildIncomeStatement($start, $end);

        $compareData = null;
        if ($compareMode) {
            [$cs, $ce] = $this->priorPeriod($start, $end, $period);
            $compareData = $this->buildIncomeStatement($cs, $ce);
        }

        return view('financial-statements.income-statement', compact(
            'data', 'compareData', 'start', 'end', 'period', 'year', 'compareMode'
        ));
    }

    private function buildIncomeStatement(Carbon $start, Carbon $end): array
    {
        $revenues = RevenueEntry::forPeriod($start, $end)
            ->select('event_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('event_category_id')
            ->with('eventCategory')
            ->get();

        $expenses = ExpenseEntry::forPeriod($start, $end)
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->with('expenseCategory')
            ->get();

        $totalRevenue = $revenues->sum('total');
        $totalExpense = $expenses->sum('total');
        $grossProfit  = $totalRevenue - $totalExpense;
        $netIncome    = $grossProfit; // Extend with taxes if needed

        return compact('revenues', 'expenses', 'totalRevenue', 'totalExpense', 'grossProfit', 'netIncome');
    }

    // ─── Balance Sheet ───────────────────────────────────────────────────────

    public function balanceSheet(Request $request)
    {
        $asOf = $request->filled('as_of') ? Carbon::parse($request->as_of) : Carbon::today();
        $compareMode = $request->boolean('compare');

        $data = $this->buildBalanceSheet($asOf);

        $compareData = null;
        if ($compareMode) {
            $compareAsOf = $asOf->copy()->subYear();
            $compareData = $this->buildBalanceSheet($compareAsOf);
        }

        return view('financial-statements.balance-sheet', compact(
            'data', 'compareData', 'asOf', 'compareMode'
        ));
    }

    private function buildBalanceSheet(Carbon $asOf): array
    {
        // Current Assets
        $cashInBank      = BankAccount::active()->sum('current_balance');
        $accountsReceivable = Invoice::unpaid()->sum('balance_due');
        $totalCurrentAssets = $cashInBank + $accountsReceivable;

        // Non-current Assets (Staff Loans as receivable from employees)
        $staffLoansTotal = StaffLoan::active()->sum('outstanding_balance');
        $totalNonCurrentAssets = $staffLoansTotal;

        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        // Liabilities
        $accountsPayable   = Bill::unpaid()->sum('balance_due');
        $totalLiabilities  = $accountsPayable;

        // Equity (Retained Earnings from inception to asOf)
        $totalRevenue = RevenueEntry::where('date', '<=', $asOf)->sum('amount');
        $totalExpense = ExpenseEntry::where('date', '<=', $asOf)->sum('amount');
        $retainedEarnings = $totalRevenue - $totalExpense;
        $totalEquity      = $retainedEarnings;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return compact(
            'cashInBank', 'accountsReceivable', 'totalCurrentAssets',
            'staffLoansTotal', 'totalNonCurrentAssets', 'totalAssets',
            'accountsPayable', 'totalLiabilities',
            'retainedEarnings', 'totalEquity', 'totalLiabilitiesAndEquity'
        );
    }

    // ─── Cash Flow Statement ─────────────────────────────────────────────────

    public function cashFlow(Request $request)
    {
        [$start, $end, $period, $year] = $this->resolvePeriod($request);

        $data = $this->buildCashFlow($start, $end);

        return view('financial-statements.cash-flow', compact('data', 'start', 'end', 'period', 'year'));
    }

    private function buildCashFlow(Carbon $start, Carbon $end): array
    {
        // Operating Activities
        $revenueCollected = RevenueEntry::forPeriod($start, $end)->sum('amount');
        $expensesPaid     = ExpenseEntry::forPeriod($start, $end)->sum('amount');
        $invoicePayments  = \App\Models\InvoicePayment::whereBetween('payment_date', [$start, $end])->sum('amount');
        $billPayments     = \App\Models\BillPayment::whereBetween('payment_date', [$start, $end])->sum('amount');

        $netOperating = $revenueCollected + $invoicePayments - $expensesPaid - $billPayments;

        // Investing Activities
        $loansDisbursed   = StaffLoan::whereBetween('date_issued', [$start, $end])->sum('loan_amount');
        $loanRepayments   = \App\Models\LoanDeduction::whereBetween('deduction_date', [$start, $end])->sum('amount');
        $netInvesting     = $loanRepayments - $loansDisbursed;

        // Net Change in Cash
        $netCashFlow   = $netOperating + $netInvesting;
        $openingCash   = BankAccount::active()->sum('opening_balance');
        $endingCash    = BankAccount::active()->sum('current_balance');

        return compact(
            'revenueCollected', 'expensesPaid', 'invoicePayments', 'billPayments', 'netOperating',
            'loansDisbursed', 'loanRepayments', 'netInvesting',
            'netCashFlow', 'openingCash', 'endingCash'
        );
    }

    // ─── Exports ─────────────────────────────────────────────────────────────

    public function exportIncomeStatement(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $data = $this->buildIncomeStatement($start, $end);
        $filename = 'income-statement-' . $start->format('Ymd') . '-' . $end->format('Ymd');

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('financial-statements.pdf.income-statement', compact('data', 'start', 'end'));
            return $pdf->download("{$filename}.pdf");
        }

        return Excel::download(new IncomeStatementExport($data, $start, $end), "{$filename}.xlsx");
    }

    public function exportBalanceSheet(Request $request)
    {
        $asOf = $request->filled('as_of') ? Carbon::parse($request->as_of) : Carbon::today();
        $data = $this->buildBalanceSheet($asOf);
        $filename = 'balance-sheet-' . $asOf->format('Ymd');

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('financial-statements.pdf.balance-sheet', compact('data', 'asOf'));
            return $pdf->download("{$filename}.pdf");
        }

        return Excel::download(new BalanceSheetExport($data, $asOf), "{$filename}.xlsx");
    }

    public function exportCashFlow(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $data = $this->buildCashFlow($start, $end);
        $filename = 'cash-flow-' . $start->format('Ymd') . '-' . $end->format('Ymd');

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('financial-statements.pdf.cash-flow', compact('data', 'start', 'end'));
            return $pdf->download("{$filename}.pdf");
        }

        return Excel::download(new CashFlowExport($data, $start, $end), "{$filename}.xlsx");
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'monthly');
        $year   = $request->get('year', now()->year);
        $month  = $request->get('month', now()->month);
        $quarter= $request->get('quarter', now()->quarter);

        [$start, $end] = match($period) {
            'monthly'   => [Carbon::create($year, $month, 1)->startOfMonth(),
                            Carbon::create($year, $month, 1)->endOfMonth()],
            'quarterly' => [Carbon::create($year, 1, 1)->startOfQuarter()->addMonths(($quarter - 1) * 3),
                            Carbon::create($year, 1, 1)->startOfQuarter()->addMonths(($quarter - 1) * 3)->endOfQuarter()],
            'yearly'    => [Carbon::create($year, 1, 1)->startOfYear(),
                            Carbon::create($year, 12, 31)->endOfYear()],
            'custom'    => [Carbon::parse($request->start_date ?? now()->startOfMonth()),
                            Carbon::parse($request->end_date ?? now()->endOfMonth())],
            default     => [now()->startOfMonth(), now()->endOfMonth()],
        };

        return [$start, $end, $period, $year];
    }

    private function priorPeriod(Carbon $start, Carbon $end, string $period): array
    {
        $diff = $start->diffInDays($end) + 1;
        return [$start->copy()->subDays($diff), $end->copy()->subDays($diff)];
    }
}
