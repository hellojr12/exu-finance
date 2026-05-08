@extends('layouts.app')
@section('title', 'Transaction Detail')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-arrow-left-right me-2 text-info"></i>Transaction Detail</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('bank-transactions.index') }}">Bank Transactions</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol></nav>
    </div>
    @if($bankTransaction->is_manual)
    @role('admin|finance')
    <form method="POST" action="{{ route('bank-transactions.destroy', $bankTransaction) }}" id="del-txn">@csrf @method('DELETE')</form>
    <button class="btn btn-outline-danger btn-sm" data-confirm="Delete this transaction? This will reverse the account balance." data-form="#del-txn">
        <i class="bi bi-trash me-1"></i>Delete
    </button>
    @endrole
    @endif
</div>
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="table-card">
    <dl class="row">
        <dt class="col-sm-4 text-muted">Date</dt>
        <dd class="col-sm-8">{{ $bankTransaction->date->format('F d, Y') }}</dd>
        <dt class="col-sm-4 text-muted">Account</dt>
        <dd class="col-sm-8 fw-600">{{ $bankTransaction->bankAccount?->name }}</dd>
        <dt class="col-sm-4 text-muted">Type</dt>
        <dd class="col-sm-8"><span class="badge bg-light text-dark">{{ ucwords(str_replace('_',' ',$bankTransaction->transaction_type)) }}</span></dd>
        <dt class="col-sm-4 text-muted">Description</dt>
        <dd class="col-sm-8">{{ $bankTransaction->description }}</dd>
        @if($bankTransaction->reference_number)
        <dt class="col-sm-4 text-muted">Reference #</dt>
        <dd class="col-sm-8"><code>{{ $bankTransaction->reference_number }}</code></dd>
        @endif
        @if($bankTransaction->debit > 0)
        <dt class="col-sm-4 text-muted">Debit (Out)</dt>
        <dd class="col-sm-8 num-red fw-600 fs-5">₱{{ number_format($bankTransaction->debit, 2) }}</dd>
        @endif
        @if($bankTransaction->credit > 0)
        <dt class="col-sm-4 text-muted">Credit (In)</dt>
        <dd class="col-sm-8 num-green fw-600 fs-5">₱{{ number_format($bankTransaction->credit, 2) }}</dd>
        @endif
        <dt class="col-sm-4 text-muted">Running Balance</dt>
        <dd class="col-sm-8 fw-600">₱{{ number_format($bankTransaction->balance, 2) }}</dd>
        <dt class="col-sm-4 text-muted">Source</dt>
        <dd class="col-sm-8">
            @if($bankTransaction->is_manual)
            <span class="badge bg-warning bg-opacity-20 text-warning">Manual Entry</span>
            @else
            <span class="badge bg-info bg-opacity-20 text-info">Auto-generated</span>
            @endif
        </dd>
        <dt class="col-sm-4 text-muted">Created by</dt>
        <dd class="col-sm-8">{{ $bankTransaction->creator?->name }} on {{ $bankTransaction->created_at->format('M d, Y h:i A') }}</dd>
        @if($bankTransaction->notes)
        <dt class="col-sm-4 text-muted">Notes</dt>
        <dd class="col-sm-8">{{ $bankTransaction->notes }}</dd>
        @endif
    </dl>
</div>
</div>
</div>
@endsection
