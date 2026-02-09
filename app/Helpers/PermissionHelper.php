<?php

if (!function_exists('hasPermission')) {
    /**
     * Check if the authenticated user has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    function hasPermission($permission)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasPermission($permission);
    }
}

if (!function_exists('hasAnyPermission')) {
    /**
     * Check if the authenticated user has any of the given permissions
     *
     * @param array|string $permissions
     * @return bool
     */
    function hasAnyPermission($permissions)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasAnyPermission($permissions);
    }
}
