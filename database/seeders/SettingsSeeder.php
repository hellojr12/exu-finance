<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name',            'value' => 'EXU Finance',                   'group' => 'general', 'type' => 'string'],
            ['key' => 'organization_name',   'value' => 'Exponential University',         'group' => 'general', 'type' => 'string'],
            ['key' => 'currency',            'value' => 'PHP',                            'group' => 'general', 'type' => 'string'],
            ['key' => 'currency_symbol',     'value' => '₱',                              'group' => 'general', 'type' => 'string'],
            ['key' => 'fiscal_year_start',   'value' => '01',                             'group' => 'finance', 'type' => 'string'],
            ['key' => 'fiscal_year_end',     'value' => '12',                             'group' => 'finance', 'type' => 'string'],
            ['key' => 'overdue_alert_days',  'value' => '7',                              'group' => 'alerts',  'type' => 'integer'],
            ['key' => 'unusual_expense_multiplier', 'value' => '2',                       'group' => 'alerts',  'type' => 'decimal'],
            ['key' => 'date_format',         'value' => 'M d, Y',                         'group' => 'general', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
