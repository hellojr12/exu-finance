@extends('layouts.app')
@section('title', 'Expense Detail')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-receipt-cutoff me-2 text-danger"></i>Expense Detail</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
            <li class="breadcrumb-item active">{{ $expense->reference_number }}</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <div class="d-flex gap-2">
        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" id="del-exp">@csrf @method('DELETE')</form>
        <button class="btn btn-outline-danger btn-sm" data-confirm="Delete this expense?" data-form="#del-exp"><i class="bi bi-trash me-1"></i>Delete</button>
    </div>
    @endrole
</div>
<div class="row g-3">
    <div class="col-md-8">
        <div class="table-card">
            <dl class="row">
                <dt class="col-sm-4 text-muted">Reference #</dt>
                <dd class="col-sm-8"><code>{{ $expense->reference_number }}</code></dd>
                <dt class="col-sm-4 text-muted">Date</dt>
                <dd class="col-sm-8">{{ $expense->date->format('F d, Y') }}</dd>
                <dt class="col-sm-4 text-muted">Category</dt>
                <dd class="col-sm-8">
                    <span class="badge" style="background:{{ $expense->expenseCategory?->color.'20' }};color:{{ $expense->expenseCategory?->color }};">
                        {{ $expense->expenseCategory?->name }}
                    </span>
                </dd>
                <dt class="col-sm-4 text-muted">Description</dt>
                <dd class="col-sm-8">{{ $expense->description }}</dd>
                <dt class="col-sm-4 text-muted">Vendor</dt>
                <dd class="col-sm-8">{{ $expense->vendor ?? '—' }}</dd>
                <dt class="col-sm-4 text-muted">Payment Method</dt>
                <dd class="col-sm-8">{{ ucwords(str_replace('_',' ',$expense->payment_method ?? 'N/A')) }}</dd>
                <dt class="col-sm-4 text-muted">Bank Account</dt>
                <dd class="col-sm-8">{{ $expense->bankAccount?->name ?? '—' }}</dd>
                @if($expense->notes)
                <dt class="col-sm-4 text-muted">Notes</dt>
                <dd class="col-sm-8">{{ $expense->notes }}</dd>
                @endif
                @if($expense->receipt_path)
                <dt class="col-sm-4 text-muted">Receipt</dt>
                <dd class="col-sm-8">
                    <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-paperclip me-1"></i>{{ $expense->receipt_original_name }}
                    </a>
                </dd>
                @endif
            </dl>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-value num-red fs-2">₱{{ number_format($expense->amount, 2) }}</div>
            <div class="kpi-label mt-1">Expense Amount</div>
        </div>
    </div>
</div>
@endsection
