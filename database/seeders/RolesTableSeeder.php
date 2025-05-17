<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'Admin',
            'Junior Clerk',
            'Assistant Registrar',
            'Deputy Registrar',
            'Additional Registrar',
            'DG',
            'Registrar',
            'Admin Junior',
            'HCJ'
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
