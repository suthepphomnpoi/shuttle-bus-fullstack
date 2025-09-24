<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.employee-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        if (Auth::guard('employee')->attempt($credentials, false)) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'redirect' => url('/'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        ], 422);
    }

    public function logout()
    {
        Auth::guard('employee')->logout();
        return redirect('/auth/employees/login');
    }
}
