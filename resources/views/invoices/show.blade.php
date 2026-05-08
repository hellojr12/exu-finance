@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ $invoice->invoice_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">AR</a></li>
            <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <div class="d-flex gap-2">
        @if($invoice->status !== 'paid')
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" id="del-inv">@csrf @method('DELETE')</form>
        <button class="btn btn-outline-danger btn-sm" data-confirm="Delete this invoice?" data-form="#del-inv"><i class="bi bi-trash me-1"></i>Delete</button>
    </div>
    @endrole
</div>

<div class="row g-3">
    {{-- Invoice card --}}
    <div class="col-md-8">
        <div class="table-card">
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <div class="fw-bold text-primary" style="text-transform:uppercase;letter-spacing:.1em;font-size:.8rem;">Invoice</div>
                    <div class="fw-bold fs-4">{{ $invoice->invoice_number }}</div>
                </div>
                @php
                    $sm = ['paid'=>'badge-paid','unpaid'=>'badge-unpaid','partial'=>'badge-partial','overdue'=>'badge-overdue','cancelled'=>'badge-cancelled'];
                @endphp
                <span class="badge {{ $sm[$invoice->status] ?? 'bg-secondary' }}" style="font-size:.85rem; padding:.5rem .85rem;">
                    {{ strtoupper($invoice->status) }}
                </span>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="text-muted" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.06em; font-weight:600;">Bill To</div>
                    <div class="fw-bold">{{ $invoice->partner_name }}</div>
                    @if($invoice->partner_email)<div>{{ $invoice->partner_email }}</div>@endif
                    @if($invoice->partner_contact)<div>{{ $invoice->partner_contact }}</div>@endif
                    @if($invoice->partner_address)<div class="text-muted small">{{ $invoice->partner_address }}</div>@endif
                </div>
                <div class="col-md-6 text-md-end">
                    <div><span class="text-muted">Invoice Date:</span> <strong>{{ $invoice->invoice_date->format('F d, Y') }}</strong></div>
                    <div><span class="text-muted">Due Date:</span>
                        <strong class="{{ $invoice->is_overdue ? 'text-danger' : '' }}">{{ $invoice->due_date->format('F d, Y') }}</strong>
                        @if($invoice->is_overdue)<span class="badge badge-overdue ms-1">{{ $invoice->days_overdue }}d overdue</span>@endif
                    </div>
                    @if($invoice->event_name)
                    <div><span class="text-muted">Event:</span> {{ $invoice->event_name }}</div>
                    @endif
                </div>
            </div>

            @if($invoice->description)
            <div class="mb-4 p-3 rounded" style="background:#f8fafc;">{{ $invoice->description }}</div>
            @endif

            {{-- Amount breakdown --}}
            <div class="d-flex flex-column align-items-end gap-1">
                <div class="d-flex gap-4">
                    <span class="text-muted">Subtotal</span>
                    <span class="num">₱{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->tax_rate > 0)
                <div class="d-flex gap-4">
                    <span class="text-muted">Tax ({{ $invoice->tax_rate }}%)</span>
                    <span class="num">₱{{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="d-flex gap-4 fw-bold fs-5 pt-2" style="border-top:2px solid #e2e8f0;">
                    <span>Total</span>
                    <span class="num">₱{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                <div class="d-flex gap-4 num-green">
                    <span class="text-muted">Paid</span>
                    <span class="num">₱{{ number_format($invoice->amount_paid, 2) }}</span>
                </div>
                <div class="d-flex gap-4 num-red fw-bold">
                    <span>Balance Due</span>
                    <span class="num">₱{{ number_format($invoice->balance_due, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Payments --}}
        <div class="table-card mt-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="table-card-title">Payment History</div>
                @role('admin|finance')
                @if($invoice->balance_due > 0)
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="bi bi-plus me-1"></i>Record Payment
                </button>
                @endif
                @endrole
            </div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                @forelse($invoice->payments as $pmt)
                <tr>
                    <td>{{ $pmt->payment_date->format('M d, Y') }}</td>
                    <td>{{ ucwords(str_replace('_',' ',$pmt->payment_method)) }}</td>
                    <td><code style="font-size:.75rem;">{{ $pmt->reference_number ?? '—' }}</code></td>
                    <td class="text-end num num-green">₱{{ number_format($pmt->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No payments recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-value num-red fs-2">₱{{ number_format($invoice->balance_due, 2) }}</div>
            <div class="kpi-label">Balance Due</div>
            <div class="progress mt-3" style="height:6px;">
                @php $pct = $invoice->total_amount > 0 ? ($invoice->amount_paid / $invoice->total_amount * 100) : 0; @endphp
                <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
            </div>
            <div class="text-muted mt-1" style="font-size:.75rem;">{{ number_format($pct, 1) }}% collected</div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
@role('admin|finance')
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="amount" class="form-control"
                                       value="{{ $invoice->balance_due }}" step="0.01"
                                       min="0.01" max="{{ $invoice->balance_due }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','paymongo'=>'PayMongo','gcash'=>'GCash','maya'=>'Maya','check'=>'Check'] as $v=>$l)
                                <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">None</option>
                                @foreach(\App\Models\BankAccount::active()->get() as $a)
                                <option value="{{ $a->id }}">{{ $a->name }} — ₱{{ number_format($a->current_balance,2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reference #</label>
                            <input type="text" name="reference_number" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endrole
@endsection
