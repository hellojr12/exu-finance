@extends('layouts.app')
@section('title', 'Manual Bank Entry')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-plus-circle me-2 text-info"></i>Manual Bank Entry</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('bank-transactions.index') }}">Bank Transactions</a></li>
        <li class="breadcrumb-item active">Manual Entry</li>
    </ol></nav>
</div>
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="table-card">
    <form method="POST" action="{{ route('bank-transactions.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                <select name="bank_account_id" class="form-select" required id="fromAccount">
                    <option value="">Select account</option>
                    @foreach($bankAccounts as $acct)
                    <option value="{{ $acct->id }}" {{ old('bank_account_id')==$acct->id?'selected':'' }}
                            data-balance="{{ $acct->current_balance }}">
                        {{ $acct->name }} — ₱{{ number_format($acct->current_balance, 2) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                <select name="transaction_type" class="form-select" required id="txnType">
                    <option value="">Select type</option>
                    @foreach($types as $v=>$l)
                    <option value="{{ $v }}" {{ old('transaction_type')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="amount" class="form-control" value="{{ old('amount') }}" step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="col-12" id="transferToField" style="display:none;">
                <label class="form-label">Transfer To Account <span class="text-danger">*</span></label>
                <select name="transfer_to_account_id" class="form-select">
                    <option value="">Select destination</option>
                    @foreach($bankAccounts as $acct)
                    <option value="{{ $acct->id }}">{{ $acct->name }} — ₱{{ number_format($acct->current_balance, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}" required
                       placeholder="Description of this transaction">
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Save Transaction</button>
            <a href="{{ route('bank-transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('txnType')?.addEventListener('change', function () {
    document.getElementById('transferToField').style.display =
        this.value === 'transfer_out' ? '' : 'none';
});
</script>
@endpush
