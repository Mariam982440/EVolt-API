<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Reservation;
use App\Models\Station;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateReservationStatus implements ShouldQueue
{
    use  Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        protected $reservationId;

    public function __construct($reservationId)
    {
        $this->reservationId = $reservationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reservation = Reservation::find($this->reservationId);
        if ($reservation && $reservation->status === 'scheduled') {
    
            $reservation->update(['status' => 'completed']);

            // 2. Optionnel : On peut envoyer un log ou une notification ici
            \Log::info("La borne {$reservation->station_id} a été libérée automatiquement.");
        }
    }
}
