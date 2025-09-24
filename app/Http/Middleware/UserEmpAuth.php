<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserEmpAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // อนุญาตถ้าผ่านอย่างน้อย 1 guard
        foreach (['web', 'employee'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);
                return $next($request);
            }
        }

        // ไม่ผ่านสัก guard
        if ($request->expectsJson()) {
            return redirect('/auth/users/login');
        }


        return redirect()->guest('/auth/users/login');
    }
}
