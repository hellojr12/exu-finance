@extends('layouts.app')
@section('title', 'Edit Bank Account')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-pencil me-2 text-success"></i>Edit Bank Account</h4>
</div>
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="table-card">
    <form method="POST" action="{{ route('bank-accounts.update', $bankAccount) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Account Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $bankAccount->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $bankAccount->bank_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Account Number</label>
                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $bankAccount->account_number) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Account Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    @foreach($types as $v=>$l)
                    <option value="{{ $v }}" {{ $bankAccount->type===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ $bankAccount->is_active?'selected':'' }}>Active</option>
                    <option value="0" {{ !$bankAccount->is_active?'selected':'' }}>Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $bankAccount->notes) }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Update</button>
            <a href="{{ route('bank-accounts.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
