<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function list()
    {
        $items = MpDepartment::orderBy('name')->get(['dept_id as id','name']);
        return response()->json($items);
    }

    public function data(Request $request)
    {
        $query = MpDepartment::query()->select(['dept_id','name','created_at']);
        return DataTables::of($query)
            ->filter(function($q) use ($request){
                if ($search = $request->input('search.value')) {
                    $q->where('name', 'like', "%{$search}%");
                }
            })
            ->order(function($q) use ($request){
                if (!$request->has('order')) {
                    $q->orderBy('dept_id','desc');
                }
            })
            ->toJson();
    }

    public function show($id)
    {
        return response()->json(
            MpDepartment::findOrFail($id)
        );
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:100','unique:mp_departments,name'],
        ], [
            'name.required' => 'กรุณากรอกชื่อแผนก',
            'name.max' => 'ชื่อแผนกต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อแผนกนี้มีอยู่ในระบบแล้ว',
        ]);



        $dept = MpDepartment::create($validated);


        return response()->json(['message' => 'Created','id' => $dept->dept_id]);
    }

    public function update($id, Request $request)
    {
        $dept = MpDepartment::findOrFail($id);


        $validated = $request->validate([
            'name' => ['required','max:100', Rule::unique('mp_departments','name')->ignore($dept->dept_id, 'dept_id')],
        ], [
            'name.required' => 'กรุณากรอกชื่อแผนก',
            'name.max' => 'ชื่อแผนกต้องไม่เกิน 100 ตัวอักษร',
            'name.unique' => 'ชื่อแผนกนี้มีอยู่ในระบบแล้ว',
        ]);


        $dept->update($validated);


        
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {

        $dept = MpDepartment::findOrFail($id);

        $dept->delete();


        return response()->json(['message' => 'Deleted']);
    }
}
