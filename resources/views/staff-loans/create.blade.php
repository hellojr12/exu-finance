@extends('layouts.app')
@section('title', 'Issue Staff Loan')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-plus-circle me-2 text-warning"></i>Issue Staff Loan</h4>
</div>
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="table-card">
    <form method="POST" action="{{ route('staff-loans.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                <input type="text" name="employee_name" class="form-control" value="{{ old('employee_name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Employee ID</label>
                <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="{{ old('department') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Position</label>
                <input type="text" name="position" class="form-control" value="{{ old('position') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Loan Amount (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="loan_amount" class="form-control" value="{{ old('loan_amount') }}" step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date Issued <span class="text-danger">*</span></label>
                <input type="date" name="date_issued" class="form-control" value="{{ old('date_issued', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Deduction Type <span class="text-danger">*</span></label>
                <select name="deduction_type" class="form-select" required id="deductionType">
                    @foreach(['monthly'=>'Monthly Deduction','manual'=>'Manual Deduction','one_time'=>'One-Time'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('deduction_type')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6" id="monthlyDeductionField">
                <label class="form-label">Monthly Deduction Amount (₱)</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="monthly_deduction" class="form-control" value="{{ old('monthly_deduction', 0) }}" step="0.01" min="0">
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Bank Account (Disbursed From)</label>
                <select name="bank_account_id" class="form-select">
                    <option value="">None</option>
                    @foreach($bankAccounts as $acct)
                    <option value="{{ $acct->id }}" {{ old('bank_account_id')==$acct->id?'selected':'' }}>
                        {{ $acct->name }} — ₱{{ number_format($acct->current_balance, 2) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Purpose</label>
                <textarea name="purpose" class="form-control" rows="3">{{ old('purpose') }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check-lg me-1"></i>Issue Loan</button>
            <a href="{{ route('staff-loans.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('deductionType')?.addEventListener('change', function () {
    document.getElementById('monthlyDeductionField').style.display =
        this.value === 'monthly' ? '' : 'none';
});
</script>
@endpush
