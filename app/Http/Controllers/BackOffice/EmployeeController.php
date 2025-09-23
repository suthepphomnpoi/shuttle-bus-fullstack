<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpDepartment;
use App\Models\MpEmployee;
use App\Models\MpPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function data(Request $request)
    {
        $query = MpEmployee::query()
            ->leftJoin('mp_departments as d', 'd.dept_id', '=', 'mp_employees.dept_id')
            ->leftJoin('mp_positions as p', 'p.position_id', '=', 'mp_employees.position_id')
            ->select([
                'mp_employees.employee_id',
                'mp_employees.email',
                'mp_employees.first_name',
                'mp_employees.last_name',
                'mp_employees.gender',
                'mp_employees.created_at',
                'd.name as dept_name',
                'p.name as position_name'
            ]);

        return DataTables::of($query)
            ->filter(function($q) use ($request){
                if ($search = $request->input('search.value')) {
                    $q->where(function($qq) use ($search){
                        $qq->where('mp_employees.email','like',"%{$search}%")
                           ->orWhere('mp_employees.first_name','like',"%{$search}%")
                           ->orWhere('mp_employees.last_name','like',"%{$search}%")
                           ->orWhere('d.name','like',"%{$search}%")
                           ->orWhere('p.name','like',"%{$search}%");
                    });
                }
            })
            ->order(function($q) use ($request){
                if (!$request->has('order')) {
                    $q->orderBy('employee_id','desc');
                }
            })
            ->toJson();
    }

    public function show($id)
    {
        $emp = MpEmployee::findOrFail($id);
        return response()->json($emp);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required','email','max:100','unique:mp_employees,email'],
            'first_name' => ['required','max:50'],
            'last_name' => ['required','max:50'],
            'gender' => ['required', Rule::in(['M','F'])],
            'dept_id' => ['required','integer','exists:mp_departments,dept_id'],
            'position_id' => ['required','integer','exists:mp_positions,position_id'],
            'password' => ['required','min:6'],
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
            'dept_id.required' => 'กรุณาเลือกแผนก',
            'dept_id.integer' => 'ข้อมูลแผนกไม่ถูกต้อง',
            'dept_id.exists' => 'แผนกที่เลือกไม่มีอยู่ในระบบ',
            'position_id.required' => 'กรุณาเลือกตำแหน่ง',
            'position_id.integer' => 'ข้อมูลตำแหน่งไม่ถูกต้อง',
            'position_id.exists' => 'ตำแหน่งที่เลือกไม่มีอยู่ในระบบ',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
        ]);
        $emp = MpEmployee::create([
            'email' => $validated['email'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'dept_id' => $validated['dept_id'],
            'position_id' => $validated['position_id'],
            'password_hash' => Hash::make($validated['password']),
        ]);
        return response()->json(['message' => 'Created','id' => $emp->employee_id]);
    }

    public function update($id, Request $request)
    {
        $emp = MpEmployee::findOrFail($id);
        $validated = $request->validate([
            'email' => ['required','email','max:100', Rule::unique('mp_employees','email')->ignore($emp->employee_id, 'employee_id')],
            'first_name' => ['required','max:50'],
            'last_name' => ['required','max:50'],
            'gender' => ['required', Rule::in(['M','F'])],
            'dept_id' => ['required','integer','exists:mp_departments,dept_id'],
            'position_id' => ['required','integer','exists:mp_positions,position_id'],
            'password' => ['nullable','min:6'],
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
            'dept_id.required' => 'กรุณาเลือกแผนก',
            'dept_id.integer' => 'ข้อมูลแผนกไม่ถูกต้อง',
            'dept_id.exists' => 'แผนกที่เลือกไม่มีอยู่ในระบบ',
            'position_id.required' => 'กรุณาเลือกตำแหน่ง',
            'position_id.integer' => 'ข้อมูลตำแหน่งไม่ถูกต้อง',
            'position_id.exists' => 'ตำแหน่งที่เลือกไม่มีอยู่ในระบบ',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
        ]);
        $emp->email = $validated['email'];
        $emp->first_name = $validated['first_name'];
        $emp->last_name = $validated['last_name'];
        $emp->gender = $validated['gender'];
        $emp->dept_id = $validated['dept_id'];
        $emp->position_id = $validated['position_id'];
        if (!empty($validated['password'])) {
            $emp->password_hash = Hash::make($validated['password']);
        }
        $emp->save();
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $emp = MpEmployee::findOrFail($id);
        $emp->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
