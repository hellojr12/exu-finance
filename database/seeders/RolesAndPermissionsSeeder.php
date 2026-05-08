<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Revenue
            'revenue.view', 'revenue.create', 'revenue.edit', 'revenue.delete',
            // Expenses
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            // Bank
            'bank.view', 'bank.create', 'bank.edit',
            // AR
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.payment',
            // AP
            'bills.view', 'bills.create', 'bills.edit', 'bills.delete', 'bills.payment',
            // Staff Loans
            'loans.view', 'loans.create', 'loans.edit', 'loans.deduction',
            // Reports
            'reports.view', 'reports.export',
            // Settings
            'settings.view', 'settings.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Admin — full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Finance — all except settings.manage and user management
        $finance = Role::firstOrCreate(['name' => 'finance']);
        $finance->givePermissionTo([
            'revenue.view', 'revenue.create', 'revenue.edit', 'revenue.delete',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'bank.view', 'bank.create', 'bank.edit',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.payment',
            'bills.view', 'bills.create', 'bills.edit', 'bills.delete', 'bills.payment',
            'loans.view', 'loans.create', 'loans.edit', 'loans.deduction',
            'reports.view', 'reports.export',
        ]);

        // CEO/COO — view only
        $ceo = Role::firstOrCreate(['name' => 'ceo']);
        $ceo->givePermissionTo([
            'revenue.view', 'expenses.view', 'bank.view',
            'invoices.view', 'bills.view', 'loans.view',
            'reports.view', 'reports.export',
        ]);

        Role::firstOrCreate(['name' => 'coo'])->syncPermissions($ceo->permissions);

        // External Viewer / Auditor — view reports only
        $external = Role::firstOrCreate(['name' => 'external_viewer']);
        $external->givePermissionTo(['reports.view']);

        $auditor = Role::firstOrCreate(['name' => 'auditor']);
        $auditor->givePermissionTo(['reports.view', 'reports.export']);
    }
}
