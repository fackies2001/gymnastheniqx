<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class RoleMenuFilter implements FilterInterface
{
    /**
     * Transforms a menu item. Add the restricted attribute to a menu item
     * when the user does not have the required role.
     *
     * @param  array  $item  A menu item
     * @return array The transformed menu item
     */
    public function transform($item)
    {
        // Skip if no role restriction
        if (!isset($item['role'])) {
            return $item;
        }

        // Get user's role
        $user = Auth::user();

        // If no user logged in, restrict the item
        if (!$user) {
            $item['restricted'] = true;
            return $item;
        }

        $userRole = $user->role?->role_name ?? null;

        // If user has no role, restrict the item
        if (!$userRole) {
            $item['restricted'] = true;
            return $item;
        }

        // Check if user has required role
        $requiredRoles = is_array($item['role']) ? $item['role'] : [$item['role']];

        if (!in_array($userRole, $requiredRoles)) {
            $item['restricted'] = true;
        }

        return $item;
    }
}
