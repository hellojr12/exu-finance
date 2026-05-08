@extends('layouts.app')
@section('title', 'Edit Bill')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-pencil me-2 text-danger"></i>Edit Bill {{ $bill->bill_number }}</h4>
</div>
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="table-card">
    <form method="POST" action="{{ route('bills.update', $bill) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                <input type="text" name="supplier_name" class="form-control" value="{{ old('supplier_name', $bill->supplier_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Supplier Email</label>
                <input type="email" name="supplier_email" class="form-control" value="{{ old('supplier_email', $bill->supplier_email) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Expense Category</label>
                <select name="expense_category_id" class="form-select">
                    <option value="">None</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $bill->expense_category_id==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Event Name</label>
                <input type="text" name="event_name" class="form-control" value="{{ old('event_name', $bill->event_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', $bill->bill_date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $bill->due_date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Total Amount (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="total_amount" class="form-control" value="{{ old('total_amount', $bill->total_amount) }}" step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $bill->description) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $bill->notes) }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-danger px-4"><i class="bi bi-check-lg me-1"></i>Update</button>
            <a href="{{ route('bills.show', $bill) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
