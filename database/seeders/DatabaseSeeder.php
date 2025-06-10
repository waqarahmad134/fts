<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesTableSeeder::class,
            AdminUserSeeder::class,
            UsersSeeder::class,
            WingsSeeder::class,
            PermissionsTableSeeder::class,
        ]);
    }

}
