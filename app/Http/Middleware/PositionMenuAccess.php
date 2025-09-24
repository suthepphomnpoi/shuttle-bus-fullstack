<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PositionMenuAccess
{
    /**
     * Enforce menu-level access based on employee position ↔ menu mapping.
     * Usage: ->middleware('position.menu:menu_code')
     */
    public function handle(Request $request, Closure $next, ?string $code = null): Response
    {
        // If logged in as web user (Admin), allow everything
        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');
            return $next($request);
        }

        // Otherwise use employee guard
        if (Auth::guard('employee')->check()) {
            Auth::shouldUse('employee');

            // derive code if not explicitly provided
            if (!$code) {
                // Map common backoffice entry routes
                $path = ltrim($request->path(), '/');
                // Expect paths like: backoffice, backoffice/users, backoffice/employees, etc.
                if ($path === 'backoffice' || $path === 'backoffice/') {
                    $code = 'dashboard';
                } elseif (str_starts_with($path, 'backoffice/users')) {
                    $code = 'user_manage';
                } elseif (str_starts_with($path, 'backoffice/departments') || str_starts_with($path, 'backoffice/positions') || $path === 'backoffice/org') {
                    $code = 'department_position_manage';
                } elseif (str_starts_with($path, 'backoffice/employees')) {
                    $code = 'employee_manage';
                } elseif (str_starts_with($path, 'backoffice/menus')) {
                    $code = 'menu_manage';
                } elseif (str_starts_with($path, 'backoffice/routes') || str_starts_with($path, 'backoffice/places') || $path === 'backoffice/routes-places') {
                    $code = 'routes_places_manage';
                } elseif (str_starts_with($path, 'backoffice/vehicle-types') || str_starts_with($path, 'backoffice/vehicles') || $path === 'backoffice/vehicles') {
                    $code = 'vehicle_vehicle_type_manage';
                }
            }

            if ($code && function_exists('canMenu')) {
                if (canMenu($code)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Forbidden');
    }
}
