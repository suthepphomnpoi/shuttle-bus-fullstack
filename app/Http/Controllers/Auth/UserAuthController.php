<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MpUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.user-login');
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

        if (Auth::guard('web')->attempt($credentials, false)) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        ], 422);
    }

    public function registerPage()
    {
        return view('auth.user-register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:mp_users,email'],
            'gender' => ['required', 'in:M,F'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'first_name.required' => 'กรุณาระบุชื่อ',
            'first_name.max' => 'ชื่อยาวเกินไป (50 ตัวอักษร)',
            'last_name.required' => 'กรุณาระบุนามสกุล',
            'last_name.max' => 'นามสกุลยาวเกินไป (50 ตัวอักษร)',
            'email.required' => 'กรุณาระบุอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.max' => 'อีเมลยาวเกินไป (100 ตัวอักษร)',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
        ]);


        $user = MpUser::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'gender' => $validated['gender'],
            'password_hash' => Hash::make($validated['password']),
        ]);

        Auth::guard('web')->login($user);

        return response()->json([
            'success' => true,
            'message' => 'สมัครสมาชิกสำเร็จ',
        ]);
    }

    public function logout(Request $request)
    {
        $redirect = '/auth/users/login';

        if (Auth::guard('employee')->check()) {
            Auth::guard('employee')->logout();
            $redirect = '/auth/employees/login';
        }

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $redirect = '/auth/users/login';
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($redirect);
    }
}
