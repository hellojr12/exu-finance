@extends('layouts.app')
@section('title', 'Income Statement')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-journal-richtext me-2 text-primary"></i>Income Statement</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Income Statement</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ request()->fullUrlWithQuery(['format' => 'xlsx']) }}&action=export"
           class="btn btn-outline-success btn-sm" id="exportExcel">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}&action=export"
           class="btn btn-outline-danger btn-sm" id="exportPDF">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
    </div>
</div>

{{-- Period Filter --}}
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
        @if($period === 'monthly')
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <select name="year" class="form-select form-select-sm">
                @for($y=now()->year; $y>=now()->year-5; $y--)
                <option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Month</label>
            <select name="month" class="form-select form-select-sm">
                @for($m=1; $m<=12; $m++)
                <option value="{{ $m }}" {{ request('month',now()->month)==$m?'selected':'' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </select>
        </div>
        @elseif($period === 'quarterly')
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <select name="year" class="form-select form-select-sm">
                @for($y=now()->year; $y>=now()->year-5; $y--)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Quarter</label>
            <select name="quarter" class="form-select form-select-sm">
                @for($q=1;$q<=4;$q++)<option value="{{ $q }}" {{ request('quarter',now()->quarter)==$q?'selected':'' }}>Q{{ $q }}</option>@endfor
            </select>
        </div>
        @elseif($period === 'yearly')
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <select name="year" class="form-select form-select-sm">
                @for($y=now()->year; $y>=now()->year-10; $y--)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor
            </select>
        </div>
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
        <div class="col-md-2 d-flex align-items-end gap-2">
            <button class="btn btn-primary btn-sm">Apply</button>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" name="compare" value="1" id="compare" {{ $compareMode?'checked':'' }} onchange="this.form.submit()">
                <label class="form-check-label" for="compare" style="font-size:.82rem;">Compare</label>
            </div>
        </div>
    </form>
</div>

{{-- Statement --}}
<div class="table-card">
    <div class="text-center mb-4">
        <div class="fw-bold text-primary" style="font-size:1.1rem; text-transform:uppercase; letter-spacing:.08em;">
            Exponential University
        </div>
        <div class="fw-bold fs-4">Income Statement</div>
        <div class="text-muted">{{ $start->format('F d, Y') }} to {{ $end->format('F d, Y') }}</div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th style="width:55%;">Description</th>
                <th class="text-end">{{ $start->format('M d') }}–{{ $end->format('M d, Y') }}</th>
                @if($compareMode && $compareData)
                <th class="text-end text-muted">Prior Period</th>
                <th class="text-end text-muted">Variance</th>
                @endif
            </tr>
            </thead>
            <tbody>
            {{-- Revenue Section --}}
            <tr class="table-light">
                <td colspan="{{ $compareMode ? 4 : 2 }}" class="fw-bold" style="text-transform:uppercase;letter-spacing:.05em;font-size:.82rem;">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Revenue
                </td>
            </tr>
            @foreach($data['revenues'] as $rev)
            <tr>
                <td class="ps-4">{{ $rev->eventCategory?->name ?? 'Uncategorized' }}</td>
                <td class="text-end num">₱{{ number_format($rev->total, 2) }}</td>
                @if($compareMode && $compareData)
                @php
                    $priorRev = $compareData['revenues']->firstWhere('event_category_id', $rev->event_category_id);
                    $priorAmt = $priorRev ? $priorRev->total : 0;
                    $variance = $rev->total - $priorAmt;
                @endphp
                <td class="text-end num text-muted">₱{{ number_format($priorAmt, 2) }}</td>
                <td class="text-end num {{ $variance >= 0 ? 'num-green' : 'num-red' }}">
                    {{ $variance >= 0 ? '+' : '' }}₱{{ number_format($variance, 2) }}
                </td>
                @endif
            </tr>
            @endforeach
            <tr class="fw-bold" style="border-top:2px solid #e2e8f0;">
                <td class="ps-4">Total Revenue</td>
                <td class="text-end num num-green fs-6">₱{{ number_format($data['totalRevenue'], 2) }}</td>
                @if($compareMode && $compareData)
                <td class="text-end num text-muted">₱{{ number_format($compareData['totalRevenue'], 2) }}</td>
                @php $var = $data['totalRevenue'] - $compareData['totalRevenue']; @endphp
                <td class="text-end num {{ $var >= 0 ? 'num-green' : 'num-red' }}">{{ $var>=0?'+':'' }}₱{{ number_format($var,2) }}</td>
                @endif
            </tr>

            <tr><td colspan="{{ $compareMode ? 4 : 2 }}" style="padding:.5rem;"></td></tr>

            {{-- Expenses Section --}}
            <tr class="table-light">
                <td colspan="{{ $compareMode ? 4 : 2 }}" class="fw-bold" style="text-transform:uppercase;letter-spacing:.05em;font-size:.82rem;">
                    <i class="bi bi-receipt-cutoff me-2 text-danger"></i>Expenses
                </td>
            </tr>
            @foreach($data['expenses'] as $exp)
            <tr>
                <td class="ps-4">{{ $exp->expenseCategory?->name ?? 'Uncategorized' }}</td>
                <td class="text-end num">₱{{ number_format($exp->total, 2) }}</td>
                @if($compareMode && $compareData)
                @php
                    $priorExp = $compareData['expenses']->firstWhere('expense_category_id', $exp->expense_category_id);
                    $priorExpAmt = $priorExp ? $priorExp->total : 0;
                    $expVariance = $exp->total - $priorExpAmt;
                @endphp
                <td class="text-end num text-muted">₱{{ number_format($priorExpAmt, 2) }}</td>
                <td class="text-end num {{ $expVariance <= 0 ? 'num-green' : 'num-red' }}">
                    {{ $expVariance >= 0 ? '+' : '' }}₱{{ number_format($expVariance, 2) }}
                </td>
                @endif
            </tr>
            @endforeach
            <tr class="fw-bold" style="border-top:2px solid #e2e8f0;">
                <td class="ps-4">Total Expenses</td>
                <td class="text-end num num-red fs-6">₱{{ number_format($data['totalExpense'], 2) }}</td>
                @if($compareMode && $compareData)
                <td class="text-end num text-muted">₱{{ number_format($compareData['totalExpense'], 2) }}</td>
                @php $varExp = $data['totalExpense'] - $compareData['totalExpense']; @endphp
                <td class="text-end num {{ $varExp <= 0 ? 'num-green' : 'num-red' }}">{{ $varExp>=0?'+':'' }}₱{{ number_format($varExp,2) }}</td>
                @endif
            </tr>

            <tr><td colspan="{{ $compareMode ? 4 : 2 }}" style="border-top:3px double #0f172a;padding:.5rem;"></td></tr>

            {{-- Net Income --}}
            <tr class="fw-bold fs-5" style="background:#f8fafc;">
                <td>Net Income / (Loss)</td>
                <td class="text-end num {{ $data['netIncome'] >= 0 ? 'num-green' : 'num-red' }}">
                    {{ $data['netIncome'] < 0 ? '(' : '' }}₱{{ number_format(abs($data['netIncome']), 2) }}{{ $data['netIncome'] < 0 ? ')' : '' }}
                </td>
                @if($compareMode && $compareData)
                <td class="text-end num text-muted">₱{{ number_format($compareData['netIncome'], 2) }}</td>
                @php $varNet = $data['netIncome'] - $compareData['netIncome']; @endphp
                <td class="text-end num {{ $varNet >= 0 ? 'num-green' : 'num-red' }}">{{ $varNet>=0?'+':'' }}₱{{ number_format($varNet,2) }}</td>
                @endif
            </tr>
            </tbody>
        </table>
    </div>
    <div class="text-muted text-center mt-4" style="font-size:.75rem;">
        Generated on {{ now()->format('F d, Y h:i A') }} · Exponential University Finance System
    </div>
</div>
@endsection

@push('scripts')
<script>
// Wire up export buttons to proper URL
document.getElementById('exportExcel')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.location = '{{ route("financial-statements.income-statement.export") }}?' + new URLSearchParams({...Object.fromEntries(new URL(window.location).searchParams), format:'xlsx'});
});
document.getElementById('exportPDF')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.location = '{{ route("financial-statements.income-statement.export") }}?' + new URLSearchParams({...Object.fromEntries(new URL(window.location).searchParams), format:'pdf'});
});
</script>
@endpush
