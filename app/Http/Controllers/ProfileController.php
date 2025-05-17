<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'designation' => ['required', 'string', 'max:255'],
            'wing' => ['required', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        // Verify current password if changing password
        if ($request->current_password) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('toast_error', 'Current password is incorrect');
            }
        }

        // Update basic info
        $user->name = $request->name;
        $user->email = $request->email;
        $user->designation = $request->designation;
        $user->wing = $request->wing;

        // Update password if provided
        if ($request->new_password) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->back()->with('toast_success', 'Profile updated successfully');
    }
} 