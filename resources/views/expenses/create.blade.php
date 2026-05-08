@extends('layouts.app')
@section('title', 'Record Expense')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-plus-circle me-2 text-danger"></i>Record Expense</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
        <li class="breadcrumb-item active">Record</li>
    </ol></nav>
</div>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="table-card">
    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Expense Category <span class="text-danger">*</span></label>
                <select name="expense_category_id" class="form-select" required>
                    <option value="">Select category</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('expense_category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}" required
                       placeholder="e.g. Venue rental for AI Hackathon May 2025">
            </div>
            <div class="col-md-6">
                <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="amount" class="form-control" value="{{ old('amount') }}" step="0.01" min="0.01" required placeholder="0.00">
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Vendor / Payee</label>
                <input type="text" name="vendor" class="form-control" value="{{ old('vendor') }}" placeholder="e.g. SMDC Events Center">
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="">Not specified</option>
                    @foreach($paymentMethods as $val=>$lbl)
                    <option value="{{ $val }}" {{ old('payment_method')===$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Bank Account</label>
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
                <label class="form-label">Receipt / Attachment</label>
                <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-text">Max 5MB. Accepted: JPG, PNG, PDF</div>
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-danger px-4"><i class="bi bi-check-lg me-1"></i>Save Expense</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
