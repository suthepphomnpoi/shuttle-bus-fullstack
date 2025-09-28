<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpTrip;
use App\Models\MpRoute;
use App\Models\MpVehicle;
use App\Models\MpEmployee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function init()
    {
        $routes = MpRoute::select(['route_id','name'])->orderBy('name')->get();
        $vehicles = MpVehicle::select(['vehicle_id','license_plate'])->orderBy('license_plate')->get();
        $drivers = MpEmployee::select(['employee_id','first_name','last_name'])->orderBy('first_name')->get()
            ->map(fn($e)=>[
                'employee_id' => $e->employee_id,
                'name' => $e->first_name.' '.$e->last_name,
            ]);
        return response()->json(compact('routes','vehicles','drivers'));
    }
    public function data(Request $request)
    {
        $q = MpTrip::query()
            ->leftJoin('mp_routes as r','r.route_id','=','mp_trips.route_id')
            ->leftJoin('mp_vehicles as v','v.vehicle_id','=','mp_trips.vehicle_id')
            ->leftJoin('mp_employees as e','e.employee_id','=','mp_trips.driver_id')
            ->select([
                'mp_trips.trip_id',
                'mp_trips.service_date',
                'mp_trips.depart_time',
                'r.name as route_name',
                'v.license_plate as vehicle_plate',
                'mp_trips.capacity',
                'mp_trips.reserved_seats',
                'mp_trips.status',
                'mp_trips.created_at',
                'mp_trips.updated_at',
                DB::raw("e.first_name || ' ' || e.last_name as driver_name"),
            ]);

        return DataTables::of($q)
            ->filter(function($q) use ($request){
                if ($s = $request->input('search.value')) {
                    $q->where(function($w) use ($s){
                        $w->where('r.name','like',"%{$s}%")
                          ->orWhere('v.license_plate','like',"%{$s}%")
                          ->orWhere('e.first_name','like',"%{$s}%")
                          ->orWhere('e.last_name','like',"%{$s}%");
                    });
                }
            })
            ->order(function($q) use ($request){ if(!$request->has('order')) $q->orderBy('mp_trips.trip_id','desc'); })
            ->toJson();
    }

    public function show($id)
    { return response()->json(MpTrip::findOrFail($id)); }

    private function validatePayload(Request $request, ?MpTrip $trip = null): array
    {
        $id = $trip?->trip_id;
        $rules = [
            'route_id' => ['required','integer','exists:mp_routes,route_id'],
            'vehicle_id' => ['required','integer','exists:mp_vehicles,vehicle_id'],
            'driver_id' => ['required','integer','exists:mp_employees,employee_id'],
            'service_date' => ['required','date'],
            'depart_time' => ['required','regex:/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/'],
            'estimated_minutes' => ['nullable','integer','min:0'],
            'capacity' => ['required','integer','min:1'],
            'reserved_seats' => ['nullable','integer','min:0'],
            'status' => ['required', Rule::in(['scheduled','ongoing','completed','cancelled'])],
            'notes' => ['nullable','max:500'],
        ];
        $messages = [
            'route_id.required' => 'กรุณาเลือกเส้นทาง',
            'route_id.exists' => 'เส้นทางไม่ถูกต้อง',
            'vehicle_id.required' => 'กรุณาเลือกรถ',
            'vehicle_id.exists' => 'รถไม่ถูกต้อง',
            'driver_id.required' => 'กรุณาเลือกคนขับ',
            'driver_id.exists' => 'คนขับไม่ถูกต้อง',
            'service_date.required' => 'กรุณาเลือกวันที่ให้บริการ',
            'service_date.date' => 'รูปแบบวันที่ไม่ถูกต้อง',
            'depart_time.required' => 'กรุณาระบุเวลาออกเดินทาง',
            'depart_time.regex' => 'รูปแบบเวลาต้องเป็น HH:MM',
            'estimated_minutes.integer' => 'เวลาต้องเป็นตัวเลข',
            'capacity.required' => 'กรุณาระบุจำนวนที่นั่ง',
            'capacity.min' => 'ต้องมากกว่า 0',
            'reserved_seats.integer' => 'ต้องเป็นตัวเลข',
            'status.required' => 'กรุณาเลือกสถานะ',
            'status.in' => 'สถานะไม่ถูกต้อง',
            'notes.max' => 'โน้ตไม่เกิน 500 ตัวอักษร',
        ];
        $validated = $request->validate($rules, $messages);

        // Enforce reserved <= capacity
        $reserved = (int)($validated['reserved_seats'] ?? 0);
        if ($reserved > (int)$validated['capacity']) {
            abort(response()->json(['message' => 'จองแล้วต้องไม่เกินจำนวนที่นั่งทั้งหมด'], 422));
        }

        // enforce uniqueness: date+time+driver and date+time+vehicle
        $existsDriver = MpTrip::where('service_date', $validated['service_date'])
            ->where('depart_time', $validated['depart_time'])
            ->where('driver_id', (int)$validated['driver_id'])
            ->when($id, fn($q)=>$q->where('trip_id','<>',$id))
            ->exists();
        if ($existsDriver) {
            abort(response()->json(['message' => 'คนขับมีรอบซ้อนในวันและเวลาเดียวกัน'], 422));
        }
        $existsVehicle = MpTrip::where('service_date', $validated['service_date'])
            ->where('depart_time', $validated['depart_time'])
            ->where('vehicle_id', (int)$validated['vehicle_id'])
            ->when($id, fn($q)=>$q->where('trip_id','<>',$id))
            ->exists();
        if ($existsVehicle) {
            abort(response()->json(['message' => 'รถคันนี้มีรอบซ้อนในวันและเวลาเดียวกัน'], 422));
        }

        return $validated;
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        // Default reserved
        if (!isset($validated['reserved_seats'])) $validated['reserved_seats'] = 0;
        $trip = MpTrip::create($validated);
        return response()->json(['message'=>'Created','id'=>$trip->trip_id]);
    }

    public function update($id, Request $request)
    {
        $trip = MpTrip::findOrFail($id);
        $validated = $this->validatePayload($request, $trip);
        $trip->update($validated);
        return response()->json(['message'=>'Updated']);
    }

    public function destroy($id)
    {
        MpTrip::findOrFail($id)->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
