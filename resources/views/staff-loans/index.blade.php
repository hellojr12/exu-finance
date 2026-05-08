@extends('layouts.app')
@section('title', 'Staff Loans')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-person-badge me-2 text-warning"></i>Staff Loans</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Staff Loans</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <a href="{{ route('staff-loans.create') }}" class="btn btn-warning">
        <i class="bi bi-plus-lg me-1"></i>Issue Loan
    </a>
    @endrole
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value" style="color:#f59e0b;">₱{{ number_format($summary['total_outstanding'], 2) }}</div>
        <div class="kpi-label">Total Outstanding Balance</div>
    </div></div>
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value">{{ $summary['total_loans'] }}</div>
        <div class="kpi-label">Total Loans Issued</div>
    </div></div>
    <div class="col-md-4"><div class="kpi-card text-center">
        <div class="kpi-value" style="color:#10b981;">{{ $summary['active_count'] }}</div>
        <div class="kpi-label">Active Loans</div>
    </div></div>
</div>

<div class="table-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach(['active'=>'Active','fully_paid'=>'Fully Paid','written_off'=>'Written Off'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Employee name / ID" value="{{ request('search') }}">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-primary btn-sm flex-fill">Filter</button>
            <a href="{{ route('staff-loans.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Employee</th><th>Department</th><th>Date Issued</th>
                <th class="text-end">Loan Amount</th>
                <th class="text-end">Total Deducted</th>
                <th class="text-end">Outstanding</th>
                <th>Deduction</th><th>Status</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($loans as $loan)
            <tr>
                <td>
                    <div class="fw-600">{{ $loan->employee_name }}</div>
                    @if($loan->employee_id)<div class="text-muted" style="font-size:.75rem;">{{ $loan->employee_id }}</div>@endif
                </td>
                <td>{{ $loan->department ?? '—' }}</td>
                <td>{{ $loan->date_issued->format('M d, Y') }}</td>
                <td class="text-end num">₱{{ number_format($loan->loan_amount, 2) }}</td>
                <td class="text-end num num-green">₱{{ number_format($loan->total_deducted, 2) }}</td>
                <td class="text-end num fw-600" style="color:#f59e0b;">₱{{ number_format($loan->outstanding_balance, 2) }}</td>
                <td>
                    <div style="font-size:.8rem;">{{ ucfirst($loan->deduction_type) }}</div>
                    @if($loan->monthly_deduction > 0)
                    <div class="text-muted" style="font-size:.72rem;">₱{{ number_format($loan->monthly_deduction, 2) }}/mo</div>
                    @endif
                </td>
                <td>
                    @php $sc=['active'=>'badge-active','fully_paid'=>'badge-paid','written_off'=>'badge-cancelled']; @endphp
                    <span class="badge {{ $sc[$loan->status] ?? 'bg-secondary' }}" style="font-size:.72rem;">
                        {{ ucwords(str_replace('_',' ',$loan->status)) }}
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('staff-loans.show', $loan) }}" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-eye"></i></a>
                        @role('admin|finance')
                        <a href="{{ route('staff-loans.edit', $loan) }}" class="btn btn-sm btn-outline-warning py-0"><i class="bi bi-pencil"></i></a>
                        @endrole
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No staff loans found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted small">Showing {{ $loans->firstItem() }}–{{ $loans->lastItem() }} of {{ $loans->total() }}</div>
        {{ $loans->links() }}
    </div>
</div>
@endsection
