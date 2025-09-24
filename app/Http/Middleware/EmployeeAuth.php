<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EmployeeAuth
{
    /**
     * Allow only authenticated employee guard; otherwise redirect to employee login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('employee')->check()) {
            Auth::shouldUse('employee');
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return redirect()->guest('/auth/employees/login');
    }
}
