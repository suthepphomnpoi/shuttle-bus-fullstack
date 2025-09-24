<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpVehicleType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class VehicleTypeController extends Controller
{
    public function data(Request $request)
    {
    $q = MpVehicleType::query()->select(['vehicle_type_id','name','created_at','updated_at']);
        return DataTables::of($q)
            ->filter(function($q) use ($request){ if($s=$request->input('search.value')) $q->where('name','like',"%{$s}%"); })
            ->order(function($q) use ($request){ if(!$request->has('order')) $q->orderBy('vehicle_type_id','desc'); })
            ->toJson();
    }

    public function list()
    {   // for dropdowns
        return response()->json(MpVehicleType::select(['vehicle_type_id','name'])->orderBy('name')->get());
    }

    public function show($id)
    { return response()->json(MpVehicleType::findOrFail($id)); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:100','unique:mp_vehicle_types,name'],
        ], [
            'name.required' => 'กรุณากรอกชื่อประเภทรถ',
            'name.max' => 'ไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อนี้มีอยู่ในระบบแล้ว',
        ]);
        $m = MpVehicleType::create($validated);
        return response()->json(['message'=>'Created','id'=>$m->vehicle_type_id]);
    }

    public function update($id, Request $request)
    {
        $m = MpVehicleType::findOrFail($id);
        $validated = $request->validate([
            'name' => ['required','max:100', Rule::unique('mp_vehicle_types','name')->ignore($m->vehicle_type_id,'vehicle_type_id')],
        ], [
            'name.required' => 'กรุณากรอกชื่อประเภทรถ',
            'name.max' => 'ไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อนี้มีอยู่ในระบบแล้ว',
        ]);
        $m->update($validated);
        return response()->json(['message'=>'Updated']);
    }

    public function destroy($id)
    {
        MpVehicleType::findOrFail($id)->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
