<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\MpPlace;
use App\Models\MpRoute;
use App\Models\MpRoutePlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class RoutePlaceController extends Controller
{
    public function data($routeId, Request $request)
    {
        $route = MpRoute::findOrFail($routeId);
        $q = MpRoutePlace::query()
            ->where('mp_route_places.route_id', $route->route_id)
            ->with(['place'])
            ->leftJoin('mp_places as p','p.place_id','=','mp_route_places.place_id')
            ->select(['mp_route_places.route_place_id','mp_route_places.sequence_no','mp_route_places.duration_min','mp_route_places.place_id','p.name as place_name']);

        return DataTables::of($q)
            ->order(function($q) use ($request){ if(!$request->has('order')) $q->orderBy('sequence_no'); })
            ->toJson();
    }

    public function store($routeId, Request $request)
    {
        $route = MpRoute::findOrFail($routeId);
        $maxSeq = (int) MpRoutePlace::where('route_id',$route->route_id)->max('sequence_no');
        $nextSeq = $maxSeq > 0 ? $maxSeq + 1 : 1;

        $validated = $request->validate([
            'place_id' => ['required','integer','exists:mp_places,place_id'],
            'duration_min' => ['required','integer'],
        ], [
            'place_id.required' => 'กรุณาเลือกจุดรับ–ส่ง',
            'place_id.integer' => 'ข้อมูลจุดรับ–ส่งไม่ถูกต้อง',
            'place_id.exists' => 'จุดรับ–ส่งที่เลือกไม่มีอยู่ในระบบ',
            'duration_min.required' => 'กรุณาระบุเวลา (นาที)',
            'duration_min.integer' => 'เวลาต้องเป็นตัวเลข',
        ]);

        
        if ($nextSeq === 1 && (int)$validated['duration_min'] !== 0) {
            return response()->json(['message' => 'แถวแรกต้องมีเวลาเป็น 0 นาที'], 422);
        }
        if ($nextSeq > 1 && (int)$validated['duration_min'] <= 0) {
            return response()->json(['message' => 'เวลาต้องมากกว่า 0 นาที'], 422);
        }

        $rp = MpRoutePlace::create([
            'route_id' => $route->route_id,
            'place_id' => (int)$validated['place_id'],
            'sequence_no' => $nextSeq,
            'duration_min' => (int)$validated['duration_min'],
        ]);

        return response()->json(['message' => 'Created','id' => $rp->route_place_id]);
    }

    public function update($routeId, $routePlaceId, Request $request)
    {
        $route = MpRoute::findOrFail($routeId);

        $rp = MpRoutePlace::where('route_id',$route->route_id)->findOrFail($routePlaceId);

        $validated = $request->validate([
            'place_id' => ['required','integer','exists:mp_places,place_id'],
            'duration_min' => ['required','integer'],
        ], [
            'place_id.required' => 'กรุณาเลือกจุดรับ–ส่ง',
            'place_id.integer' => 'ข้อมูลจุดรับ–ส่งไม่ถูกต้อง',
            'place_id.exists' => 'จุดรับ–ส่งที่เลือกไม่มีอยู่ในระบบ',
            'duration_min.required' => 'กรุณาระบุเวลา (นาที)',
            'duration_min.integer' => 'เวลาต้องเป็นตัวเลข',
        ]);

        
        if ((int)$rp->sequence_no === 1 && (int)$validated['duration_min'] !== 0) {
            return response()->json(['message' => 'แถวแรกต้องมีเวลาเป็น 0 นาที'], 422);
        }
        if ((int)$rp->sequence_no > 1 && (int)$validated['duration_min'] <= 0) {
            return response()->json(['message' => 'เวลาต้องมากกว่า 0 นาที'], 422);
        }

        $rp->update([
            'place_id' => (int)$validated['place_id'],
            'duration_min' => (int)$validated['duration_min'],
        ]);
        
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($routeId, $routePlaceId)
    {
        $route = MpRoute::findOrFail($routeId);
        $rp = MpRoutePlace::where('route_id',$route->route_id)->findOrFail($routePlaceId);
        $seq = (int)$rp->sequence_no;
        $rp->delete();

       
        $items = MpRoutePlace::where('route_id',$route->route_id)->orderBy('sequence_no')->get();
        $i = 1; foreach($items as $it){ $it->sequence_no = $i++; $it->save(); }
        return response()->json(['message' => 'Deleted']);
    }

    public function reorder($routeId, Request $request)
    {
        $route = MpRoute::findOrFail($routeId);
        $ids = array_values(array_map('intval', (array)$request->input('order', [])));
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'ข้อมูลลำดับไม่ถูกต้อง'], 422);
        }
 
        $validIds = MpRoutePlace::where('route_id',$route->route_id)->whereIn('route_place_id',$ids)->pluck('route_place_id')->toArray();
        if (count($validIds) !== count($ids)) {
            return response()->json(['message' => 'ข้อมูลไม่ตรงกับเส้นทาง'], 422);
        }

        $items = MpRoutePlace::where('route_id', $route->route_id)
            ->whereIn('route_place_id', $ids)
            ->get()
            ->keyBy('route_place_id');

      
        $firstId = (int)$ids[0];
        if (!isset($items[$firstId]) || (int)$items[$firstId]->duration_min !== 0) {
            return response()->json(['message' => 'แถวแรกต้องมีเวลาเป็น 0 นาที'], 422);
        }
        foreach ($ids as $index => $id) {
            if ($index === 0) continue; // already checked first
            if (!isset($items[$id]) || (int)$items[$id]->duration_min <= 0) {
                return response()->json(['message' => 'แถวถัดไปต้องมีเวลา > 0 นาที'], 422);
            }
        }

    
        DB::transaction(function() use ($ids, $route) {
            $cases = [];
            $bindings = [];
            $seq = 1;
            foreach ($ids as $id) {
                $cases[] = 'WHEN TO_NUMBER(?) THEN TO_NUMBER(?)';
                $bindings[] = (int)$id;  
                $bindings[] = $seq++;     
            }
            $inPlaceholders = implode(',', array_fill(0, count($ids), 'TO_NUMBER(?)'));
            $sql = 'UPDATE mp_route_places SET sequence_no = CASE route_place_id ' . implode(' ', $cases) . ' END '
                 . 'WHERE route_id = TO_NUMBER(?) AND route_place_id IN (' . $inPlaceholders . ')';

        
            $bindings[] = (int)$route->route_id;
            foreach ($ids as $id) { $bindings[] = (int)$id; }

            DB::statement($sql, $bindings);
        });
        return response()->json(['message' => 'Reordered']);
    }
}
