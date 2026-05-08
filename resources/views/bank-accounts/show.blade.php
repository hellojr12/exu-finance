@extends('layouts.app')
@section('title', $bankAccount->name . ' — Ledger')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-bank me-2 text-success"></i>{{ $bankAccount->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('bank-accounts.index') }}">Bank Accounts</a></li>
            <li class="breadcrumb-item active">Ledger</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <a href="{{ route('bank-transactions.create') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-plus me-1"></i>Record Transaction
    </a>
    @endrole
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="kpi-card text-center">
        <div class="kpi-value num-green">₱{{ number_format($bankAccount->current_balance, 2) }}</div>
        <div class="kpi-label">Current Balance</div>
    </div></div>
    <div class="col-md-3"><div class="kpi-card text-center">
        <div class="kpi-value">₱{{ number_format($bankAccount->opening_balance, 2) }}</div>
        <div class="kpi-label">Opening Balance</div>
    </div></div>
    <div class="col-md-3"><div class="kpi-card text-center">
        <div class="kpi-value num-green">₱{{ number_format($totalCredits, 2) }}</div>
        <div class="kpi-label">Total Credits (In)</div>
    </div></div>
    <div class="col-md-3"><div class="kpi-card text-center">
        <div class="kpi-value num-red">₱{{ number_format($totalDebits, 2) }}</div>
        <div class="kpi-label">Total Debits (Out)</div>
    </div></div>
</div>

<div class="table-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-primary btn-sm flex-fill">Filter</button>
            <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Date</th><th>Description</th><th>Type</th>
                <th class="text-end">Debit (Out)</th>
                <th class="text-end">Credit (In)</th>
                <th class="text-end">Balance</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $txn)
            <tr>
                <td>{{ $txn->date->format('M d, Y') }}</td>
                <td>
                    <div class="fw-500">{{ $txn->description }}</div>
                    @if($txn->reference_number)
                    <div class="text-muted" style="font-size:.72rem;"><code>{{ $txn->reference_number }}</code></div>
                    @endif
                </td>
                <td><span class="badge bg-light text-dark" style="font-size:.7rem;">
                    {{ ucwords(str_replace('_',' ',$txn->transaction_type)) }}
                </span></td>
                <td class="text-end num num-red">{{ $txn->debit > 0 ? '₱'.number_format($txn->debit,2) : '—' }}</td>
                <td class="text-end num num-green">{{ $txn->credit > 0 ? '₱'.number_format($txn->credit,2) : '—' }}</td>
                <td class="text-end num fw-600">₱{{ number_format($txn->balance, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No transactions found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted small">Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }}</div>
        {{ $transactions->links() }}
    </div>
</div>
@endsection
