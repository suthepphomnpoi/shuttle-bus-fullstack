<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

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
        $adminPosId = (int) (DB::table('mp_positions')->where('name', 'Admin')->value('position_id') ?? 1);
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
                    'position_id'   => $employeePosId,  // เริ่มต้นเป็น Employee (จะอัปเป็น Admin ทีหลังทั้งหมด)
                    'created_at'    => DB::raw('SYSDATE'),
                ]);
            }
        }

        // ===== Step 4: Seed menus (ถ้ายังไม่มี) =====
        $menus = [
            ['key_name' => 'dashboard',                    'name' => 'Dashboard'],
            ['key_name' => 'user_manage',                  'name' => 'ผู้ใช้งาน (Users)'],
            ['key_name' => 'department_position_manage',   'name' => 'แผนก & ตำแหน่ง'],
            ['key_name' => 'employee_manage',              'name' => 'พนักงาน'],
            ['key_name' => 'menu_manage',                  'name' => 'เมนู & สิทธิ์เข้าถึง'],
            ['key_name' => 'routes_places_manage',         'name' => 'เส้นทาง & สถานที่'],
            ['key_name' => 'vehicle_vehicle_type_manage',  'name' => 'รถ & ประเภทรถ'],
            ['key_name' => 'trips_manage',                 'name' => 'รอบรถ (Trips)'],
        ];
        foreach ($menus as $m) {
            $exists = DB::table('mp_menus')->where('key_name', $m['key_name'])->exists();
            if (!$exists) {
                DB::table('mp_menus')->insert([
                    'key_name'   => $m['key_name'],
                    'name'       => $m['name'],
                    'created_at' => DB::raw('SYSDATE'),
                ]);
            }
        }

        // ===== Step 5: ตั้งพนักงานทั้งหมดเป็นตำแหน่ง Admin =====
        DB::table('mp_employees')->update(['position_id' => $adminPosId]);

        // ===== Step 6: Grant สิทธิ์เมนูทั้งหมดให้ตำแหน่ง Admin =====
        $menuIds = DB::table('mp_menus')->pluck('menu_id')->map(fn($v)=>(int)$v)->all();
        // ลบ mapping เดิมของ Admin ออกก่อน แล้วค่อยใส่ใหม่ทั้งหมด
        DB::table('mp_position_menus')->where('position_id', $adminPosId)->delete();
        $rows = [];
        foreach ($menuIds as $mid) {
            $rows[] = [ 'position_id' => $adminPosId, 'menu_id' => $mid ];
        }
        if (!empty($rows)) {
            // insert เป็นชุด
            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('mp_position_menus')->insert($chunk);
            }
        }

        // ===== Step 6.5: Vehicle Types (ถ้ายังไม่มี) =====
        $vehicleTypes = [
            ['name' => 'Van'],
            ['name' => 'Minibus'],
            ['name' => 'Bus'],
        ];
        foreach ($vehicleTypes as $vt) {
            $exists = DB::table('mp_vehicle_types')->where('name', $vt['name'])->exists();
            if (!$exists) {
                DB::table('mp_vehicle_types')->insert([
                    'name' => $vt['name'],
                    'created_at' => DB::raw('SYSDATE'),
                    'updated_at' => DB::raw('SYSDATE'),
                ]);
            }
        }

        $vtIds = DB::table('mp_vehicle_types')->pluck('vehicle_type_id', 'name');

        // ===== Step 6.6: Vehicles (กันซ้ำด้วยป้ายทะเบียน) =====
        $vehicles = [
            ['license_plate' => 'VAN-2001', 'vehicle_type' => 'Van',    'capacity' => 12, 'description' => 'Passenger van 12 seats'],
            ['license_plate' => 'BUS-1001', 'vehicle_type' => 'Bus',    'capacity' => 40, 'description' => 'Full size bus 40 seats'],
            ['license_plate' => 'MB-3001',  'vehicle_type' => 'Minibus','capacity' => 20, 'description' => 'Mini bus 20 seats'],
        ];
        foreach ($vehicles as $v) {
            if (!DB::table('mp_vehicles')->where('license_plate', $v['license_plate'])->exists()) {
                $typeId = $vtIds[$v['vehicle_type']] ?? null;
                if ($typeId) {
                    DB::table('mp_vehicles')->insert([
                        'vehicle_type_id' => (int)$typeId,
                        'capacity'        => (int)$v['capacity'],
                        'license_plate'   => $v['license_plate'],
                        'description'     => $v['description'],
                        'status'          => 'active',
                        'created_at'      => DB::raw('SYSDATE'),
                        'updated_at'      => DB::raw('SYSDATE'),
                    ]);
                }
            }
        }

        // ===== Step 6.7: Routes (กันซ้ำด้วยชื่อ) =====
        $routes = [
            ['name' => 'Campus Loop'],
            ['name' => 'Dorm Shuttle'],
            ['name' => 'Airport Express'],
        ];
        foreach ($routes as $r) {
            if (!DB::table('mp_routes')->where('name', $r['name'])->exists()) {
                DB::table('mp_routes')->insert([
                    'name'       => $r['name'],
                    'created_at' => DB::raw('SYSDATE'),
                ]);
            }
        }

        // ===== Step 6.7.1: Places (ถ้ายังไม่มี) =====
        $places = [
            'Main Gate', 'Engineering Building', 'Library', 'Dorm A', 'Dorm B', 'Cafeteria', 'Airport', 'City Center', 'Stadium'
        ];
        foreach ($places as $pname) {
            if (!DB::table('mp_places')->where('name', $pname)->exists()) {
                DB::table('mp_places')->insert([
                    'name'       => $pname,
                    'created_at' => DB::raw('SYSDATE'),
                ]);
            }
        }

        // ===== Step 6.7.2: Attach route places with order =====
        $routeIdsByName = DB::table('mp_routes')->pluck('route_id', 'name');
        $placeIdsByName = DB::table('mp_places')->pluck('place_id', 'name');

        $routeStops = [
            'Campus Loop' => [
                ['Main Gate', 0],
                ['Engineering Building', 7],
                ['Library', 5],
                ['Cafeteria', 6],
                ['Main Gate', 8],
            ],
            'Dorm Shuttle' => [
                ['Dorm A', 0],
                ['Dorm B', 6],
                ['Main Gate', 10],
                ['Dorm A', 10],
            ],
            'Airport Express' => [
                ['Main Gate', 0],
                ['City Center', 25],
                ['Airport', 30],
            ],
        ];

        foreach ($routeStops as $routeName => $stops) {
            $rid = $routeIdsByName[$routeName] ?? null;
            if (!$rid) continue;

            // ถ้ายังไม่มี route_places ของ route นี้ ค่อยสร้าง (idempotent)
            $hasAny = DB::table('mp_route_places')->where('route_id', $rid)->exists();
            if ($hasAny) continue;

            $seq = 1;
            $rows = [];
            foreach ($stops as [$pname, $dur]) {
                $pid = $placeIdsByName[$pname] ?? null;
                if (!$pid) continue;
                $rows[] = [
                    'route_id'    => (int)$rid,
                    'place_id'    => (int)$pid,
                    'sequence_no' => $seq,
                    'duration_min'=> (int)$dur,
                ];
                $seq++;
            }
            if (!empty($rows)) {
                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table('mp_route_places')->insert($chunk);
                }
            }
        }

        // ===== Step 6.7.1: Places (กันซ้ำด้วยชื่อ) =====
        $places = [
            ['name' => 'Main Gate'],
            ['name' => 'Library'],
            ['name' => 'Dorm A'],
            ['name' => 'Dorm B'],
            ['name' => 'Engineering Building'],
            ['name' => 'Cafeteria'],
            ['name' => 'Airport Terminal'],
            ['name' => 'Bus Station'],
            // --- Additional Thai places ---
            ['name' => 'มหาวิทยาลัยเทคโนโลยีมหานคร'],
            ['name' => 'โลตัสหนองจอก'],
            ['name' => 'รงพยาบาลหนองจอก'],
            ['name' => 'Big C หนองจอก'],
            ['name' => 'สวนสาธารณะหนองจอก'],
            ['name' => 'ร้านส้มตำป้านาง ซอย8'],
        ];
        foreach ($places as $p) {
            if (!DB::table('mp_places')->where('name', $p['name'])->exists()) {
                DB::table('mp_places')->insert([
                    'name'       => $p['name'],
                    'created_at' => DB::raw('SYSDATE'),
                ]);
            }
        }

        // ===== Step 6.7.2: Route Places mapping (idempotent, with sequence/duration) =====
        $routeMap = DB::table('mp_routes')->pluck('route_id', 'name');
        $placeMap = DB::table('mp_places')->pluck('place_id', 'name');

        $routePlaces = [
            'Campus Loop' => [
                ['place' => 'Main Gate', 'seq' => 1, 'dur' => 0],
                ['place' => 'Engineering Building', 'seq' => 2, 'dur' => 7],
                ['place' => 'Library', 'seq' => 3, 'dur' => 5],
                ['place' => 'Cafeteria', 'seq' => 4, 'dur' => 4],
                ['place' => 'Main Gate', 'seq' => 5, 'dur' => 6],
            ],
            'Dorm Shuttle' => [
                ['place' => 'Main Gate', 'seq' => 1, 'dur' => 0],
                ['place' => 'Dorm A', 'seq' => 2, 'dur' => 8],
                ['place' => 'Dorm B', 'seq' => 3, 'dur' => 5],
                ['place' => 'Main Gate', 'seq' => 4, 'dur' => 7],
            ],
            'Airport Express' => [
                ['place' => 'Main Gate', 'seq' => 1, 'dur' => 0],
                ['place' => 'Bus Station', 'seq' => 2, 'dur' => 20],
                ['place' => 'Airport Terminal', 'seq' => 3, 'dur' => 25],
            ],
        ];

        foreach ($routePlaces as $routeName => $stops) {
            $rid = $routeMap[$routeName] ?? null;
            if (!$rid) continue;

            foreach ($stops as $s) {
                $pid = $placeMap[$s['place']] ?? null;
                if (!$pid) continue;

                // unique by (route_id, sequence_no)
                $exists = DB::table('mp_route_places')
                    ->where('route_id', $rid)
                    ->where('sequence_no', (int)$s['seq'])
                    ->exists();
                if ($exists) continue;

                DB::table('mp_route_places')->insert([
                    'route_id'    => (int)$rid,
                    'place_id'    => (int)$pid,
                    'sequence_no' => (int)$s['seq'],
                    'duration_min'=> (int)$s['dur'],
                ]);
            }
        }

        // (Removed) Step 6.8: Sample Trips — no auto-created trips; add via UI instead.

        // ===== Step 7: Invalidate menu access cache/version =====
        if (function_exists('bumpMenuAccessVersion')) {
            bumpMenuAccessVersion();
        } else {
            try {
                Cache::increment('menu_access_version');
            } catch (\Throwable $e) {
                Cache::forever('menu_access_version', 1);
            }
        }
    }
}
