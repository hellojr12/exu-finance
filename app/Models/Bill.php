<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_number', 'supplier_name', 'supplier_email', 'supplier_contact',
        'bill_date', 'due_date', 'event_name', 'expense_category_id',
        'description', 'total_amount', 'amount_paid', 'balance_due',
        'status', 'attachment_path', 'notes', 'created_by',
    ];

    protected $casts = [
        'bill_date'    => 'date',
        'due_date'     => 'date',
        'total_amount' => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'balance_due'  => 'decimal:2',
    ];

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function payments()
    {
        return $this->hasMany(BillPayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date < Carbon::today();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) return 0;
        return $this->due_date->diffInDays(Carbon::today());
    }

    public function updateStatus(): void
    {
        if ($this->amount_paid <= 0) {
            $this->status = $this->due_date < Carbon::today() ? 'overdue' : 'unpaid';
        } elseif ($this->amount_paid >= $this->total_amount) {
            $this->status = 'paid';
        } else {
            $this->status = $this->due_date < Carbon::today() ? 'overdue' : 'partial';
        }
        $this->balance_due = max(0, $this->total_amount - $this->amount_paid);
        $this->save();
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
                     ->where('due_date', '<', Carbon::today());
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial', 'overdue']);
    }

    public static function generateNumber(): string
    {
        $prefix = 'BILL-' . date('Y') . '-';
        $last   = static::where('bill_number', 'like', $prefix . '%')
                         ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->bill_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
