<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'supervisor'])->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $supervisors = User::all();
        return view('users.create', compact('roles', 'supervisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:6',
            'role_id'       => 'nullable|exists:roles,id',
            'designation'   => 'nullable|string|max:255',
            'wing'          => 'nullable|string|max:255',
            'supervisor_id' => 'nullable|exists:users,id',
            'ip_address'    => 'nullable|ip',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'device_token'  => 'nullable|string|max:255',
        ]);

        try {
            $validated['password'] = Hash::make($validated['password']);
            User::create($validated);

            return redirect('/users')->with('toast_success', 'User created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('toast_error', 'Error creating user: ' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $supervisors = User::where('id', '!=', $id)->get();
        return view('users.edit', compact('user', 'roles', 'supervisors'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'password'     => 'nullable|min:6|confirmed',
            'role_id'      => 'nullable|exists:roles,id',
            'designation'  => 'nullable|string|max:255',
            'wing'         => 'nullable|string|max:255',
            'supervisor_id'=> 'nullable|exists:users,id',
            'ip_address'   => 'nullable|ip',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'device_token' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect('/users')->with('toast_success', 'User updated successfully');
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->back()->with('toast_success', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Error deleting user');
        }
    }
}

