@extends('layouts.app')
@section('title', 'Revenue')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Revenue</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Revenue</li>
        </ol></nav>
    </div>
    @hasanyrole('admin|finance')
    <a href="{{ route('revenue.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Record Revenue
    </a>
    @endhasanyrole
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-value text-primary">₱{{ number_format($total, 2) }}</div>
            <div class="kpi-label">Total Revenue (Filtered)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-value">{{ $revenues->total() }}</div>
            <div class="kpi-label">Total Entries</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-value">₱{{ $revenues->total() > 0 ? number_format($total / $revenues->total(), 2) : '0.00' }}</div>
            <div class="kpi-label">Average per Entry</div>
        </div>
    </div>
</div>

{{-- Filters --}}
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
            <label class="form-label">Event Category</label>
            <select name="event_category_id" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('event_category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select form-select-sm">
                <option value="">All Methods</option>
                @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','paymongo'=>'PayMongo','gcash'=>'GCash','maya'=>'Maya','check'=>'Check'] as $v=>$l)
                <option value="{{ $v }}" {{ request('payment_method')===$v ? 'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Event name / Ref #" value="{{ request('search') }}">
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button class="btn btn-primary btn-sm flex-fill">Filter</button>
            <a href="{{ route('revenue.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x"></i>
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Event Name</th>
                <th>Category</th>
                <th>Payment</th>
                <th>Bank Account</th>
                <th class="text-end">Amount</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($revenues as $rev)
            <tr>
                <td>{{ $rev->date->format('M d, Y') }}</td>
                <td><code style="font-size:.75rem;">{{ $rev->reference_number }}</code></td>
                <td class="fw-500">{{ $rev->event_name }}</td>
                <td>
                    <span class="badge" style="background:{{ $rev->eventCategory?->color.'20' }};color:{{ $rev->eventCategory?->color }};">
                        {{ $rev->eventCategory?->name ?? '—' }}
                    </span>
                </td>
                <td><span class="badge bg-light text-dark" style="font-size:.72rem;">{{ ucwords(str_replace('_',' ',$rev->payment_method)) }}</span></td>
                <td>{{ $rev->bankAccount?->name ?? '—' }}</td>
                <td class="text-end num fw-600 num-green">₱{{ number_format($rev->amount, 2) }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('revenue.show', $rev) }}" class="btn btn-sm btn-outline-secondary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        @role('admin|finance')
                        <a href="{{ route('revenue.edit', $rev) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('revenue.destroy', $rev) }}" id="del-rev-{{ $rev->id }}">
                            @csrf @method('DELETE')
                        </form>
                        <button class="btn btn-sm btn-outline-danger py-0"
                                data-confirm="Delete this revenue entry?"
                                data-form="#del-rev-{{ $rev->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endrole
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No revenue entries found.</td></tr>
            @endforelse
            </tbody>
            @if($revenues->count() > 0)
            <tfoot>
            <tr class="table-light fw-bold">
                <td colspan="6" class="text-end">Total</td>
                <td class="text-end num-green">₱{{ number_format($total, 2) }}</td>
                <td></td>
            </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted small">Showing {{ $revenues->firstItem() }}–{{ $revenues->lastItem() }} of {{ $revenues->total() }} entries</div>
        {{ $revenues->links() }}
    </div>
</div>
@endsection
