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
        $roles = Role::with('permissions')->paginate(10);
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
            'name'         => 'required|unique:roles',
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
            'name'  => $request->name,
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
            'name'  => ['required', Rule::unique('roles')->ignore($role->id)],
            'level' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('roles')->ignore($role->id),
            ],
        ]);

        $role->update([
            'name'  => $request->name,
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
        $permissions = Permission::all(['id', 'name']);
        $assigned = $role->permissions->pluck('id')->toArray();

        $grouped = [];

        foreach ($permissions as $perm) {
            $parts = explode('_', $perm->name);
            $groupKey = isset($parts[1]) ? $parts[1] : 'misc'; // use 2nd word as group
            $grouped[$groupKey][] = [
                'id' => $perm->id,
                'name' => $perm->name
            ];
        }

        return response()->json([
            'permissions' => $grouped,
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
