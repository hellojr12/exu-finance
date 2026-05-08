<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'partner_name', 'partner_email', 'partner_contact',
        'partner_address', 'invoice_date', 'due_date', 'event_name',
        'event_category_id', 'description', 'subtotal', 'tax_rate',
        'tax_amount', 'total_amount', 'amount_paid', 'balance_due',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'subtotal'     => 'decimal:2',
        'tax_rate'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'balance_due'  => 'decimal:2',
    ];

    public function eventCategory()
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
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
        $prefix = 'INV-' . date('Y') . '-';
        $last   = static::where('invoice_number', 'like', $prefix . '%')
                         ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
