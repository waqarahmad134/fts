<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Junior Clerk',
            'Assistant Registrar',
            'Deputy Registrar',
            'Additional Registrar',
            'DG',
            'Registrar',
            'HCJ',
        ];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if (!$role) {
                $this->command->warn("Role '{$roleName}' not found. Skipping user creation.");
                continue;
            }

            $email = strtolower(str_replace(' ', '', $roleName)) . '@gmail.com';

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $roleName . ' User',
                    'password' => Hash::make('password'),
                    'role_id'  => $role->id,
                ]
            );
        }
    }
}
