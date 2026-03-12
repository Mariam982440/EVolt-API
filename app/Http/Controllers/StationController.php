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
    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'connector_type_id' => 'required|exists:connector_types,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'power_kw' => 'required|integer|min:1',
        ]);

        $station = Station::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . rand(100, 999), // Slug unique
            'connector_type_id' => $validated['connector_type_id'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'power_kw' => $validated['power_kw'],
            'status' => 'available',
        ]);
        return response()->json($station,201);
    }
    
    public function show($id){
        $station = Station::with('connectorType')->findOrFail($id);
        return response()->json($station, 200);
    }

    public function update(Request $request, $id)
    {
        $station = Station::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'connector_type_id' => 'sometimes|exists:connector_types,id',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'power_kw' => 'sometimes|integer',
            'status' => 'sometimes|in:available,maintenance',
        ]);

        $station->update($validated);

        return response()->json($station, 200);
    }
}
