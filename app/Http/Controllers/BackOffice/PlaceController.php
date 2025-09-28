<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpPlace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PlaceController extends Controller
{
    public function data(Request $request)
    {
        $q = MpPlace::query()->select(['place_id', 'name', 'created_at']);
        return DataTables::of($q)
            ->filter(function ($q) use ($request) {
                if ($s = $request->input('search.value')) $q->where('name', 'like', "%{$s}%");
            })
            ->order(function ($q) use ($request) {
                if (!$request->has('order')) $q->orderBy('place_id', 'desc');
            })
            ->toJson();
    }

    public function list()
    {
        return response()->json(
            MpPlace::query()->select(['place_id', 'name'])->orderBy('name')->get()
        );
    }

    public function show($id)
    {
        return response()->json(MpPlace::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:100', 'unique:mp_places,name'],
        ], [
            'name.required' => 'กรุณากรอกชื่อสถานที่',
            'name.max' => 'ชื่อต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อนี้มีอยู่ในระบบแล้ว',
        ]);


        $m = MpPlace::create($validated);

        return response()->json(['message' => 'Created', 'id' => $m->place_id]);
    }

    public function update($id, Request $request)
    {


        
        $m = MpPlace::findOrFail($id);



        $validated = $request->validate([
            'name' => ['required', 'max:100', Rule::unique('mp_places', 'name')->ignore($m->place_id, 'place_id')],
        ], [
            'name.required' => 'กรุณากรอกชื่อสถานที่',
            'name.max' => 'ชื่อต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อนี้มีอยู่ในระบบแล้ว',
        ]);



        $m->update($validated);

        
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        MpPlace::findOrFail($id)->delete();


        return response()->json(['message' => 'Deleted']);
    }
}
