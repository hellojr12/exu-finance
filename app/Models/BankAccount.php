<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'account_number', 'bank_name', 'type',
        'opening_balance', 'current_balance', 'currency', 'is_active', 'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function revenueEntries()
    {
        return $this->hasMany(RevenueEntry::class);
    }

    public function expenseEntries()
    {
        return $this->hasMany(ExpenseEntry::class);
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function billPayments()
    {
        return $this->hasMany(BillPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function recalculateBalance(): void
    {
        $totalCredits = $this->transactions()->sum('credit');
        $totalDebits  = $this->transactions()->sum('debit');
        $this->current_balance = $this->opening_balance + $totalCredits - $totalDebits;
        $this->save();
    }
}
