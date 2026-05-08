<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function revenueEntries()
    {
        return $this->hasMany(RevenueEntry::class, 'created_by');
    }

    public function expenseEntries()
    {
        return $this->hasMany(ExpenseEntry::class, 'created_by');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('admin');
    }

    public function getIsFinanceAttribute(): bool
    {
        return $this->hasRole('finance');
    }

    public function getIsViewOnlyAttribute(): bool
    {
        return $this->hasAnyRole(['ceo', 'coo', 'external_viewer', 'auditor']);
    }
}
