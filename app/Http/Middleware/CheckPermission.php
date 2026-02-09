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
            // If AJAX request, return JSON error
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'You do not have permission to perform this action.'
                ], 403);
            }

            // Otherwise redirect with error message
            return redirect()->back()->with('toast_error', 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
