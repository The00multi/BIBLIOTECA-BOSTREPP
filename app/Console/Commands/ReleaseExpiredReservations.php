<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\ReservationQueue;

#[Signature('reservations:release-expired')]
#[Description('Libera ejemplares con reservas vencidas y atiende la siguiente solicitud')]
class ReleaseExpiredReservations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ReservationQueue $reservationQueue): int
    {
        $released = $reservationQueue->releaseExpired();
        $this->info("Se liberaron {$released} reservas vencidas.");

        return self::SUCCESS;
    }
}
