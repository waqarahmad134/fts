<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Junior Clerk', 'level' => 100],
            ['name' => 'Assistant Registrar', 'level' => 200],
            ['name' => 'Deputy Registrar', 'level' => 300],
            ['name' => 'Additional Registrar', 'level' => 400],
            ['name' => 'DG', 'level' => 500],
            ['name' => 'Registrar', 'level' => 600],
            ['name' => 'Admin Junior', 'level' => 700],
            ['name' => 'Admin', 'level' => 800],
            ['name' => 'HCJ', 'level' => 900],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'level' => $role['level'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
