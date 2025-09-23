<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MpUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function usersPage()
    {
        return view('backoffice.users');
    }

    public function data(Request $request)
    {
        $query = MpUser::query()->select(['user_id', 'email', 'first_name', 'last_name', 'gender', 'created_at']);
        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                if ($search = $request->input('search.value')) {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('email', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                }
            })
            ->order(function ($q) use ($request) {
                if (!$request->has('order')) {
                    $q->orderBy('user_id', 'desc');
                }
            })
            ->toJson();
    }

    public function show($id)
    {
        $user = MpUser::findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100', 'unique:mp_users,email'],
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'gender' => ['required', Rule::in(['M', 'F'])],
            'password' => ['required', 'min:6'],
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.max' => 'อีเมลต้องไม่เกิน 100 ตัวอักษร',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
            'first_name.required' => 'กรุณากรอกชื่อ',
            'first_name.max' => 'ชื่อต้องไม่เกิน 50 ตัวอักษร',
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'last_name.max' => 'นามสกุลต้องไม่เกิน 50 ตัวอักษร',
            'gender.required' => 'กรุณาเลือกเพศ',
            'gender.in' => 'ค่าเพศไม่ถูกต้อง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
        ]);

        $user = MpUser::create([
            'email' => $validated['email'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'password_hash' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Created', 'id' => $user->user_id]);
    }

    public function update($id, Request $request)
    {
        $user = MpUser::findOrFail($id);
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100', Rule::unique('mp_users', 'email')->ignore($user->user_id, 'user_id')],
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'gender' => ['required', Rule::in(['M', 'F'])],
            'password' => ['nullable', 'min:6'],
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.max' => 'อีเมลต้องไม่เกิน 100 ตัวอักษร',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
            'first_name.required' => 'กรุณากรอกชื่อ',
            'first_name.max' => 'ชื่อต้องไม่เกิน 50 ตัวอักษร',
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'last_name.max' => 'นามสกุลต้องไม่เกิน 50 ตัวอักษร',
            'gender.required' => 'กรุณาเลือกเพศ',
            'gender.in' => 'ค่าเพศไม่ถูกต้อง',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
        ]);

        $user->email = $validated['email'];
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->gender = $validated['gender'];
        if (!empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }
        $user->save();

        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $user = MpUser::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
