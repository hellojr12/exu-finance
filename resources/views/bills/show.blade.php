@extends('layouts.app')
@section('title', 'Bill ' . $bill->bill_number)

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="bi bi-file-earmark-minus me-2 text-danger"></i>{{ $bill->bill_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('bills.index') }}">AP</a></li>
            <li class="breadcrumb-item active">{{ $bill->bill_number }}</li>
        </ol></nav>
    </div>
    @role('admin|finance')
    <div class="d-flex gap-2">
        @if($bill->status !== 'paid')
        <a href="{{ route('bills.edit', $bill) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        <form method="POST" action="{{ route('bills.destroy', $bill) }}" id="del-bill">@csrf @method('DELETE')</form>
        <button class="btn btn-outline-danger btn-sm" data-confirm="Delete this bill?" data-form="#del-bill"><i class="bi bi-trash me-1"></i>Delete</button>
    </div>
    @endrole
</div>
<div class="row g-3">
    <div class="col-md-8">
        <div class="table-card">
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <div class="fw-bold text-danger" style="text-transform:uppercase;letter-spacing:.1em;font-size:.8rem;">Bill / AP</div>
                    <div class="fw-bold fs-4">{{ $bill->bill_number }}</div>
                </div>
                @php $sm=['paid'=>'badge-paid','unpaid'=>'badge-unpaid','partial'=>'badge-partial','overdue'=>'badge-overdue']; @endphp
                <span class="badge {{ $sm[$bill->status] ?? 'bg-secondary' }}" style="font-size:.85rem;padding:.5rem .85rem;">
                    {{ strtoupper($bill->status) }}
                </span>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Supplier</div>
                    <div class="fw-bold">{{ $bill->supplier_name }}</div>
                    @if($bill->supplier_email)<div>{{ $bill->supplier_email }}</div>@endif
                </div>
                <div class="col-md-6 text-md-end">
                    <div><span class="text-muted">Bill Date:</span> <strong>{{ $bill->bill_date->format('F d, Y') }}</strong></div>
                    <div><span class="text-muted">Due Date:</span>
                        <strong class="{{ $bill->is_overdue ? 'text-danger' : '' }}">{{ $bill->due_date->format('F d, Y') }}</strong>
                        @if($bill->is_overdue)<span class="badge badge-overdue ms-1">{{ $bill->days_overdue }}d overdue</span>@endif
                    </div>
                    @if($bill->event_name)<div><span class="text-muted">Event:</span> {{ $bill->event_name }}</div>@endif
                </div>
            </div>
            @if($bill->description)
            <div class="mb-4 p-3 rounded" style="background:#f8fafc;">{{ $bill->description }}</div>
            @endif
            <div class="d-flex flex-column align-items-end gap-1">
                <div class="d-flex gap-4 fw-bold fs-5">
                    <span>Total</span>
                    <span class="num">₱{{ number_format($bill->total_amount, 2) }}</span>
                </div>
                <div class="d-flex gap-4 num-green">
                    <span class="text-muted">Paid</span>
                    <span class="num">₱{{ number_format($bill->amount_paid, 2) }}</span>
                </div>
                <div class="d-flex gap-4 num-red fw-bold">
                    <span>Balance Due</span>
                    <span class="num">₱{{ number_format($bill->balance_due, 2) }}</span>
                </div>
            </div>
            @if($bill->attachment_path)
            <div class="mt-3">
                <a href="{{ asset('storage/'.$bill->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-paperclip me-1"></i>View Attachment
                </a>
            </div>
            @endif
        </div>

        <div class="table-card mt-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="table-card-title">Payment History</div>
                @role('admin|finance')
                @if($bill->balance_due > 0)
                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="bi bi-plus me-1"></i>Record Payment
                </button>
                @endif
                @endrole
            </div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Method</th><th>Account</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                @forelse($bill->payments as $pmt)
                <tr>
                    <td>{{ $pmt->payment_date->format('M d, Y') }}</td>
                    <td>{{ ucwords(str_replace('_',' ',$pmt->payment_method)) }}</td>
                    <td>{{ $pmt->bankAccount?->name ?? '—' }}</td>
                    <td class="text-end num num-red">₱{{ number_format($pmt->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No payments yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card text-center">
            <div class="kpi-value num-red fs-2">₱{{ number_format($bill->balance_due, 2) }}</div>
            <div class="kpi-label">Balance Due</div>
            @php $pct = $bill->total_amount > 0 ? ($bill->amount_paid / $bill->total_amount * 100) : 0; @endphp
            <div class="progress mt-3" style="height:6px;">
                <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
            </div>
            <div class="text-muted mt-1" style="font-size:.75rem;">{{ number_format($pct, 1) }}% paid</div>
        </div>
    </div>
</div>

@role('admin|finance')
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('bills.payments.store', $bill) }}">
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
                                <input type="number" name="amount" class="form-control" value="{{ $bill->balance_due }}" step="0.01" min="0.01" max="{{ $bill->balance_due }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','check'=>'Check','gcash'=>'GCash','maya'=>'Maya'] as $v=>$l)
                                <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bank Account (Deducted From)</label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">None</option>
                                @foreach($bankAccounts as $a)
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
                    <button type="submit" class="btn btn-danger">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endrole
@endsection
