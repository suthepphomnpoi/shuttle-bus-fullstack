<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpVehicle;
use App\Models\MpVehicleType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class VehicleController extends Controller
{
    public function data(Request $request)
    {
        $q = MpVehicle::query()
            ->leftJoin('mp_vehicle_types as t','t.vehicle_type_id','=','mp_vehicles.vehicle_type_id')
            ->select(['mp_vehicles.vehicle_id','mp_vehicles.license_plate','mp_vehicles.description','mp_vehicles.status','mp_vehicles.capacity','t.name as type_name','mp_vehicles.created_at','mp_vehicles.updated_at']);

        return DataTables::of($q)
            ->filter(function($q) use ($request){ if($s=$request->input('search.value')) $q->where('mp_vehicles.license_plate','like',"%{$s}%"); })
            ->order(function($q) use ($request){ if(!$request->has('order')) $q->orderBy('mp_vehicles.vehicle_id','desc'); })
            ->toJson();
    }

    public function show($id)
    { return response()->json(MpVehicle::findOrFail($id)); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type_id' => ['required','integer','exists:mp_vehicle_types,vehicle_type_id'],
            'license_plate' => ['required','max:50','unique:mp_vehicles,license_plate'],
            'description' => ['nullable','max:255'],
            'capacity' => ['required','integer','min:1'],
            'status' => ['required', Rule::in(['active','maintenance','retired'])],
        ], [
            'vehicle_type_id.required' => 'กรุณาเลือกประเภทรถ',
            'vehicle_type_id.integer' => 'ข้อมูลประเภทรถไม่ถูกต้อง',
            'vehicle_type_id.exists' => 'ประเภทรถที่เลือกไม่มีอยู่ในระบบ',
            'license_plate.required' => 'กรุณากรอกทะเบียนรถ',
            'license_plate.max' => 'ทะเบียนรถไม่เกิน 50 ตัวอักษร',
            'license_plate.unique' => 'ทะเบียนนี้มีอยู่ในระบบแล้ว',
            'description.max' => 'คำอธิบายไม่เกิน 255 ตัวอักษร',
            'capacity.required' => 'กรุณาระบุจำนวนที่นั่ง',
            'capacity.integer' => 'ต้องเป็นตัวเลข',
            'capacity.min' => 'ต้องมากกว่า 0',
            'status.required' => 'กรุณาเลือกสถานะรถ',
            'status.in' => 'สถานะไม่ถูกต้อง'
        ]);

        $m = MpVehicle::create($validated);
        return response()->json(['message'=>'Created','id'=>$m->vehicle_id]);
    }

    public function update($id, Request $request)
    {
        $m = MpVehicle::findOrFail($id);
        $validated = $request->validate([
            'vehicle_type_id' => ['required','integer','exists:mp_vehicle_types,vehicle_type_id'],
            'license_plate' => ['required','max:50', Rule::unique('mp_vehicles','license_plate')->ignore($m->vehicle_id,'vehicle_id')],
            'description' => ['nullable','max:255'],
            'capacity' => ['required','integer','min:1'],
            'status' => ['required', Rule::in(['active','maintenance','retired'])],
        ], [
            'vehicle_type_id.required' => 'กรุณาเลือกประเภทรถ',
            'vehicle_type_id.integer' => 'ข้อมูลประเภทรถไม่ถูกต้อง',
            'vehicle_type_id.exists' => 'ประเภทรถที่เลือกไม่มีอยู่ในระบบ',
            'license_plate.required' => 'กรุณากรอกทะเบียนรถ',
            'license_plate.max' => 'ทะเบียนรถไม่เกิน 50 ตัวอักษร',
            'license_plate.unique' => 'ทะเบียนนี้มีอยู่ในระบบแล้ว',
            'description.max' => 'คำอธิบายไม่เกิน 255 ตัวอักษร',
            'capacity.required' => 'กรุณาระบุจำนวนที่นั่ง',
            'capacity.integer' => 'ต้องเป็นตัวเลข',
            'capacity.min' => 'ต้องมากกว่า 0',
            'status.required' => 'กรุณาเลือกสถานะรถ',
            'status.in' => 'สถานะไม่ถูกต้อง'
        ]);
        $m->update($validated);
        return response()->json(['message'=>'Updated']);
    }

    public function destroy($id)
    {
        MpVehicle::findOrFail($id)->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
