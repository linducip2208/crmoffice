<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            ReferenceDataSeeder::class,
            ProviderSeeder::class,
            OwnerUserSeeder::class,
            DemoUsersSeeder::class,
        ]);

        if (env('APP_DEMO_SEED', false)) {
            $this->call(DemoContentSeeder::class);
            $this->call(MassiveDemoSeeder::class);
        }
    }
}
