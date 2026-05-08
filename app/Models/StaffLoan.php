<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_name', 'employee_id', 'department', 'position',
        'loan_amount', 'date_issued', 'outstanding_balance',
        'deduction_type', 'monthly_deduction', 'status',
        'purpose', 'bank_account_id', 'created_by',
    ];

    protected $casts = [
        'date_issued'         => 'date',
        'loan_amount'         => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'monthly_deduction'   => 'decimal:2',
    ];

    public function deductions()
    {
        return $this->hasMany(LoanDeduction::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalDeductedAttribute(): float
    {
        return (float) $this->deductions()->sum('amount');
    }

    public function recalculateBalance(): void
    {
        $this->outstanding_balance = max(0, $this->loan_amount - $this->total_deducted);
        if ($this->outstanding_balance <= 0) {
            $this->status = 'fully_paid';
        }
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
