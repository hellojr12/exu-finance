@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- ── Period Filter ─────────────────────────────────────────────────────── --}}
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Overview for
                    {{ $start->format('M d') }} – {{ $end->format('M d, Y') }}
                </li>
            </ol>
        </nav>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
        <select name="period" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            @foreach(['daily'=>'Today','weekly'=>'This Week','monthly'=>'This Month','quarterly'=>'This Quarter','yearly'=>'This Year','custom'=>'Custom'] as $val=>$lbl)
            <option value="{{ $val }}" {{ $period===$val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
        @if($period === 'custom')
        <input type="date" name="start_date" class="form-control form-control-sm"
               value="{{ request('start_date', $start->format('Y-m-d')) }}" style="width:auto;">
        <span class="text-muted small">to</span>
        <input type="date" name="end_date" class="form-control form-control-sm"
               value="{{ request('end_date', $end->format('Y-m-d')) }}" style="width:auto;">
        <button class="btn btn-sm btn-primary">Apply</button>
        @endif
    </form>
</div>

{{-- ── Alert Strips ──────────────────────────────────────────────────────── --}}
@if($overdueInvoices > 0 || $overdueBills > 0 || $unusualExpenses->count() > 0)
<div class="row g-2 mb-3">
    @if($overdueInvoices > 0)
    <div class="col-md-auto">
        <div class="alert-strip danger">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>{{ $overdueInvoices }} overdue invoice{{ $overdueInvoices > 1 ? 's' : '' }}</strong>
            — <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="fw-bold">Review AR</a>
        </div>
    </div>
    @endif
    @if($overdueBills > 0)
    <div class="col-md-auto">
        <div class="alert-strip warning">
            <i class="bi bi-clock-history me-2"></i>
            <strong>{{ $overdueBills }} overdue bill{{ $overdueBills > 1 ? 's' : '' }}</strong>
            — <a href="{{ route('bills.index', ['status' => 'overdue']) }}" class="fw-bold">Review AP</a>
        </div>
    </div>
    @endif
    @if($unusualExpenses->count() > 0)
    <div class="col-md-auto">
        <div class="alert-strip info">
            <i class="bi bi-graph-up me-2"></i>
            <strong>{{ $unusualExpenses->count() }} unusual expense{{ $unusualExpenses->count() > 1 ? 's' : '' }}</strong>
            detected (above 2× average)
        </div>
    </div>
    @endif
</div>
@endif

{{-- ── KPI Cards ─────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @php
        $revChange = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue * 100) : 0;
        $expChange = $prevExpenses > 0 ? (($totalExpenses - $prevExpenses) / $prevExpenses * 100) : 0;
        $netChange = $prevNetProfit != 0 ? (($netProfit - $prevNetProfit) / abs($prevNetProfit) * 100) : 0;
    @endphp

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon" style="background:#eff6ff;">
                    <i class="bi bi-graph-up-arrow" style="color:#3b82f6;"></i>
                </div>
                <span class="kpi-change {{ $revChange >= 0 ? 'up' : 'down' }}">
                    <i class="bi bi-arrow-{{ $revChange >= 0 ? 'up' : 'down' }}-right"></i>
                    {{ number_format(abs($revChange), 1) }}%
                </span>
            </div>
            <div class="kpi-value">₱{{ number_format($totalRevenue, 2) }}</div>
            <div class="kpi-label">Total Revenue</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon" style="background:#fef2f2;">
                    <i class="bi bi-receipt-cutoff" style="color:#ef4444;"></i>
                </div>
                <span class="kpi-change {{ $expChange <= 0 ? 'up' : 'down' }}">
                    <i class="bi bi-arrow-{{ $expChange >= 0 ? 'up' : 'down' }}-right"></i>
                    {{ number_format(abs($expChange), 1) }}%
                </span>
            </div>
            <div class="kpi-value">₱{{ number_format($totalExpenses, 2) }}</div>
            <div class="kpi-label">Total Expenses</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon" style="background:{{ $netProfit >= 0 ? '#f0fdf4' : '#fef2f2' }};">
                    <i class="bi bi-coin" style="color:{{ $netProfit >= 0 ? '#10b981' : '#ef4444' }};"></i>
                </div>
                <span class="kpi-change {{ $netChange >= 0 ? 'up' : 'down' }}">
                    <i class="bi bi-arrow-{{ $netChange >= 0 ? 'up' : 'down' }}-right"></i>
                    {{ number_format(abs($netChange), 1) }}%
                </span>
            </div>
            <div class="kpi-value {{ $netProfit >= 0 ? 'num-green' : 'num-red' }}">
                ₱{{ number_format(abs($netProfit), 2) }}
                @if($netProfit < 0)<small class="text-danger fs-6">(Loss)</small>@endif
            </div>
            <div class="kpi-label">Net Profit / (Loss)</div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon" style="background:#f0fdf4;">
                    <i class="bi bi-bank" style="color:#10b981;"></i>
                </div>
                <span class="text-muted" style="font-size:.75rem;">All accounts</span>
            </div>
            <div class="kpi-value num-green">₱{{ number_format($cashInBank, 2) }}</div>
            <div class="kpi-label">Cash in Bank</div>
        </div>
    </div>
</div>

{{-- ── Charts Row 1 ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Revenue vs Expense Trend --}}
    <div class="col-12 col-xl-8">
        <div class="chart-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="chart-title">Revenue vs Expense Trend</div>
                <span class="text-muted small">{{ now()->year }}</span>
            </div>
            <canvas id="trendChart" height="100"></canvas>
        </div>
    </div>
    {{-- Expense Breakdown Pie --}}
    <div class="col-12 col-xl-4">
        <div class="chart-card h-100">
            <div class="chart-title mb-3">Expense Breakdown</div>
            <canvas id="expensePieChart"></canvas>
            <div id="pieLegend" class="mt-3"></div>
        </div>
    </div>
</div>

{{-- ── Charts Row 2 ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Revenue by Category --}}
    <div class="col-12 col-xl-6">
        <div class="chart-card">
            <div class="chart-title mb-3">Revenue by Event Category</div>
            <canvas id="revenueCategoryChart" height="120"></canvas>
        </div>
    </div>
    {{-- Bank Account Balances --}}
    <div class="col-12 col-xl-6">
        <div class="chart-card">
            <div class="chart-title mb-3">Bank Account Balances</div>
            <canvas id="bankBalanceChart" height="120"></canvas>
        </div>
    </div>
</div>

{{-- ── Tables ────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Recent Bank Transactions --}}
    <div class="col-12">
        <div class="table-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="table-card-title">Recent Bank Transactions</div>
                <a href="{{ route('bank-transactions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                    <tr>
                        <th>Date</th><th>Account</th><th>Description</th>
                        <th>Type</th><th class="text-end">Debit</th>
                        <th class="text-end">Credit</th><th class="text-end">Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentTransactions as $txn)
                    <tr>
                        <td>{{ $txn->date->format('M d, Y') }}</td>
                        <td>{{ $txn->bankAccount?->name }}</td>
                        <td>{{ $txn->description }}</td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.7rem;">
                                {{ ucwords(str_replace('_', ' ', $txn->transaction_type)) }}
                            </span>
                        </td>
                        <td class="text-end num {{ $txn->debit > 0 ? 'num-red' : '' }}">
                            {{ $txn->debit > 0 ? '₱'.number_format($txn->debit, 2) : '—' }}
                        </td>
                        <td class="text-end num {{ $txn->credit > 0 ? 'num-green' : '' }}">
                            {{ $txn->credit > 0 ? '₱'.number_format($txn->credit, 2) : '—' }}
                        </td>
                        <td class="text-end num fw-500">₱{{ number_format($txn->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No transactions yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Accounts Receivable --}}
    <div class="col-12 col-xl-4">
        <div class="table-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="table-card-title"><i class="bi bi-file-earmark-text me-1 text-primary"></i>Accounts Receivable</div>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Partner</th><th>Due</th><th class="text-end">Balance</th></tr></thead>
                <tbody>
                @forelse($receivables as $inv)
                <tr>
                    <td>
                        <a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none fw-500">
                            {{ Str::limit($inv->partner_name, 20) }}
                        </a>
                        @if($inv->is_overdue)
                        <span class="badge badge-overdue" style="font-size:.65rem;">Overdue</span>
                        @endif
                    </td>
                    <td class="{{ $inv->is_overdue ? 'text-danger' : '' }}">{{ $inv->due_date->format('M d') }}</td>
                    <td class="text-end num fw-500">₱{{ number_format($inv->balance_due, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No outstanding invoices.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Accounts Payable --}}
    <div class="col-12 col-xl-4">
        <div class="table-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="table-card-title"><i class="bi bi-file-earmark-minus me-1 text-danger"></i>Accounts Payable</div>
                <a href="{{ route('bills.index') }}" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Supplier</th><th>Due</th><th class="text-end">Balance</th></tr></thead>
                <tbody>
                @forelse($payables as $bill)
                <tr>
                    <td>
                        <a href="{{ route('bills.show', $bill) }}" class="text-decoration-none fw-500">
                            {{ Str::limit($bill->supplier_name, 20) }}
                        </a>
                        @if($bill->is_overdue)
                        <span class="badge badge-overdue" style="font-size:.65rem;">Overdue</span>
                        @endif
                    </td>
                    <td class="{{ $bill->is_overdue ? 'text-danger' : '' }}">{{ $bill->due_date->format('M d') }}</td>
                    <td class="text-end num fw-500 num-red">₱{{ number_format($bill->balance_due, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No outstanding bills.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Staff Loans --}}
    <div class="col-12 col-xl-4">
        <div class="table-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="table-card-title"><i class="bi bi-person-badge me-1 text-warning"></i>Active Staff Loans</div>
                <a href="{{ route('staff-loans.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Employee</th><th class="text-end">Balance</th><th>Deduction</th></tr></thead>
                <tbody>
                @forelse($activeLoans as $loan)
                <tr>
                    <td>
                        <a href="{{ route('staff-loans.show', $loan) }}" class="text-decoration-none fw-500">
                            {{ Str::limit($loan->employee_name, 18) }}
                        </a>
                    </td>
                    <td class="text-end num fw-500">₱{{ number_format($loan->outstanding_balance, 2) }}</td>
                    <td class="text-muted" style="font-size:.78rem;">
                        {{ ucfirst($loan->deduction_type) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No active loans.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const revenueData = @json($revenueByMonth);
const expenseData = @json($expenseByMonth);
const expBreakdown = @json($expenseBreakdown);
const revCategories = @json($revenueByCategory);
const bankAccounts  = @json($bankAccounts->map(fn($a) => ['name' => $a->name, 'balance' => (float)$a->current_balance]));

// ── Trend Chart ─────────────────────────────────────────────────────────────
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Revenue',
                data: revenueData,
                backgroundColor: 'rgba(59,130,246,.2)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                borderRadius: 4,
                type: 'bar',
            },
            {
                label: 'Expenses',
                data: expenseData,
                backgroundColor: 'rgba(239,68,68,.15)',
                borderColor: '#ef4444',
                borderWidth: 2,
                borderRadius: 4,
                type: 'bar',
            },
            {
                label: 'Net Profit',
                data: revenueData.map((r, i) => r - expenseData[i]),
                borderColor: '#10b981',
                borderWidth: 2,
                pointRadius: 4,
                fill: false,
                tension: .3,
                type: 'line',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index' },
        plugins: {
            legend: { position: 'top', labels: { font: { size: 12 } } },
            tooltip: {
                callbacks: {
                    label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2})
                }
            }
        },
        scales: {
            y: {
                grid: { color: '#f1f5f9' },
                ticks: {
                    callback: v => '₱' + (v/1000).toFixed(0) + 'k',
                    font: { size: 11 }
                }
            },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});

// ── Expense Pie ─────────────────────────────────────────────────────────────
const pieCtx = document.getElementById('expensePieChart');
if (expBreakdown.length > 0) {
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: expBreakdown.map(e => e.label),
            datasets: [{
                data: expBreakdown.map(e => e.value),
                backgroundColor: expBreakdown.map(e => e.color),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ₱${ctx.parsed.toLocaleString('en-PH', {minimumFractionDigits:2})}`
                    }
                }
            }
        }
    });
    const legend = document.getElementById('pieLegend');
    expBreakdown.slice(0,6).forEach(e => {
        const pct = expBreakdown.reduce((a,b)=>a+b.value,0) > 0
            ? (e.value / expBreakdown.reduce((a,b)=>a+b.value,0) * 100).toFixed(1) : 0;
        legend.innerHTML += `<div class="d-flex align-items-center justify-content-between mb-1">
            <div class="d-flex align-items-center gap-2">
                <div style="width:10px;height:10px;border-radius:2px;background:${e.color};flex-shrink:0;"></div>
                <span style="font-size:.75rem;">${e.label}</span>
            </div>
            <span style="font-size:.75rem;font-weight:600;">${pct}%</span>
        </div>`;
    });
} else {
    pieCtx.closest('.chart-card').innerHTML += '<p class="text-muted text-center py-4">No expense data for selected period.</p>';
}

// ── Revenue by Category ─────────────────────────────────────────────────────
new Chart(document.getElementById('revenueCategoryChart'), {
    type: 'bar',
    data: {
        labels: revCategories.map(r => r.label),
        datasets: [{
            label: 'Revenue',
            data: revCategories.map(r => r.value),
            backgroundColor: revCategories.map(r => r.color + '99'),
            borderColor: revCategories.map(r => r.color),
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ₱${ctx.parsed.x.toLocaleString('en-PH', {minimumFractionDigits:2})}` } }
        },
        scales: {
            x: {
                grid: { color: '#f1f5f9' },
                ticks: { callback: v => '₱'+(v/1000).toFixed(0)+'k', font:{size:11} }
            },
            y: { grid: { display: false }, ticks: { font:{size:11} } }
        }
    }
});

// ── Bank Balances ────────────────────────────────────────────────────────────
new Chart(document.getElementById('bankBalanceChart'), {
    type: 'bar',
    data: {
        labels: bankAccounts.map(a => a.name),
        datasets: [{
            label: 'Balance',
            data: bankAccounts.map(a => a.balance),
            backgroundColor: ['#3b82f680','#8b5cf680','#10b98180','#f59e0b80'],
            borderColor:     ['#3b82f6','#8b5cf6','#10b981','#f59e0b'],
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ₱${ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2})}` } }
        },
        scales: {
            y: { grid:{color:'#f1f5f9'}, ticks:{callback:v=>'₱'+(v/1000).toFixed(0)+'k',font:{size:11}} },
            x: { grid:{display:false}, ticks:{font:{size:11}} }
        }
    }
});
</script>
@endpush
