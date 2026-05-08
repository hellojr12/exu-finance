@extends('layouts.app')
@section('title', 'Add Bank Account')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-plus-circle me-2 text-success"></i>Add Bank Account</h4>
</div>
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="table-card">
    <form method="POST" action="{{ route('bank-accounts.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Account Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Metrobank Main">
            </div>
            <div class="col-md-6">
                <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" required placeholder="e.g. Metrobank">
            </div>
            <div class="col-md-6">
                <label class="form-label">Account Number</label>
                <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" placeholder="Optional">
            </div>
            <div class="col-md-6">
                <label class="form-label">Account Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    @foreach($types as $v=>$l)
                    <option value="{{ $v }}" {{ old('type')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Opening Balance (₱)</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="opening_balance" class="form-control" value="{{ old('opening_balance', 0) }}" step="0.01" min="0">
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Save Account</button>
            <a href="{{ route('bank-accounts.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
