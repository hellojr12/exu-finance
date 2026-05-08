@extends('layouts.app')
@section('title', 'Accounts Payable')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-file-earmark-minus me-2 text-danger"></i>Accounts Payable</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Accounts Payable</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <a href="{{ route('bills.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Create Bill
    </a>
    @endrole
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value num-red">₱{{ number_format($summary['total_ap'], 2) }}</div>
        <div class="kpi-label">Total Outstanding AP</div>
    </div></div>
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value num-red">₱{{ number_format($summary['overdue'], 2) }}</div>
        <div class="kpi-label">Overdue Amount</div>
    </div></div>
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value num-green">₱{{ number_format($summary['paid_month'], 2) }}</div>
        <div class="kpi-label">Paid This Month</div>
    </div></div>
</div>

<div class="table-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach(['unpaid'=>'Unpaid','partial'=>'Partial','paid'=>'Paid','overdue'=>'Overdue'] as $v=>$l)
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
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Supplier / Bill # / Event" value="{{ request('search') }}">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-primary btn-sm flex-fill">Filter</button>
            <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Bill #</th><th>Supplier</th><th>Event</th>
                <th>Bill Date</th><th>Due Date</th>
                <th class="text-end">Total</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Balance</th>
                <th>Status</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($bills as $bill)
            <tr class="{{ $bill->is_overdue ? 'table-danger bg-opacity-25' : '' }}">
                <td><code style="font-size:.78rem;">{{ $bill->bill_number }}</code></td>
                <td class="fw-500">{{ $bill->supplier_name }}</td>
                <td>{{ Str::limit($bill->event_name, 25) ?? '—' }}</td>
                <td>{{ $bill->bill_date->format('M d, Y') }}</td>
                <td class="{{ $bill->is_overdue ? 'text-danger fw-600' : '' }}">
                    {{ $bill->due_date->format('M d, Y') }}
                    @if($bill->is_overdue)<span class="badge badge-overdue ms-1">{{ $bill->days_overdue }}d</span>@endif
                </td>
                <td class="text-end num">₱{{ number_format($bill->total_amount, 2) }}</td>
                <td class="text-end num num-green">₱{{ number_format($bill->amount_paid, 2) }}</td>
                <td class="text-end num fw-600 num-red">₱{{ number_format($bill->balance_due, 2) }}</td>
                <td>
                    @php $sm = ['paid'=>'badge-paid','unpaid'=>'badge-unpaid','partial'=>'badge-partial','overdue'=>'badge-overdue']; @endphp
                    <span class="badge {{ $sm[$bill->status] ?? 'bg-secondary' }}" style="font-size:.72rem;">{{ ucfirst($bill->status) }}</span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-eye"></i></a>
                        @role('admin|finance')
                        @if($bill->status !== 'paid')
                        <a href="{{ route('bills.edit', $bill) }}" class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-pencil"></i></a>
                        @endif
                        <form method="POST" action="{{ route('bills.destroy', $bill) }}" id="del-bill-{{ $bill->id }}">@csrf @method('DELETE')</form>
                        <button class="btn btn-sm btn-outline-danger py-0" data-confirm="Delete bill {{ $bill->bill_number }}?" data-form="#del-bill-{{ $bill->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endrole
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center text-muted py-4">No bills found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted small">Showing {{ $bills->firstItem() }}–{{ $bills->lastItem() }} of {{ $bills->total() }}</div>
        {{ $bills->links() }}
    </div>
</div>
@endsection
