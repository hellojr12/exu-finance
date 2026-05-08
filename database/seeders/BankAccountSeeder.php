<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name'            => 'PayMongo Sales',
                'account_number'  => 'PM-SALES-001',
                'bank_name'       => 'PayMongo',
                'type'            => 'ewallet',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
            ],
            [
                'name'            => 'PayMongo Expense',
                'account_number'  => 'PM-EXP-001',
                'bank_name'       => 'PayMongo',
                'type'            => 'ewallet',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
            ],
            [
                'name'            => 'GoTyme Business',
                'account_number'  => 'GT-001',
                'bank_name'       => 'GoTyme',
                'type'            => 'checking',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
            ],
            [
                'name'            => 'Metrobank Main',
                'account_number'  => 'MB-001',
                'bank_name'       => 'Metrobank',
                'type'            => 'checking',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::firstOrCreate(
                ['name' => $account['name']],
                $account
            );
        }
    }
}
