@extends('layouts.app')
@section('title', 'Bank Transactions')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-arrow-left-right me-2 text-info"></i>Bank Transactions</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Bank Transactions</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <a href="{{ route('bank-transactions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Manual Entry
    </a>
    @endrole
</div>

<div class="table-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Bank Account</label>
            <select name="bank_account_id" class="form-select form-select-sm">
                <option value="">All Accounts</option>
                @foreach($bankAccounts as $acct)
                <option value="{{ $acct->id }}" {{ request('bank_account_id')==$acct->id?'selected':'' }}>{{ $acct->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">All Types</option>
                @foreach(['deposit'=>'Deposit','withdrawal'=>'Withdrawal','transfer_in'=>'Transfer In','transfer_out'=>'Transfer Out','revenue'=>'Revenue','expense'=>'Expense'] as $v=>$l)
                <option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Description / Ref">
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button class="btn btn-primary btn-sm flex-fill">Filter</button>
            <a href="{{ route('bank-transactions.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Date</th><th>Account</th><th>Description</th><th>Type</th>
                <th class="text-end">Debit (Out)</th>
                <th class="text-end">Credit (In)</th>
                <th class="text-end">Balance</th>
                <th>Source</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $txn)
            <tr>
                <td>{{ $txn->date->format('M d, Y') }}</td>
                <td><span class="fw-500">{{ $txn->bankAccount?->name }}</span></td>
                <td>
                    <div>{{ $txn->description }}</div>
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
                <td>
                    @if($txn->is_manual)
                    <span class="badge bg-warning bg-opacity-20 text-warning" style="font-size:.7rem;">Manual</span>
                    @else
                    <span class="badge bg-info bg-opacity-20 text-info" style="font-size:.7rem;">Auto</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('bank-transactions.show', $txn) }}" class="btn btn-sm btn-outline-secondary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($txn->is_manual)
                        @role('admin|finance')
                        <form method="POST" action="{{ route('bank-transactions.destroy', $txn) }}" id="del-txn-{{ $txn->id }}">@csrf @method('DELETE')</form>
                        <button class="btn btn-sm btn-outline-danger py-0" data-confirm="Delete this manual transaction?" data-form="#del-txn-{{ $txn->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endrole
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No transactions found.</td></tr>
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
