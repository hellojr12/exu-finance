<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Venue',                 'color' => '#EF4444', 'description' => 'Venue rental and related costs'],
            ['name' => 'Food & Beverage',        'color' => '#F97316', 'description' => 'Catering and meals'],
            ['name' => 'Speakers & Talents',     'color' => '#F59E0B', 'description' => 'Speaker fees and talent costs'],
            ['name' => 'Marketing & Promotions', 'color' => '#84CC16', 'description' => 'Digital and print marketing'],
            ['name' => 'Equipment & Technology', 'color' => '#10B981', 'description' => 'AV equipment and tech rentals'],
            ['name' => 'Transportation',         'color' => '#06B6D4', 'description' => 'Travel and transport costs'],
            ['name' => 'Accommodation',          'color' => '#3B82F6', 'description' => 'Hotel and accommodation'],
            ['name' => 'Staff Salaries',         'color' => '#8B5CF6', 'description' => 'Staff compensation and benefits'],
            ['name' => 'Office Supplies',        'color' => '#EC4899', 'description' => 'Office and event supplies'],
            ['name' => 'Professional Services',  'color' => '#14B8A6', 'description' => 'Legal, accounting, consulting'],
            ['name' => 'Software & Subscriptions','color' => '#6366F1', 'description' => 'SaaS and software costs'],
            ['name' => 'Utilities',              'color' => '#A78BFA', 'description' => 'Electricity, internet, water'],
            ['name' => 'Bank Charges',           'color' => '#78716C', 'description' => 'Bank fees and charges'],
            ['name' => 'Miscellaneous',          'color' => '#6B7280', 'description' => 'Other expenses not categorized'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, ['slug' => Str::slug($cat['name'])])
            );
        }
    }
}
