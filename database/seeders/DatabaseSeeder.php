<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            EventCategorySeeder::class,
            ExpenseCategorySeeder::class,
            BankAccountSeeder::class,
            SettingsSeeder::class,
            UserSeeder::class,
        ]);
    }
}
