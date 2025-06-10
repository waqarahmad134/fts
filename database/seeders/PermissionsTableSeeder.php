<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        // Define route name prefixes or keywords you want to exclude
        $excludedPrefixes = [
            'debugbar', 'ignition', 'telescope', 'sanctum', 'livewire', 'horizon'
        ];

        // Define URI substrings you want to exclude (optional)
        $excludedUris = [
            'telescope', '_ignition', 'sanctum', 'livewire', 'horizon'
        ];

        $routes = collect(Route::getRoutes())->filter(function ($route) use ($excludedPrefixes, $excludedUris) {
            $name = $route->getName();
            $uri = $route->uri();

            // Exclude unnamed routes
            if (!$name) return false;

            // Exclude routes based on prefix
            foreach ($excludedPrefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    return false;
                }
            }

            // Exclude routes based on URI pattern
            foreach ($excludedUris as $pattern) {
                if (str_contains($uri, $pattern)) {
                    return false;
                }
            }

            return true;
        });

        $inserted = 0;

        foreach ($routes as $route) {
            $name = $route->getName(); // e.g., roles.create
            $segments = explode('.', $name);

            if (count($segments) < 2) continue;

            $resource = $segments[0];      // e.g., roles
            $action = $segments[1];        // e.g., create
            $permissionName = "{$action}_{$resource}"; // e.g., create_roles

            DB::table('permissions')->updateOrInsert(
                ['name' => $permissionName],
                [
                    'description' => ucwords(str_replace('_', ' ', $permissionName)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $inserted++;
        }

        $this->command->info("Seeded {$inserted} dynamic permissions from routes.");
    }
}
