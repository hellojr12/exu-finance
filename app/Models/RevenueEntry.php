<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenueEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date', 'reference_number', 'event_name', 'event_category_id',
        'amount', 'payment_method', 'bank_account_id', 'notes',
        'created_by', 'updated_by', 'is_posted',
    ];

    protected $casts = [
        'date'      => 'date',
        'amount'    => 'decimal:2',
        'is_posted' => 'boolean',
    ];

    public function eventCategory()
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function bankTransaction()
    {
        return $this->morphOne(BankTransaction::class, 'transactionable');
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function scopeForYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }
}
