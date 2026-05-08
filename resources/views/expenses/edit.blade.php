@extends('layouts.app')
@section('title', 'Edit Expense')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-pencil me-2 text-danger"></i>Edit Expense</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
</div>
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="table-card">
    <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Expense Category <span class="text-danger">*</span></label>
                <select name="expense_category_id" class="form-select" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $expense->expense_category_id==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $expense->description) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Vendor / Payee</label>
                <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $expense->vendor) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="">Not specified</option>
                    @foreach($paymentMethods as $val=>$lbl)
                    <option value="{{ $val }}" {{ $expense->payment_method===$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Bank Account</label>
                <select name="bank_account_id" class="form-select">
                    <option value="">None</option>
                    @foreach($bankAccounts as $acct)
                    <option value="{{ $acct->id }}" {{ $expense->bank_account_id==$acct->id?'selected':'' }}>
                        {{ $acct->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Replace Receipt</label>
                @if($expense->receipt_path)
                <div class="mb-2">
                    <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-paperclip me-1"></i>View Current Receipt ({{ $expense->receipt_original_name }})
                    </a>
                </div>
                @endif
                <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $expense->notes) }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-danger px-4"><i class="bi bi-check-lg me-1"></i>Update</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
