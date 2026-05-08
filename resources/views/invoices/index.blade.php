@extends('layouts.app')
@section('title', 'Accounts Receivable')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-file-earmark-text me-2 text-primary"></i>Accounts Receivable</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Accounts Receivable</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Create Invoice
    </a>
    @endrole
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value num-green">₱{{ number_format($summary['total_ar'], 2) }}</div>
        <div class="kpi-label">Total Outstanding AR</div>
    </div></div>
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value num-red">₱{{ number_format($summary['overdue'], 2) }}</div>
        <div class="kpi-label">Overdue Amount</div>
    </div></div>
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value num-green">₱{{ number_format($summary['paid_month'], 2) }}</div>
        <div class="kpi-label">Collected This Month</div>
    </div></div>
</div>

<div class="table-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                @foreach(['unpaid'=>'Unpaid','partial'=>'Partial','paid'=>'Paid','overdue'=>'Overdue','cancelled'=>'Cancelled'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
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
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Partner / Invoice # / Event" value="{{ request('search') }}">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-primary btn-sm flex-fill">Filter</button>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Invoice #</th><th>Partner</th><th>Event</th>
                <th>Invoice Date</th><th>Due Date</th>
                <th class="text-end">Total</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Balance</th>
                <th>Status</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($invoices as $inv)
            <tr class="{{ $inv->is_overdue ? 'table-danger bg-opacity-25' : '' }}">
                <td><code style="font-size:.78rem;">{{ $inv->invoice_number }}</code></td>
                <td class="fw-500">{{ $inv->partner_name }}</td>
                <td>{{ Str::limit($inv->event_name, 25) ?? '—' }}</td>
                <td>{{ $inv->invoice_date->format('M d, Y') }}</td>
                <td class="{{ $inv->is_overdue ? 'text-danger fw-600' : '' }}">
                    {{ $inv->due_date->format('M d, Y') }}
                    @if($inv->is_overdue)
                    <span class="badge badge-overdue ms-1">{{ $inv->days_overdue }}d</span>
                    @endif
                </td>
                <td class="text-end num">₱{{ number_format($inv->total_amount, 2) }}</td>
                <td class="text-end num num-green">₱{{ number_format($inv->amount_paid, 2) }}</td>
                <td class="text-end num fw-600 num-red">₱{{ number_format($inv->balance_due, 2) }}</td>
                <td>
                    @php
                        $statusMap = ['paid'=>'badge-paid','unpaid'=>'badge-unpaid','partial'=>'badge-partial','overdue'=>'badge-overdue','cancelled'=>'badge-cancelled'];
                    @endphp
                    <span class="badge {{ $statusMap[$inv->status] ?? 'bg-secondary' }}" style="font-size:.72rem;">
                        {{ ucfirst($inv->status) }}
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-eye"></i></a>
                        @role('admin|finance')
                        @if($inv->status !== 'paid')
                        <a href="{{ route('invoices.edit', $inv) }}" class="btn btn-sm btn-outline-primary py-0"><i class="bi bi-pencil"></i></a>
                        @endif
                        <form method="POST" action="{{ route('invoices.destroy', $inv) }}" id="del-inv-{{ $inv->id }}">@csrf @method('DELETE')</form>
                        <button class="btn btn-sm btn-outline-danger py-0" data-confirm="Delete invoice {{ $inv->invoice_number }}?" data-form="#del-inv-{{ $inv->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endrole
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center text-muted py-4">No invoices found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted small">Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}</div>
        {{ $invoices->links() }}
    </div>
</div>
@endsection
