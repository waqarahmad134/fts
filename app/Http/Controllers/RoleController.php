<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /* ---------------- Display ---------------- */

    public function index()
    {
        // Show roles in hierarchical order
        $roles = Role::with('permissions')->orderBy('level')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    /* ---------------- Create ---------------- */

    public function create()
    {
        $existingRoles = Role::orderBy('level')->get();
        return view('roles.create', compact('existingRoles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles',
            'insert_after' => 'nullable|exists:roles,id'
        ]);

        if ($request->filled('insert_after')) {
            $afterRole = Role::find($request->insert_after);

            // Get the next role after the selected one
            $nextRole = Role::where('level', '>', $afterRole->level)
                ->orderBy('level')
                ->first();

            if ($nextRole) {
                // Insert between afterRole and nextRole
                $newLevel = intval(($afterRole->level + $nextRole->level) / 2);
            } else {
                // No next role; put 100 after
                $newLevel = $afterRole->level + 100;
            }
        } else {
            // Add at the end
            $newLevel = (Role::max('level') ?? 0) + 100;
        }

        Role::create([
            'name' => $request->name,
            'level' => $newLevel,
        ]);

        return redirect()->route('roles.index')->with('toast_success', 'Role created successfully.');
    }


    /* ---------------- Edit / Update ---------------- */

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => ['required', Rule::unique('roles')->ignore($role->id)],
            'level' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('roles')->ignore($role->id),
            ],
        ]);

        $role->update([
            'name' => $request->name,
            // keep current level if none supplied
            'level' => $request->filled('level') ? $request->level : $role->level,
        ]);

        return redirect()
            ->route('roles.index')
            ->with('toast_success', 'Role updated successfully.');
    }

    /* ---------------- Show & Delete ---------------- */

    public function show(Role $role)
    {
        return $role;
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function getPermissions(Role $role)
    {
        $permissions = Permission::all(['id', 'name', 'description']);
        $assigned = $role->permissions->pluck('id')->toArray();

        // Group permissions by module (Files, Users, Profile, File Status)
        $grouped = [];

        // Define the order of modules
        $moduleOrder = ['Files', 'Users', 'Profile', 'File Status'];

        foreach ($permissions as $perm) {
            // Determine module from permission name
            if (str_contains($perm->name, '_files')) {
                $module = 'Files';
            } elseif (str_contains($perm->name, '_users')) {
                $module = 'Users';
            } elseif (str_contains($perm->name, '_profile')) {
                $module = 'Profile';
            } elseif (str_contains($perm->name, '_file_statuses')) {
                $module = 'File Status';
            } else {
                continue; // Skip any other permissions
            }

            // Determine action order (Create, View, Edit, Delete)
            $actionOrder = 0;
            if (str_starts_with($perm->name, 'create_'))
                $actionOrder = 1;
            elseif (str_starts_with($perm->name, 'view_'))
                $actionOrder = 2;
            elseif (str_starts_with($perm->name, 'edit_'))
                $actionOrder = 3;
            elseif (str_starts_with($perm->name, 'delete_'))
                $actionOrder = 4;

            $grouped[$module][] = [
                'id' => $perm->id,
                'name' => $perm->name,
                'description' => $perm->description ?? ucwords(str_replace('_', ' ', $perm->name)),
                'order' => $actionOrder
            ];
        }

        // Sort each module's permissions by action order
        foreach ($grouped as $module => &$perms) {
            usort($perms, function ($a, $b) {
                return $a['order'] <=> $b['order'];
            });
        }

        // Sort modules by defined order
        $sortedGrouped = [];
        foreach ($moduleOrder as $module) {
            if (isset($grouped[$module])) {
                $sortedGrouped[$module] = $grouped[$module];
            }
        }

        return response()->json([
            'permissions' => $sortedGrouped,
            'assigned' => $assigned
        ]);
    }


    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return response()->json(['message' => 'Permissions updated successfully']);
    }

}
