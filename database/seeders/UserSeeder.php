<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'EXU Admin',
                'email'    => 'admin@exponentialuniversity.ph',
                'password' => Hash::make('Admin@EXU2024!'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Finance Officer',
                'email'    => 'finance@exponentialuniversity.ph',
                'password' => Hash::make('Finance@EXU2024!'),
                'role'     => 'finance',
            ],
            [
                'name'     => 'CEO',
                'email'    => 'ceo@exponentialuniversity.ph',
                'password' => Hash::make('CEO@EXU2024!'),
                'role'     => 'ceo',
            ],
            [
                'name'     => 'External Auditor',
                'email'    => 'auditor@exponentialuniversity.ph',
                'password' => Hash::make('Auditor@EXU2024!'),
                'role'     => 'auditor',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
    }
}
