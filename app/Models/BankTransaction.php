<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date', 'bank_account_id', 'transaction_type', 'description',
        'debit', 'credit', 'balance', 'reference_number',
        'transactionable_type', 'transactionable_id',
        'transfer_to_account_id', 'is_manual', 'created_by', 'notes',
    ];

    protected $casts = [
        'date'       => 'date',
        'debit'      => 'decimal:2',
        'credit'     => 'decimal:2',
        'balance'    => 'decimal:2',
        'is_manual'  => 'boolean',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function transferToAccount()
    {
        return $this->belongsTo(BankAccount::class, 'transfer_to_account_id');
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('bank_account_id', $accountId);
    }
}
