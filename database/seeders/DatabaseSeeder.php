<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        if (APP::environment('dev')) {
            $this->call([
                PermissionSeeder::class,
                RoleSeeder::class,
                UserSeeder::class,
                //TagSeeder::class,
                //PhotoSeeder::class
            ]);
        }
    }
}
