<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('id');

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRoleId,
            'designation' => 'Administrator',
            'wing' => 'Admin Wing',
            'ip_address' => '127.0.0.1',
            'latitude' => null,
            'longitude' => null,
            'device_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
