<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@crmoffice.local'],
            [
                'name' => 'Owner',
                'password' => Hash::make('password'),
                'is_active' => true,
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'email_verified_at' => now(),
            ]
        );

        if (! $owner->hasRole('owner')) {
            $owner->assignRole('owner');
        }
    }
}
