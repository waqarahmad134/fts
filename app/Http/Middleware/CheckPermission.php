<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Admin always has access
        if ($user->role && strtolower($user->role->name) === 'admin') {
            return $next($request);
        }

        // Check if user has any of the required permissions
        if (!$user->hasAnyPermission($permissions)) {
            // Format permission names for display
            $permissionNames = array_map(function ($p) {
                return ucwords(str_replace('_', ' ', $p));
            }, $permissions);
            $permissionText = implode(' or ', $permissionNames);

            $errorMessage = "Access Denied! You don't have permission to: {$permissionText}. Please contact your administrator.";

            // If AJAX request, return JSON error
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => $errorMessage,
                    'permissions_required' => $permissions
                ], 403);
            }

            // Otherwise redirect to home page with error message
            return redirect('/')->with('toast_error', $errorMessage);
        }

        return $next($request);
    }
}
