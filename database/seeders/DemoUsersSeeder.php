<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['email' => 'admin@crmoffice.local',      'name' => 'Demo Admin',       'role' => 'admin'],
            ['email' => 'sales@crmoffice.local',      'name' => 'Demo Sales',       'role' => 'sales'],
            ['email' => 'pm@crmoffice.local',         'name' => 'Demo PM',          'role' => 'pm'],
            ['email' => 'support@crmoffice.local',    'name' => 'Demo Support',     'role' => 'support'],
            ['email' => 'accountant@crmoffice.local', 'name' => 'Demo Accountant',  'role' => 'accountant'],
            ['email' => 'staff@crmoffice.local',      'name' => 'Demo Staff',       'role' => 'staff'],
        ];

        foreach ($accounts as $acc) {
            $user = User::firstOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'locale' => 'id',
                    'timezone' => 'Asia/Jakarta',
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($acc['role'])) {
                $user->assignRole($acc['role']);
            }
        }
    }
}
