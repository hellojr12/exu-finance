@extends('layouts.app')
@section('title', 'Edit Revenue')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-pencil me-2 text-primary"></i>Edit Revenue</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('revenue.index') }}">Revenue</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
</div>
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="table-card">
    <form method="POST" action="{{ route('revenue.update', $revenue) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ old('date', $revenue->date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Event Category <span class="text-danger">*</span></label>
                <select name="event_category_id" class="form-select" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $revenue->event_category_id==$cat->id ? 'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Event Name <span class="text-danger">*</span></label>
                <input type="text" name="event_name" class="form-control" value="{{ old('event_name', $revenue->event_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="amount" class="form-control" value="{{ old('amount', $revenue->amount) }}" step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-select" required>
                    @foreach($paymentMethods as $val => $lbl)
                    <option value="{{ $val }}" {{ $revenue->payment_method===$val ? 'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Bank Account</label>
                <select name="bank_account_id" class="form-select">
                    <option value="">None</option>
                    @foreach($bankAccounts as $acct)
                    <option value="{{ $acct->id }}" {{ $revenue->bank_account_id==$acct->id ? 'selected':'' }}>
                        {{ $acct->name }} — ₱{{ number_format($acct->current_balance, 2) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $revenue->notes) }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update</button>
            <a href="{{ route('revenue.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
