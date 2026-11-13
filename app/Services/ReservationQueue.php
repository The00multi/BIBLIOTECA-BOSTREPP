<?php

namespace App\Services;

use App\Models\Copy;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationQueue
{
    public function assignNext(Copy $copy): void
    {
        DB::transaction(function () use ($copy): void {
            $lockedCopy = Copy::query()->whereKey($copy->id)->lockForUpdate()->firstOrFail();
            $this->assignNextLocked($lockedCopy);
        }, attempts: 3);
    }

    public function cancel(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation): void {
            $copyId = Reservation::query()->whereKey($reservation->id)->value('copy_id');
            $copy = $copyId === null
                ? null
                : Copy::query()->whereKey($copyId)->lockForUpdate()->first();

            $lockedReservation = Reservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($lockedReservation->status, ['pending', 'ready'], true), 422, 'Esta reserva ya no se puede cancelar.');
            abort_unless($lockedReservation->copy_id === $copyId, 409, 'La reserva cambió mientras se procesaba. Inténtalo de nuevo.');

            $lockedReservation->update(['status' => 'cancelled', 'copy_id' => null, 'expires_at' => null]);

            if ($copy?->status === 'reserved') {
                $copy->update(['status' => 'available']);
                $this->assignNextLocked($copy);
            }
        }, attempts: 3);
    }

    public function releaseExpired(): int
    {
        return DB::transaction(function (): int {
            $expiredReservations = Reservation::query()
                ->where('status', 'ready')
                ->whereDate('expires_at', '<', today())
                ->orderBy('id')
                ->get(['id', 'copy_id']);

            $released = 0;

            foreach ($expiredReservations as $candidate) {
                $copy = $candidate->copy_id === null
                    ? null
                    : Copy::query()->whereKey($candidate->copy_id)->lockForUpdate()->first();
                $reservation = Reservation::query()->whereKey($candidate->id)->lockForUpdate()->first();

                if (! $reservation || $reservation->status !== 'ready' || $reservation->expires_at?->gte(today())) {
                    continue;
                }

                if ($reservation->copy_id !== $candidate->copy_id) {
                    continue;
                }

                $reservation->update(['status' => 'expired', 'copy_id' => null]);
                $released++;

                if ($copy?->status === 'reserved') {
                    $copy->update(['status' => 'available']);
                    $this->assignNextLocked($copy);
                }
            }

            return $released;
        }, attempts: 3);
    }

    private function assignNextLocked(Copy $copy): void
    {
        if ($copy->status !== 'available') {
            return;
        }

        $reservation = Reservation::query()
            ->where('book_id', $copy->book_id)
            ->where('status', 'pending')
            ->orderBy('reserved_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if (! $reservation) {
            return;
        }

        $reservation->update([
            'copy_id' => $copy->id,
            'status' => 'ready',
            'expires_at' => Carbon::today()->addDays(3),
        ]);
        $copy->update(['status' => 'reserved']);
    }
}
