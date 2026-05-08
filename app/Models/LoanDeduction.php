<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_loan_id', 'deduction_date', 'amount',
        'deduction_type', 'reference_number', 'notes', 'created_by',
    ];

    protected $casts = [
        'deduction_date' => 'date',
        'amount'         => 'decimal:2',
    ];

    public function staffLoan()
    {
        return $this->belongsTo(StaffLoan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
