@extends('layouts.app')
@section('title', 'Revenue Detail')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Revenue Detail</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('revenue.index') }}">Revenue</a></li>
            <li class="breadcrumb-item active">{{ $revenue->reference_number }}</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <div class="d-flex gap-2">
        <a href="{{ route('revenue.edit', $revenue) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form method="POST" action="{{ route('revenue.destroy', $revenue) }}" id="del-rev">
            @csrf @method('DELETE')
        </form>
        <button class="btn btn-outline-danger btn-sm" data-confirm="Delete this revenue entry?" data-form="#del-rev">
            <i class="bi bi-trash me-1"></i>Delete
        </button>
    </div>
    @endrole
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="table-card">
            <h6 class="fw-bold mb-4 text-muted text-uppercase" style="font-size:.75rem; letter-spacing:.06em;">
                Revenue Information
            </h6>
            <dl class="row">
                <dt class="col-sm-4 text-muted" style="font-size:.85rem;">Reference #</dt>
                <dd class="col-sm-8"><code>{{ $revenue->reference_number }}</code></dd>

                <dt class="col-sm-4 text-muted" style="font-size:.85rem;">Date</dt>
                <dd class="col-sm-8">{{ $revenue->date->format('F d, Y') }}</dd>

                <dt class="col-sm-4 text-muted" style="font-size:.85rem;">Event Name</dt>
                <dd class="col-sm-8 fw-600">{{ $revenue->event_name }}</dd>

                <dt class="col-sm-4 text-muted" style="font-size:.85rem;">Category</dt>
                <dd class="col-sm-8">
                    <span class="badge" style="background:{{ $revenue->eventCategory?->color.'20' }};color:{{ $revenue->eventCategory?->color }};">
                        {{ $revenue->eventCategory?->name ?? '—' }}
                    </span>
                </dd>

                <dt class="col-sm-4 text-muted" style="font-size:.85rem;">Payment Method</dt>
                <dd class="col-sm-8">{{ ucwords(str_replace('_', ' ', $revenue->payment_method)) }}</dd>

                <dt class="col-sm-4 text-muted" style="font-size:.85rem;">Bank Account</dt>
                <dd class="col-sm-8">{{ $revenue->bankAccount?->name ?? '—' }}</dd>

                @if($revenue->notes)
                <dt class="col-sm-4 text-muted" style="font-size:.85rem;">Notes</dt>
                <dd class="col-sm-8">{{ $revenue->notes }}</dd>
                @endif

                <dt class="col-sm-4 text-muted" style="font-size:.85rem;">Created by</dt>
                <dd class="col-sm-8">{{ $revenue->creator?->name ?? '—' }} on {{ $revenue->created_at->format('M d, Y h:i A') }}</dd>
            </dl>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-icon mx-auto mb-2" style="background:#eff6ff;width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                <i class="bi bi-cash-coin" style="color:#3b82f6;"></i>
            </div>
            <div class="kpi-value num-green fs-2">₱{{ number_format($revenue->amount, 2) }}</div>
            <div class="kpi-label mt-1">Revenue Amount</div>
        </div>
        @if($revenue->bankTransaction)
        <div class="table-card mt-3">
            <h6 class="fw-bold text-muted" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.06em;">
                Bank Transaction
            </h6>
            <div class="mt-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Account</span>
                    <span class="small fw-500">{{ $revenue->bankTransaction->bankAccount?->name }}</span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted small">Credit</span>
                    <span class="small fw-600 num-green">₱{{ number_format($revenue->bankTransaction->credit, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted small">Running Balance</span>
                    <span class="small fw-500">₱{{ number_format($revenue->bankTransaction->balance, 2) }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
