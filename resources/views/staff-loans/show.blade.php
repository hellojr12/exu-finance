@extends('layouts.app')
@section('title', 'Staff Loan — ' . $staffLoan->employee_name)

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-person-badge me-2 text-warning"></i>{{ $staffLoan->employee_name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('staff-loans.index') }}">Staff Loans</a></li>
            <li class="breadcrumb-item active">{{ $staffLoan->employee_name }}</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <a href="{{ route('staff-loans.edit', $staffLoan) }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    @endrole
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="table-card">
            <dl class="row">
                <dt class="col-sm-4 text-muted">Employee</dt>
                <dd class="col-sm-8 fw-600">{{ $staffLoan->employee_name }}</dd>
                @if($staffLoan->employee_id)
                <dt class="col-sm-4 text-muted">Employee ID</dt>
                <dd class="col-sm-8">{{ $staffLoan->employee_id }}</dd>
                @endif
                @if($staffLoan->department)
                <dt class="col-sm-4 text-muted">Department</dt>
                <dd class="col-sm-8">{{ $staffLoan->department }}</dd>
                @endif
                <dt class="col-sm-4 text-muted">Date Issued</dt>
                <dd class="col-sm-8">{{ $staffLoan->date_issued->format('F d, Y') }}</dd>
                <dt class="col-sm-4 text-muted">Deduction Type</dt>
                <dd class="col-sm-8">{{ ucwords(str_replace('_',' ',$staffLoan->deduction_type)) }}</dd>
                @if($staffLoan->monthly_deduction > 0)
                <dt class="col-sm-4 text-muted">Monthly Deduction</dt>
                <dd class="col-sm-8">₱{{ number_format($staffLoan->monthly_deduction, 2) }}</dd>
                @endif
                <dt class="col-sm-4 text-muted">Disbursed From</dt>
                <dd class="col-sm-8">{{ $staffLoan->bankAccount?->name ?? '—' }}</dd>
                @if($staffLoan->purpose)
                <dt class="col-sm-4 text-muted">Purpose</dt>
                <dd class="col-sm-8">{{ $staffLoan->purpose }}</dd>
                @endif
            </dl>
        </div>

        <div class="table-card mt-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="table-card-title">Deduction History</div>
                @role('admin|finance')
                @if($staffLoan->outstanding_balance > 0 && $staffLoan->status === 'active')
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#deductionModal">
                    <i class="bi bi-plus me-1"></i>Record Deduction
                </button>
                @endif
                @endrole
            </div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                @forelse($staffLoan->deductions as $ded)
                <tr>
                    <td>{{ $ded->deduction_date->format('M d, Y') }}</td>
                    <td>{{ ucfirst($ded->deduction_type) }}</td>
                    <td><code style="font-size:.75rem;">{{ $ded->reference_number ?? '—' }}</code></td>
                    <td class="text-end num num-green">₱{{ number_format($ded->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No deductions yet.</td></tr>
                @endforelse
                </tbody>
                @if($staffLoan->deductions->count() > 0)
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Total Deducted</td>
                        <td class="text-end num-green">₱{{ number_format($staffLoan->total_deducted, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-value fs-3">₱{{ number_format($staffLoan->loan_amount, 2) }}</div>
            <div class="kpi-label">Loan Amount</div>
        </div>
        <div class="kpi-card text-center mt-3">
            <div class="kpi-value num-green">₱{{ number_format($staffLoan->total_deducted, 2) }}</div>
            <div class="kpi-label">Total Deducted</div>
        </div>
        <div class="kpi-card text-center mt-3">
            <div class="kpi-value" style="color:#f59e0b;">₱{{ number_format($staffLoan->outstanding_balance, 2) }}</div>
            <div class="kpi-label">Outstanding Balance</div>
            @php $pct = $staffLoan->loan_amount > 0 ? ($staffLoan->total_deducted / $staffLoan->loan_amount * 100) : 0; @endphp
            <div class="progress mt-3" style="height:6px;">
                <div class="progress-bar" style="width:{{ $pct }}%;background:#f59e0b;"></div>
            </div>
            <div class="text-muted mt-1" style="font-size:.75rem;">{{ number_format($pct, 1) }}% repaid</div>
        </div>
        <div class="table-card mt-3 text-center">
            @php $sc=['active'=>'badge-active','fully_paid'=>'badge-paid','written_off'=>'badge-cancelled']; @endphp
            <span class="badge {{ $sc[$staffLoan->status] ?? 'bg-secondary' }}" style="font-size:.9rem;padding:.5rem .85rem;">
                {{ strtoupper(str_replace('_',' ',$staffLoan->status)) }}
            </span>
        </div>
    </div>
</div>

@role('admin|finance')
<div class="modal fade" id="deductionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Record Deduction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('staff-loans.deductions.store', $staffLoan) }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Deduction Date <span class="text-danger">*</span></label>
                            <input type="date" name="deduction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="amount" class="form-control"
                                       value="{{ $staffLoan->monthly_deduction > 0 ? $staffLoan->monthly_deduction : $staffLoan->outstanding_balance }}"
                                       step="0.01" min="0.01" max="{{ $staffLoan->outstanding_balance }}" required>
                            </div>
                            <div class="form-text">Max: ₱{{ number_format($staffLoan->outstanding_balance, 2) }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="deduction_type" class="form-select" required>
                                <option value="monthly">Monthly</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reference #</label>
                            <input type="text" name="reference_number" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Record Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endrole
@endsection
