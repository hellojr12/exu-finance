<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Bill;
use App\Models\ExpenseEntry;
use App\Models\Invoice;
use App\Models\RevenueEntry;
use App\Models\StaffLoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        [$start, $end, $period] = $this->resolvePeriod($request);
        [$compareStart, $compareEnd] = $this->comparePeriod($start, $end, $period);

        // KPI Cards
        $totalRevenue   = RevenueEntry::forPeriod($start, $end)->sum('amount');
        $totalExpenses  = ExpenseEntry::forPeriod($start, $end)->sum('amount');
        $netProfit      = $totalRevenue - $totalExpenses;
        $cashInBank     = BankAccount::active()->sum('current_balance');

        // Comparative KPIs
        $prevRevenue    = RevenueEntry::forPeriod($compareStart, $compareEnd)->sum('amount');
        $prevExpenses   = ExpenseEntry::forPeriod($compareStart, $compareEnd)->sum('amount');
        $prevNetProfit  = $prevRevenue - $prevExpenses;

        // Revenue vs Expense Trend (monthly for current year)
        $year = $start->year;
        $revenueByMonth  = $this->monthlyTotals(RevenueEntry::class, $year);
        $expenseByMonth  = $this->monthlyTotals(ExpenseEntry::class, $year);

        // Expense Breakdown Pie
        $expenseBreakdown = ExpenseEntry::with('expenseCategory')
            ->forPeriod($start, $end)
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->with('expenseCategory')
            ->get()
            ->map(fn($e) => [
                'label' => $e->expenseCategory?->name ?? 'Uncategorized',
                'value' => (float) $e->total,
                'color' => $e->expenseCategory?->color ?? '#6B7280',
            ]);

        // Revenue by Event Category
        $revenueByCategory = RevenueEntry::forPeriod($start, $end)
            ->select('event_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('event_category_id')
            ->with('eventCategory')
            ->get()
            ->map(fn($r) => [
                'label' => $r->eventCategory?->name ?? 'Uncategorized',
                'value' => (float) $r->total,
                'color' => $r->eventCategory?->color ?? '#3B82F6',
            ]);

        // Bank Account Balances
        $bankAccounts = BankAccount::active()->get();

        // Recent Bank Transactions
        $recentTransactions = BankTransaction::with('bankAccount', 'creator')
            ->orderByDesc('date')->orderByDesc('id')
            ->limit(10)->get();

        // Accounts Receivable
        $receivables = Invoice::unpaid()
            ->orderBy('due_date')
            ->limit(10)->get();

        // Accounts Payable
        $payables = Bill::unpaid()
            ->orderBy('due_date')
            ->limit(10)->get();

        // Staff Loans
        $activeLoans = StaffLoan::active()->get();

        // Alerts
        $overdueInvoices = Invoice::overdue()->count();
        $overdueBills    = Bill::overdue()->count();

        // Unusual expenses (above 2x the average for the period)
        $avgExpense = ExpenseEntry::forPeriod($start, $end)->avg('amount') ?? 0;
        $unusualExpenses = ExpenseEntry::forPeriod($start, $end)
            ->where('amount', '>', $avgExpense * 2)
            ->orderByDesc('amount')
            ->limit(5)->get();

        return view('dashboard.index', compact(
            'start', 'end', 'period',
            'totalRevenue', 'totalExpenses', 'netProfit', 'cashInBank',
            'prevRevenue', 'prevExpenses', 'prevNetProfit',
            'revenueByMonth', 'expenseByMonth',
            'expenseBreakdown', 'revenueByCategory',
            'bankAccounts', 'recentTransactions',
            'receivables', 'payables', 'activeLoans',
            'overdueInvoices', 'overdueBills', 'unusualExpenses'
        ));
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'monthly');
        $today  = Carbon::today();

        $start = match($period) {
            'daily'     => $today->copy(),
            'weekly'    => $today->copy()->startOfWeek(),
            'monthly'   => $today->copy()->startOfMonth(),
            'quarterly' => $today->copy()->startOfQuarter(),
            'yearly'    => $today->copy()->startOfYear(),
            'custom'    => Carbon::parse($request->get('start_date', $today->copy()->startOfMonth())),
            default     => $today->copy()->startOfMonth(),
        };

        $end = match($period) {
            'daily'     => $today->copy(),
            'weekly'    => $today->copy()->endOfWeek(),
            'monthly'   => $today->copy()->endOfMonth(),
            'quarterly' => $today->copy()->endOfQuarter(),
            'yearly'    => $today->copy()->endOfYear(),
            'custom'    => Carbon::parse($request->get('end_date', $today->copy()->endOfMonth())),
            default     => $today->copy()->endOfMonth(),
        };

        return [$start, $end, $period];
    }

    private function comparePeriod(Carbon $start, Carbon $end, string $period): array
    {
        $diff = $start->diffInDays($end);
        return [
            $start->copy()->subDays($diff + 1),
            $end->copy()->subDays($diff + 1),
        ];
    }

    private function monthlyTotals(string $model, int $year): array
    {
        $rows = $model::whereYear('date', $year)
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy(DB::raw('MONTH(date)'))
            ->pluck('total', 'month')
            ->toArray();

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = (float) ($rows[$m] ?? 0);
        }
        return $result;
    }
}
