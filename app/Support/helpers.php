<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

if (!function_exists('canMenu')) {
    /**
     * Check if current user (web or employee) can access menu by key code.
     * - web guard: always true (admin)
     * - employee guard: true if employee's position is mapped to menu code in mp_menus/mp_position_menus
     * Results cached per-user for 60 minutes; invalidated by cache version bump.
     */
    function canMenu(string $code): bool
    {
        // Admins (web) can access all
        if (Auth::guard('web')->check()) {
            return true;
        }

        $guard = 'employee';
        if (!Auth::guard($guard)->check()) {
            return false;
        }

        $user = Auth::guard($guard)->user();
        $empId = $user->employee_id ?? null;
        $posId = $user->position_id ?? null;
        if (!$empId || !$posId) return false;

        $version = Cache::get('menu_access_version', 1);
        $cacheKey = "menu_access:emp:{$empId}:v{$version}";

        $codes = Cache::remember($cacheKey, 60 * 60, function () use ($posId) {
            // Get key_name for menus mapped to this position
            $rows = DB::table('mp_position_menus as pm')
                ->join('mp_menus as m', 'm.menu_id', '=', 'pm.menu_id')
                ->where('pm.position_id', $posId)
                ->pluck('m.key_name')
                ->map(fn($v) => strtolower(trim($v)))
                ->unique()
                ->values()
                ->all();
            return $rows;
        });

        return in_array(strtolower($code), $codes, true);
    }
}

if (!function_exists('bumpMenuAccessVersion')) {
    /**
     * Bump global version to invalidate per-user cached menu access.
     */
    function bumpMenuAccessVersion(): void
    {
        $current = Cache::get('menu_access_version', 1);
        Cache::put('menu_access_version', $current + 1, 60 * 60 * 24);
    }
}
