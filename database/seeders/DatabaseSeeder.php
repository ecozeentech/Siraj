<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            QuranSeeder::class,
            AdhkarSeeder::class,
            PlanSeeder::class,
            LocationSeeder::class,
            CategorySeeder::class,
            StarterContentSeeder::class,
        ]);

        if (app()->environment(['local', 'testing']) || env('SIRAJ_SEED_DEMO')) {
            $this->call(DemoSeeder::class);
        }
    }
}
