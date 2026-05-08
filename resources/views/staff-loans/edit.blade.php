@extends('layouts.app')
@section('title', 'Edit Staff Loan')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-pencil me-2 text-warning"></i>Edit Staff Loan</h4>
</div>
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="table-card">
    <form method="POST" action="{{ route('staff-loans.update', $staffLoan) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                <input type="text" name="employee_name" class="form-control" value="{{ old('employee_name', $staffLoan->employee_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Employee ID</label>
                <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id', $staffLoan->employee_id) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="{{ old('department', $staffLoan->department) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Position</label>
                <input type="text" name="position" class="form-control" value="{{ old('position', $staffLoan->position) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Deduction Type <span class="text-danger">*</span></label>
                <select name="deduction_type" class="form-select" required>
                    @foreach(['monthly'=>'Monthly','manual'=>'Manual','one_time'=>'One-Time'] as $v=>$l)
                    <option value="{{ $v }}" {{ $staffLoan->deduction_type===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Monthly Deduction (₱)</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="monthly_deduction" class="form-control" value="{{ old('monthly_deduction', $staffLoan->monthly_deduction) }}" step="0.01" min="0">
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    @foreach(['active'=>'Active','fully_paid'=>'Fully Paid','written_off'=>'Written Off'] as $v=>$l)
                    <option value="{{ $v }}" {{ $staffLoan->status===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Purpose</label>
                <textarea name="purpose" class="form-control" rows="3">{{ old('purpose', $staffLoan->purpose) }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check-lg me-1"></i>Update</button>
            <a href="{{ route('staff-loans.show', $staffLoan) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
