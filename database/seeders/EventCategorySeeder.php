<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Scale with AI',  'color' => '#3B82F6', 'description' => 'AI scaling events and workshops'],
            ['name' => 'AI Hackathon',   'color' => '#8B5CF6', 'description' => 'AI hackathon competitions'],
            ['name' => 'Corporate Training', 'color' => '#10B981', 'description' => 'Corporate training programs'],
            ['name' => 'Bootcamp',       'color' => '#F59E0B', 'description' => 'Intensive bootcamp programs'],
            ['name' => 'Summit',         'color' => '#EF4444', 'description' => 'Industry summits and conferences'],
            ['name' => 'Webinar',        'color' => '#06B6D4', 'description' => 'Online webinars and virtual events'],
            ['name' => 'Sponsorship',    'color' => '#84CC16', 'description' => 'Sponsorship revenues'],
            ['name' => 'Membership',     'color' => '#F97316', 'description' => 'Membership fees and subscriptions'],
            ['name' => 'Others',         'color' => '#6B7280', 'description' => 'Miscellaneous revenues'],
        ];

        foreach ($categories as $cat) {
            EventCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, ['slug' => Str::slug($cat['name'])])
            );
        }
    }
}
