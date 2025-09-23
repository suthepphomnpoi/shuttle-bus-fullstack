<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpRoute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class RouteController extends Controller
{
    public function data(Request $request)
    {
        $q = MpRoute::query()->select(['route_id','name','created_at']);
        return DataTables::of($q)
            ->filter(function($q) use ($request){
                if ($s = $request->input('search.value')) {
                    $q->where('name','like',"%{$s}%");
                }
            })
            ->order(function($q) use ($request){ if(!$request->has('order')) $q->orderBy('route_id','desc'); })
            ->toJson();
    }

    public function show($id)
    {
        return response()->json(MpRoute::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:100','unique:mp_routes,name'],
        ], [
            'name.required' => 'กรุณากรอกชื่อเส้นทาง',
            'name.max' => 'ชื่อเส้นทางต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อเส้นทางนี้มีอยู่ในระบบแล้ว',
        ]);
        $m = MpRoute::create($validated);
        return response()->json(['message' => 'Created','id' => $m->route_id]);
    }

    public function update($id, Request $request)
    {
        $m = MpRoute::findOrFail($id);
        $validated = $request->validate([
            'name' => ['required','max:100', Rule::unique('mp_routes','name')->ignore($m->route_id,'route_id')],
        ], [
            'name.required' => 'กรุณากรอกชื่อเส้นทาง',
            'name.max' => 'ชื่อเส้นทางต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อเส้นทางนี้มีอยู่ในระบบแล้ว',
        ]);
        $m->update($validated);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $m = MpRoute::findOrFail($id);
        $m->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
