@extends('layouts.app')
@section('title', 'Bank Accounts')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-bank me-2 text-success"></i>Bank Accounts</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Bank Accounts</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <a href="{{ route('bank-accounts.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Add Account
    </a>
    @endrole
</div>

<div class="row g-3 mb-4">
    @foreach($accounts as $acct)
    <div class="col-md-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="kpi-icon" style="background:#f0fdf4;">
                    <i class="bi bi-bank" style="color:#10b981;"></i>
                </div>
                <span class="badge {{ $acct->is_active ? 'bg-success' : 'bg-secondary' }}" style="font-size:.7rem;">
                    {{ $acct->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="fw-600 mb-1">{{ $acct->name }}</div>
            <div class="text-muted" style="font-size:.8rem;">{{ $acct->bank_name }} · {{ ucfirst($acct->type) }}</div>
            @if($acct->account_number)
            <div class="text-muted" style="font-size:.75rem;">•••• {{ substr($acct->account_number, -4) }}</div>
            @endif
            <div class="kpi-value mt-2 {{ $acct->current_balance >= 0 ? 'num-green' : 'num-red' }}">
                ₱{{ number_format($acct->current_balance, 2) }}
            </div>
            <div class="kpi-label">Current Balance</div>
            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('bank-accounts.show', $acct) }}" class="btn btn-sm btn-outline-success flex-fill">
                    <i class="bi bi-list-ul me-1"></i>Ledger
                </a>
                @role('admin|finance')
                <a href="{{ route('bank-accounts.edit', $acct) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i>
                </a>
                @endrole
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="table-card">
    <div class="table-card-title mb-3">Account Summary</div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Account Name</th><th>Bank</th><th>Type</th>
                <th class="text-end">Opening Balance</th>
                <th class="text-end">Current Balance</th>
                <th>Transactions</th><th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($accounts as $acct)
            <tr>
                <td class="fw-500">
                    <a href="{{ route('bank-accounts.show', $acct) }}" class="text-decoration-none">{{ $acct->name }}</a>
                </td>
                <td>{{ $acct->bank_name }}</td>
                <td><span class="badge bg-light text-dark">{{ ucfirst($acct->type) }}</span></td>
                <td class="text-end num">₱{{ number_format($acct->opening_balance, 2) }}</td>
                <td class="text-end num fw-600 {{ $acct->current_balance >= 0 ? 'num-green' : 'num-red' }}">
                    ₱{{ number_format($acct->current_balance, 2) }}
                </td>
                <td>{{ number_format($acct->transactions_count) }}</td>
                <td>
                    <span class="badge {{ $acct->is_active ? 'badge-active' : '' }}">
                        {{ $acct->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">Total</td>
                <td class="text-end num">₱{{ number_format($accounts->sum('opening_balance'), 2) }}</td>
                <td class="text-end num num-green">₱{{ number_format($accounts->sum('current_balance'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
