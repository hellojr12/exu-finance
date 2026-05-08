@extends('layouts.app')
@section('title', 'Create Invoice')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-plus-circle me-2 text-primary"></i>Create Invoice</h4>
</div>
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="table-card">
    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        <input type="hidden" name="invoice_number" value="{{ $number }}">

        <div class="row g-3">
            {{-- Header --}}
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center p-3 rounded mb-2" style="background:#f8fafc;">
                    <div>
                        <div class="fw-bold text-primary" style="font-size:.85rem; text-transform:uppercase; letter-spacing:.06em;">Invoice</div>
                        <div class="fw-bold fs-5">{{ $number }}</div>
                    </div>
                </div>
            </div>

            {{-- Partner Details --}}
            <div class="col-md-6">
                <label class="form-label">Partner / Client Name <span class="text-danger">*</span></label>
                <input type="text" name="partner_name" class="form-control" value="{{ old('partner_name') }}" required
                       placeholder="e.g. Accenture Philippines">
            </div>
            <div class="col-md-6">
                <label class="form-label">Partner Email</label>
                <input type="email" name="partner_email" class="form-control" value="{{ old('partner_email') }}" placeholder="billing@partner.com">
            </div>
            <div class="col-md-6">
                <label class="form-label">Partner Contact</label>
                <input type="text" name="partner_contact" class="form-control" value="{{ old('partner_contact') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Event Category</label>
                <select name="event_category_id" class="form-select">
                    <option value="">None</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('event_category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Event Name</label>
                <input type="text" name="event_name" class="form-control" value="{{ old('event_name') }}" placeholder="e.g. AI Hackathon 2025 — Sponsorship">
            </div>

            {{-- Dates --}}
            <div class="col-md-6">
                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required>
            </div>

            {{-- Amounts --}}
            <div class="col-md-4">
                <label class="form-label">Subtotal (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="subtotal" id="subtotal" class="form-control" value="{{ old('subtotal') }}"
                           step="0.01" min="0" placeholder="0.00" oninput="calcTotal()">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tax Rate (%)</label>
                <div class="input-group">
                    <input type="number" name="tax_rate" id="taxRate" class="form-control" value="{{ old('tax_rate', 0) }}"
                           step="0.01" min="0" max="100" oninput="calcTotal()">
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Total Amount (₱)</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="total_amount" id="totalAmount" class="form-control fw-bold"
                           value="{{ old('total_amount') }}" step="0.01" min="0.01" required readonly
                           style="background:#f8fafc;">
                </div>
                <input type="hidden" name="tax_amount" id="taxAmount">
            </div>

            <div class="col-12">
                <label class="form-label">Description / Notes</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Partner Address</label>
                <textarea name="partner_address" class="form-control" rows="2">{{ old('partner_address') }}</textarea>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Create Invoice</button>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
