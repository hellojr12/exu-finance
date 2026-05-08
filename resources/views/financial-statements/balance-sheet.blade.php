@extends('layouts.app')
@section('title', 'Balance Sheet')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-clipboard-data me-2 text-success"></i>Balance Sheet</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Balance Sheet</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('financial-statements.balance-sheet.export', array_merge(request()->query(), ['format'=>'xlsx'])) }}"
           class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="{{ route('financial-statements.balance-sheet.export', array_merge(request()->query(), ['format'=>'pdf'])) }}"
           class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
    </div>
</div>

<div class="table-card mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">As of Date</label>
            <input type="date" name="as_of" class="form-control form-control-sm" value="{{ $asOf->format('Y-m-d') }}">
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2">
            <button class="btn btn-primary btn-sm">Apply</button>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" name="compare" value="1" id="compare" {{ $compareMode?'checked':'' }} onchange="this.form.submit()">
                <label class="form-check-label" for="compare" style="font-size:.82rem;">vs Prior Year</label>
            </div>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="text-center mb-4">
        <div class="fw-bold text-success" style="font-size:1.1rem; text-transform:uppercase; letter-spacing:.08em;">Exponential University</div>
        <div class="fw-bold fs-4">Balance Sheet</div>
        <div class="text-muted">As of {{ $asOf->format('F d, Y') }}</div>
    </div>

    <div class="row">
        {{-- ASSETS --}}
        <div class="col-md-6">
            <table class="table">
                <thead>
                <tr>
                    <th>ASSETS</th>
                    <th class="text-end">{{ $asOf->format('M d, Y') }}</th>
                    @if($compareMode && $compareData)
                    <th class="text-end text-muted">{{ $asOf->copy()->subYear()->format('M d, Y') }}</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                <tr class="table-light"><td class="fw-600" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.05em;">Current Assets</td><td></td>@if($compareMode && $compareData)<td></td>@endif</tr>
                <tr><td class="ps-4">Cash & Bank Balances</td>
                    <td class="text-end num">₱{{ number_format($data['cashInBank'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['cashInBank'], 2) }}</td>@endif
                </tr>
                <tr><td class="ps-4">Accounts Receivable</td>
                    <td class="text-end num">₱{{ number_format($data['accountsReceivable'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['accountsReceivable'], 2) }}</td>@endif
                </tr>
                <tr class="fw-bold" style="border-top:1px solid #dee2e6;">
                    <td class="ps-4">Total Current Assets</td>
                    <td class="text-end num">₱{{ number_format($data['totalCurrentAssets'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['totalCurrentAssets'], 2) }}</td>@endif
                </tr>

                <tr><td style="padding:.4rem;"></td><td></td>@if($compareMode && $compareData)<td></td>@endif</tr>

                <tr class="table-light"><td class="fw-600" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.05em;">Non-Current Assets</td><td></td>@if($compareMode && $compareData)<td></td>@endif</tr>
                <tr><td class="ps-4">Staff Loans Receivable</td>
                    <td class="text-end num">₱{{ number_format($data['staffLoansTotal'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['staffLoansTotal'], 2) }}</td>@endif
                </tr>
                <tr class="fw-bold" style="border-top:1px solid #dee2e6;">
                    <td class="ps-4">Total Non-Current Assets</td>
                    <td class="text-end num">₱{{ number_format($data['totalNonCurrentAssets'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['totalNonCurrentAssets'], 2) }}</td>@endif
                </tr>

                <tr style="border-top:3px double #0f172a;"><td></td><td></td>@if($compareMode && $compareData)<td></td>@endif</tr>
                <tr class="fw-bold fs-6">
                    <td>TOTAL ASSETS</td>
                    <td class="text-end num num-green">₱{{ number_format($data['totalAssets'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['totalAssets'], 2) }}</td>@endif
                </tr>
                </tbody>
            </table>
        </div>

        {{-- LIABILITIES & EQUITY --}}
        <div class="col-md-6">
            <table class="table">
                <thead>
                <tr>
                    <th>LIABILITIES & EQUITY</th>
                    <th class="text-end">{{ $asOf->format('M d, Y') }}</th>
                    @if($compareMode && $compareData)<th class="text-end text-muted">{{ $asOf->copy()->subYear()->format('M d, Y') }}</th>@endif
                </tr>
                </thead>
                <tbody>
                <tr class="table-light"><td class="fw-600" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.05em;">Current Liabilities</td><td></td>@if($compareMode && $compareData)<td></td>@endif</tr>
                <tr><td class="ps-4">Accounts Payable</td>
                    <td class="text-end num">₱{{ number_format($data['accountsPayable'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['accountsPayable'], 2) }}</td>@endif
                </tr>
                <tr class="fw-bold" style="border-top:1px solid #dee2e6;">
                    <td class="ps-4">Total Liabilities</td>
                    <td class="text-end num num-red">₱{{ number_format($data['totalLiabilities'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['totalLiabilities'], 2) }}</td>@endif
                </tr>

                <tr><td style="padding:.4rem;"></td><td></td>@if($compareMode && $compareData)<td></td>@endif</tr>

                <tr class="table-light"><td class="fw-600" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.05em;">Equity</td><td></td>@if($compareMode && $compareData)<td></td>@endif</tr>
                <tr><td class="ps-4">Retained Earnings</td>
                    <td class="text-end num {{ $data['retainedEarnings'] >= 0 ? 'num-green' : 'num-red' }}">
                        ₱{{ number_format($data['retainedEarnings'], 2) }}
                    </td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['retainedEarnings'], 2) }}</td>@endif
                </tr>
                <tr class="fw-bold" style="border-top:1px solid #dee2e6;">
                    <td class="ps-4">Total Equity</td>
                    <td class="text-end num">₱{{ number_format($data['totalEquity'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['totalEquity'], 2) }}</td>@endif
                </tr>

                <tr style="border-top:3px double #0f172a;"><td></td><td></td>@if($compareMode && $compareData)<td></td>@endif</tr>
                <tr class="fw-bold fs-6">
                    <td>TOTAL LIABILITIES & EQUITY</td>
                    <td class="text-end num num-green">₱{{ number_format($data['totalLiabilitiesAndEquity'], 2) }}</td>
                    @if($compareMode && $compareData)<td class="text-end num text-muted">₱{{ number_format($compareData['totalLiabilitiesAndEquity'], 2) }}</td>@endif
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if(abs($data['totalAssets'] - $data['totalLiabilitiesAndEquity']) < 0.01)
    <div class="alert alert-success mt-3" style="border-radius:8px;font-size:.85rem;">
        <i class="bi bi-check-circle me-2"></i><strong>Balanced.</strong> Assets = Liabilities + Equity
    </div>
    @else
    <div class="alert alert-warning mt-3" style="border-radius:8px;font-size:.85rem;">
        <i class="bi bi-exclamation-triangle me-2"></i>Balance sheet difference: ₱{{ number_format(abs($data['totalAssets'] - $data['totalLiabilitiesAndEquity']), 2) }}
    </div>
    @endif

    <div class="text-muted text-center mt-3" style="font-size:.75rem;">
        Generated on {{ now()->format('F d, Y h:i A') }} · Exponential University Finance System
    </div>
</div>
@endsection
