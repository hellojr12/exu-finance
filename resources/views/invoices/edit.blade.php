@extends('layouts.app')
@section('title', 'Edit Invoice')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-pencil me-2 text-primary"></i>Edit Invoice {{ $invoice->invoice_number }}</h4>
</div>
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="table-card">
    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Partner Name <span class="text-danger">*</span></label>
                <input type="text" name="partner_name" class="form-control" value="{{ old('partner_name', $invoice->partner_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Partner Email</label>
                <input type="email" name="partner_email" class="form-control" value="{{ old('partner_email', $invoice->partner_email) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Event Category</label>
                <select name="event_category_id" class="form-select">
                    <option value="">None</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $invoice->event_category_id==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Event Name</label>
                <input type="text" name="event_name" class="form-control" value="{{ old('event_name', $invoice->event_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Subtotal (₱)</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="subtotal" id="subtotal" class="form-control" value="{{ old('subtotal', $invoice->subtotal) }}" step="0.01" oninput="calcTotal()">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tax Rate (%)</label>
                <div class="input-group">
                    <input type="number" name="tax_rate" id="taxRate" class="form-control" value="{{ old('tax_rate', $invoice->tax_rate) }}" step="0.01" oninput="calcTotal()">
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Total Amount (₱)</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="total_amount" id="totalAmount" class="form-control fw-bold"
                           value="{{ old('total_amount', $invoice->total_amount) }}" step="0.01" required readonly style="background:#f8fafc;">
                </div>
                <input type="hidden" name="tax_amount" id="taxAmount" value="{{ $invoice->tax_amount }}">
            </div>
            <div class="col-12">
                <label class="form-label">Description / Notes</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $invoice->description) }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update Invoice</button>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function calcTotal() {
    const sub = parseFloat(document.getElementById('subtotal').value) || 0;
    const rate = parseFloat(document.getElementById('taxRate').value) || 0;
    const tax = sub * rate / 100;
    document.getElementById('taxAmount').value = tax.toFixed(2);
    document.getElementById('totalAmount').value = (sub + tax).toFixed(2);
}
</script>
@endpush
