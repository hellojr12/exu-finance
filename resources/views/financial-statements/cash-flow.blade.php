@extends('layouts.app')
@section('title', 'Cash Flow Statement')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-water me-2 text-info"></i>Cash Flow Statement</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Cash Flow</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('financial-statements.cash-flow.export', array_merge(request()->query(), ['format'=>'xlsx'])) }}"
           class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="{{ route('financial-statements.cash-flow.export', array_merge(request()->query(), ['format'=>'pdf'])) }}"
           class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
    </div>
</div>

<div class="table-card mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label">Period</label>
            <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach(['monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Annual','custom'=>'Custom'] as $v=>$l)
                <option value="{{ $v }}" {{ $period===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        @if(in_array($period, ['monthly', 'quarterly', 'yearly']))
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <select name="year" class="form-select form-select-sm">
                @for($y=now()->year; $y>=now()->year-10; $y--)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor
            </select>
        </div>
        @if($period === 'monthly')
        <div class="col-md-2">
            <label class="form-label">Month</label>
            <select name="month" class="form-select form-select-sm">
                @for($m=1;$m<=12;$m++)<option value="{{ $m }}" {{ request('month',now()->month)==$m?'selected':'' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>@endfor
            </select>
        </div>
        @endif
        @elseif($period === 'custom')
        <div class="col-md-2">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $end->format('Y-m-d') }}">
        </div>
        @endif
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm">Apply</button>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="text-center mb-4">
        <div class="fw-bold text-info" style="font-size:1.1rem;text-transform:uppercase;letter-spacing:.08em;">Exponential University</div>
        <div class="fw-bold fs-4">Statement of Cash Flows</div>
        <div class="text-muted">{{ $start->format('F d, Y') }} to {{ $end->format('F d, Y') }}</div>
    </div>

    <table class="table" style="max-width:680px;margin:0 auto;">
        <thead>
        <tr><th style="width:65%;">Description</th><th class="text-end">Amount</th></tr>
        </thead>
        <tbody>
        {{-- Operating Activities --}}
        <tr class="table-light">
            <td colspan="2" class="fw-bold" style="text-transform:uppercase;font-size:.82rem;letter-spacing:.05em;">
                <i class="bi bi-gear me-2 text-primary"></i>Operating Activities
            </td>
        </tr>
        <tr><td class="ps-4">Revenue Collected</td><td class="text-end num num-green">₱{{ number_format($data['revenueCollected'], 2) }}</td></tr>
        <tr><td class="ps-4">AR Collections (Invoice Payments)</td><td class="text-end num num-green">₱{{ number_format($data['invoicePayments'], 2) }}</td></tr>
        <tr><td class="ps-4">Expenses Paid</td><td class="text-end num num-red">(₱{{ number_format($data['expensesPaid'], 2) }})</td></tr>
        <tr><td class="ps-4">AP Payments (Bills Paid)</td><td class="text-end num num-red">(₱{{ number_format($data['billPayments'], 2) }})</td></tr>
        <tr class="fw-bold" style="border-top:2px solid #e2e8f0;">
            <td class="ps-4">Net Cash from Operating Activities</td>
            <td class="text-end num {{ $data['netOperating'] >= 0 ? 'num-green' : 'num-red' }}">
                {{ $data['netOperating'] < 0 ? '(' : '' }}₱{{ number_format(abs($data['netOperating']), 2) }}{{ $data['netOperating'] < 0 ? ')' : '' }}
            </td>
        </tr>

        <tr><td style="padding:.6rem;" colspan="2"></td></tr>

        {{-- Investing Activities --}}
        <tr class="table-light">
            <td colspan="2" class="fw-bold" style="text-transform:uppercase;font-size:.82rem;letter-spacing:.05em;">
                <i class="bi bi-graph-up me-2 text-warning"></i>Investing Activities
            </td>
        </tr>
        <tr><td class="ps-4">Staff Loans Disbursed</td><td class="text-end num num-red">(₱{{ number_format($data['loansDisbursed'], 2) }})</td></tr>
        <tr><td class="ps-4">Loan Repayments Collected</td><td class="text-end num num-green">₱{{ number_format($data['loanRepayments'], 2) }}</td></tr>
        <tr class="fw-bold" style="border-top:2px solid #e2e8f0;">
            <td class="ps-4">Net Cash from Investing Activities</td>
            <td class="text-end num {{ $data['netInvesting'] >= 0 ? 'num-green' : 'num-red' }}">
                {{ $data['netInvesting'] < 0 ? '(' : '' }}₱{{ number_format(abs($data['netInvesting']), 2) }}{{ $data['netInvesting'] < 0 ? ')' : '' }}
            </td>
        </tr>

        <tr><td style="padding:.6rem;" colspan="2"></td></tr>

        {{-- Net Change --}}
        <tr style="border-top:3px double #0f172a;">
            <td class="fw-bold fs-6">Net Increase / (Decrease) in Cash</td>
            <td class="text-end num fw-bold fs-6 {{ $data['netCashFlow'] >= 0 ? 'num-green' : 'num-red' }}">
                {{ $data['netCashFlow'] < 0 ? '(' : '' }}₱{{ number_format(abs($data['netCashFlow']), 2) }}{{ $data['netCashFlow'] < 0 ? ')' : '' }}
            </td>
        </tr>
        <tr><td class="ps-4 text-muted">Opening Cash Balance</td><td class="text-end num">₱{{ number_format($data['openingCash'], 2) }}</td></tr>
        <tr class="fw-bold fs-6" style="background:#f0fdf4;">
            <td>Ending Cash Balance</td>
            <td class="text-end num num-green">₱{{ number_format($data['endingCash'], 2) }}</td>
        </tr>
        </tbody>
    </table>

    <div class="text-muted text-center mt-4" style="font-size:.75rem;">
        Generated on {{ now()->format('F d, Y h:i A') }} · Exponential University Finance System
    </div>
</div>
@endsection
