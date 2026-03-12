<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;


class ReservationController extends Controller
{
    public function index($request){
        $reservations = $request->user()->reservations()
            ->with('station.connectorType')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json($reservations);
    }
    
}
