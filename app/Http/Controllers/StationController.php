<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StationController extends Controller
{
    public function index(Request $request ){
        $query = Station::with('connectorType');
        
        if($request->has('connector_type_id')){
            $query->where('connector_type_id',$request->connector_type_id);
        }
        if($request->has('min_power')){
            $query->where('power_kw', '>=', $request->min_power);
        }
        if ($request->has('available') && $request->available == 'true') {
            $query->where('status', 'available');
        }

        return response()->json($query->get(),200);

    }
    
}
