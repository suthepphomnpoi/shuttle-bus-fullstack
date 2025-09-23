<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Step 0: ข้อมูลตั้งต้น =====
        $emails = [
            "6611130085@mut.ac.th",
            "6611130075@mut.ac.th",
            "6611130093@mut.ac.th",
            "6611130092@mut.ac.th",
            "6611130012@mut.ac.th",
            "6611130048@mut.ac.th",
            "6611130001@mut.ac.th",
            "thachadap95@gmail.com",
            "jetsada.kho.forwork@gmail.com",
            "6611130039@mut.ac.th",
            "6611130027@mut.ac.th",
            "6611120062@mut.ac.th",
        ];

        // ===== Step 1: Departments =====
        // กันซ้ำ: ถ้ายังไม่มีค่อย insert
        $deptIT = DB::table('mp_departments')->where('name', 'IT Department')->first();
        if (!$deptIT) {
            DB::table('mp_departments')->insert([
                ['name' => 'IT Department', 'created_at' => DB::raw('SYSDATE')],
                ['name' => 'HR Department', 'created_at' => DB::raw('SYSDATE')],
            ]);
        }
        // ดึง id มาใช้ (Oracle อาจตั้งค่า id อัตโนมัติจาก sequence/trigger)
        $deptId = (int) (DB::table('mp_departments')->where('name', 'IT Department')->value('dept_id') ?? 1);

        // ===== Step 2: Positions =====
        $posAdmin = DB::table('mp_positions')->where('name', 'Admin')->first();
        if (!$posAdmin) {
            DB::table('mp_positions')->insert([
                ['name' => 'Admin', 'created_at' => DB::raw('SYSDATE')],
                ['name' => 'Employee', 'created_at' => DB::raw('SYSDATE')],
            ]);
        }
        $employeePosId = (int) (DB::table('mp_positions')->where('name', 'Employee')->value('position_id') ?? 2);

        // ===== Step 3: Users & Employees =====
        foreach ($emails as $i => $email) {
            $username = explode('@', $email)[0];   // รหัสผ่าน = ข้อความหน้า @
            $password = $username;

            // --- 3.1 สร้าง mp_users (กันซ้ำด้วย email) ---
            $existingUser = DB::table('mp_users')->where('email', $email)->first();

            if ($existingUser) {
                $userId = (int) $existingUser->user_id;
            } else {
                // NOTE: insertGetId ต้องระบุชื่อคอลัมน์ PK เป็น 'user_id' (ไม่ใช่ id)
                $userId = DB::table('mp_users')->insertGetId([
                    'email'         => $email,
                    'password_hash' => Hash::make($password),
                    'first_name'    => 'User' . ($i + 1),
                    'last_name'     => 'Test',
                    'gender'        => 'M',
                    'created_at'    => DB::raw('SYSDATE'),
                ], 'user_id');
            }

            // --- 3.2 สร้าง mp_employees (กันซ้ำด้วย email) ---
            $existingEmp = DB::table('mp_employees')->where('email', $email)->first();
            if (!$existingEmp) {
                // ให้ employee_id autoincrement ของตัวเอง (ไม่จำเป็นต้องเท่ากับ user_id)
                DB::table('mp_employees')->insert([
                    'email'         => $email,
                    'password_hash' => Hash::make($password),
                    'first_name'    => 'Emp' . ($i + 1),
                    'last_name'     => 'Test',
                    'gender'        => 'M',
                    'dept_id'       => $deptId,         // IT Department
                    'position_id'   => $employeePosId,  // Employee
                    'created_at'    => DB::raw('SYSDATE'),
                ]);
            }
        }

        // (เลือกได้) ตั้งใครสักคนเป็น Admin ใน employees/positions
        // เช่น promote อีเมลลำดับแรกให้เป็น Admin
        $adminEmail = $emails[0] ?? null;
        if ($adminEmail) {
            $adminPosId = (int) (DB::table('mp_positions')->where('name', 'Admin')->value('position_id') ?? 1);
            DB::table('mp_employees')
                ->where('email', $adminEmail)
                ->update(['position_id' => $adminPosId]);
        }
    }
}
