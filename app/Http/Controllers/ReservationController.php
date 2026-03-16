<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Station;
use Carbon\Carbon;
use App\Jobs\UpdateReservationStatus;


class ReservationController extends Controller
{
    public function index($request){
        $reservations = $request->user()->reservations()
            ->with('station.connectorType')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json($reservations);
    }
    public function store(Request $request)
    {
        $request->validate([
            'station_id' => 'required|exists:stations,id',
            'start_time' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:240', 
        ]);

        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes($request->duration_minutes);
        $stationId = $request->station_id;

        $station = Station::findOrFail($stationId);
        if ($station->status === 'maintenance') {
            return response()->json(['message' => 'Cette borne est actuellement en maintenance.'], 422);
        }

        // une réservation existe si : (début1 < fin2) et (fin1 > début2)
        $conflict = Reservation::where('station_id', $stationId)
            ->where('status', 'scheduled') // seulement les réservations confirmées
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'La borne est déjà réservée sur ce créneau.'], 409);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'station_id' => $stationId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $request->duration_minutes,
            'status' => 'scheduled'
        ]);

        // on calcule le délai : c'est l'heure de fin (end_time)
        UpdateReservationStatus::dispatch($reservation->id)
            ->delay($reservation->end_time);

        return response()->json([
            'message' => 'Réservation réussie !',
            'data' => $reservation->load('station')
        ], 201);
    }

   
    public function destroy(Request $request, $id)
    {
        $reservation = $request->user()->reservations()->findOrFail($id);

        // on peut seulement annuler si ça n'a pas encore commencé (optionnel)
        if ($reservation->start_time->isPast()) {
            return response()->json(['message' => 'Impossible d\'annuler une session déjà commencée ou passée.'], 422);
        }

        $reservation->delete(); // ou passer le status à cancelled

        return response()->json(['message' => 'Réservation annulée avec succès.']);
    }
    
    public function update(Request $request, $id)
    {
        $reservation = $request->user()->reservations()->findOrFail($id);

        $request->validate([
            'start_time' => 'sometimes|date|after:now',
            'duration_minutes' => 'sometimes|integer|min:15|max:240',
        ]);

        $startTime = $request->has('start_time') ? Carbon::parse($request->start_time) : $reservation->start_time;
        $duration = $request->has('duration_minutes') ? $request->duration_minutes : $reservation->duration_minutes;
        $endTime = $startTime->copy()->addMinutes($duration);

        // verification des conflits (en excluant la réservation actuelle elle-même)
        $conflict = Reservation::where('station_id', $reservation->station_id)
            ->where('id', '!=', $id) // ne pas se comparer à soi-même
            ->where('status', 'scheduled')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Ce nouveau créneau est déjà pris.'], 409);
        }

        // mise à jour
        $reservation->update([
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $duration
        ]);

        return response()->json([
            'message' => 'Réservation mise à jour avec succès',
            'data' => $reservation
        ]);
    }
}
