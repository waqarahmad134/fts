<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wing;

class WingsSeeder extends Seeder
{
    public function run(): void
    {
        $wings = [
            ['name' => 'Finance', 'description' => 'Handles financial matters'],
            ['name' => 'Legal', 'description' => 'Handles legal affairs'],
            ['name' => 'Administration', 'description' => 'Administrative responsibilities'],
            ['name' => 'IT', 'description' => 'Manages information technology'],
        ];

        foreach ($wings as $wing) {
            Wing::firstOrCreate(['name' => $wing['name']], $wing);
        }
    }
}
