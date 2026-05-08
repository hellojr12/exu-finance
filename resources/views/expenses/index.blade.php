@extends('layouts.app')
@section('title', 'Expenses')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-receipt-cutoff me-2 text-danger"></i>Expenses / Disbursements</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Expenses</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <a href="{{ route('expenses.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Record Expense
    </a>
    @endrole
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value num-red">₱{{ number_format($total, 2) }}</div>
        <div class="kpi-label">Total (Filtered)</div>
    </div></div>
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value">{{ $expenses->total() }}</div>
        <div class="kpi-label">Total Entries</div>
    </div></div>
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value">₱{{ $expenses->total()>0 ? number_format($total/$expenses->total(),2):'0.00' }}</div>
        <div class="kpi-label">Average per Entry</div>
    </div></div>
</div>

<div class="table-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="expense_category_id" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('expense_category_id')==$cat->id ? 'selected':'' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Description / Vendor" value="{{ request('search') }}">
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button class="btn btn-primary btn-sm flex-fill">Filter</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Date</th><th>Reference</th><th>Category</th><th>Description</th>
                <th>Vendor</th><th>Payment</th><th>Receipt</th>
                <th class="text-end">Amount</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($expenses as $exp)
            <tr>
                <td>{{ $exp->date->format('M d, Y') }}</td>
                <td><code style="font-size:.75rem;">{{ $exp->reference_number }}</code></td>
                <td>
                    <span class="badge" style="background:{{ $exp->expenseCategory?->color.'20' }};color:{{ $exp->expenseCategory?->color }};">
                        {{ $exp->expenseCategory?->name ?? '—' }}
                    </span>
                </td>
                <td>{{ Str::limit($exp->description, 40) }}</td>
                <td>{{ $exp->vendor ?? '—' }}</td>
                <td><span class="badge bg-light text-dark" style="font-size:.72rem;">{{ ucwords(str_replace('_',' ',$exp->payment_method ?? 'N/A')) }}</span></td>
                <td>
                    @if($exp->receipt_path)
                    <a href="{{ asset('storage/'.$exp->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0">
                        <i class="bi bi-paperclip"></i>
                    </a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-end num fw-600 num-red">₱{{ number_format($exp->amount, 2) }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('expenses.show', $exp) }}" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-eye"></i></a>
                        @role('admin|finance')
                        <a href="{{ route('expenses.edit', $exp) }}" class="btn btn-sm btn-outline-primary py-0"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('expenses.destroy', $exp) }}" id="del-exp-{{ $exp->id }}">@csrf @method('DELETE')</form>
                        <button class="btn btn-sm btn-outline-danger py-0" data-confirm="Delete this expense?" data-form="#del-exp-{{ $exp->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endrole
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No expense entries found.</td></tr>
            @endforelse
            </tbody>
            @if($expenses->count()>0)
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="7" class="text-end">Total</td>
                    <td class="text-end num-red">₱{{ number_format($total, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted small">Showing {{ $expenses->firstItem() }}–{{ $expenses->lastItem() }} of {{ $expenses->total() }}</div>
        {{ $expenses->links() }}
    </div>
</div>
@endsection
