<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpPosition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PositionController extends Controller
{
    public function list()
    {
        $items = MpPosition::orderBy('name')->get(['position_id as id','name']);
        return response()->json($items);
    }

    public function data(Request $request)
    {
        $query = MpPosition::query()->select(['position_id','name','created_at']);
        return DataTables::of($query)
            ->filter(function($q) use ($request){
                if ($search = $request->input('search.value')) {
                    $q->where('name', 'like', "%{$search}%");
                }
            })
            ->order(function($q) use ($request){
                if (!$request->has('order')) {
                    $q->orderBy('position_id','desc');
                }
            })
            ->toJson();
    }

    public function show($id)
    {
        return response()->json(MpPosition::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:100','unique:mp_positions,name'],
        ], [
            'name.required' => 'กรุณากรอกชื่อตำแหน่ง',
            'name.max' => 'ชื่อตำแหน่งต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อตำแหน่งนี้มีอยู่ในระบบแล้ว',
        ]);
        $pos = MpPosition::create($validated);
        return response()->json(['message' => 'Created','id' => $pos->position_id]);
    }

    public function update($id, Request $request)
    {
        $pos = MpPosition::findOrFail($id);
        $validated = $request->validate([
            'name' => ['required','max:100', Rule::unique('mp_positions','name')->ignore($pos->position_id, 'position_id')],
        ], [
            'name.required' => 'กรุณากรอกชื่อตำแหน่ง',
            'name.max' => 'ชื่อตำแหน่งต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อตำแหน่งนี้มีอยู่ในระบบแล้ว',
        ]);
        $pos->update($validated);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $pos = MpPosition::findOrFail($id);
        // Detach pivot relations first to satisfy FK (mp_position_menus)
        // This avoids ORA-02292 when a position has menu access mapped.
        if (method_exists($pos, 'menus')) {
            $pos->menus()->detach();
        }
        $pos->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
