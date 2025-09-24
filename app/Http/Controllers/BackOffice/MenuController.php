<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpMenu;
use App\Models\MpPosition;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    // Menus DataTables server-side
    public function data(Request $request)
    {
        $query = MpMenu::query()->select(['menu_id','key_name','name','created_at']);
        return DataTables::of($query)
            ->filter(function($q) use ($request){
                if ($search = $request->input('search.value')) {
                    $q->where(function($qq) use ($search){
                        $qq->where('name','like',"%{$search}%")
                           ->orWhere('key_name','like',"%{$search}%");
                    });
                }
            })
            ->order(function($q) use ($request){
                // DataTables will send order; default to menu_id desc if none
                if (!$request->has('order')) {
                    $q->orderBy('menu_id','desc');
                }
            })
            ->toJson();
    }

    public function show($id)
    {
        return response()->json(MpMenu::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key_name' => ['required','max:50','unique:mp_menus,key_name'],
            'name' => ['required','max:100'],
        ], [
            'key_name.required' => 'กรุณากรอก Key',
            'key_name.max' => 'Key ต้องไม่เกิน 50 ตัวอักษร',
            'key_name.unique' => 'Key นี้ถูกใช้งานแล้ว',
            'name.required' => 'กรุณากรอกชื่อเมนู',
            'name.max' => 'ชื่อเมนูต้องไม่เกิน 100 ตัวอักษร',
        ]);
        $menu = MpMenu::create($validated);
        return response()->json(['message' => 'Created','id' => $menu->menu_id]);
    }

    public function update($id, Request $request)
    {
        $menu = MpMenu::findOrFail($id);
        $validated = $request->validate([
            'key_name' => ['required','max:50', Rule::unique('mp_menus','key_name')->ignore($menu->menu_id, 'menu_id')],
            'name' => ['required','max:100'],
        ], [
            'key_name.required' => 'กรุณากรอก Key',
            'key_name.max' => 'Key ต้องไม่เกิน 50 ตัวอักษร',
            'key_name.unique' => 'Key นี้ถูกใช้งานแล้ว',
            'name.required' => 'กรุณากรอกชื่อเมนู',
            'name.max' => 'ชื่อเมนูต้องไม่เกิน 100 ตัวอักษร',
        ]);
        $menu->update($validated);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        MpMenu::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // For position access management
    public function listAll()
    {
        return response()->json(MpMenu::orderBy('name')->get(['menu_id as id','name','key_name']));
    }

    public function positionAccess($positionId)
    {
        $pos = MpPosition::findOrFail($positionId);
        $ids = $pos->menus()->pluck('mp_menus.menu_id')->toArray();
        return response()->json(array_values($ids));
    }

    public function savePositionAccess($positionId, Request $request)
    {
        $pos = MpPosition::findOrFail($positionId);
        $menuIds = $request->input('menu_ids', []);
        if (!is_array($menuIds)) $menuIds = [];
        $menuIds = array_map('intval', $menuIds);
        $pos->menus()->sync($menuIds);
        if (function_exists('bumpMenuAccessVersion')) {
            bumpMenuAccessVersion();
        }
        return response()->json(['message' => 'Saved']);
    }
}
