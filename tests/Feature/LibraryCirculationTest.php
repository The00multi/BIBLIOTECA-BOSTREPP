<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Loan;
use App\Models\Reader;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LibraryCirculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_queue_loan_return_and_late_fine_work_together(): void
    {
        Carbon::setTestNow('2026-11-12 10:00:00');
        config(['biblioteca.fine_per_day' => 0.50, 'biblioteca.loan_period_days' => 14]);

        $book = Book::create(['title' => 'Libro de prueba', 'author' => 'Autora']);
        $copy = Copy::create(['book_id' => $book->id, 'code' => 'T-001', 'status' => 'loaned']);
        $otherReader = $this->reader('Otro');
        $reader = $this->reader('Lector');
        $loan = Loan::create([
            'reader_id' => $otherReader->id,
            'copy_id' => $copy->id,
            'borrowed_at' => '2026-10-01',
            'due_at' => '2026-11-10',
            'status' => 'active',
        ]);
        $reservation = Reservation::create([
            'reader_id' => $reader->id,
            'book_id' => $book->id,
            'reserved_at' => '2026-11-01',
            'status' => 'pending',
        ]);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password', 'role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.prestamos.devolver', $loan), ['copy_status' => 'available'])
            ->assertRedirect();

        $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'ready', 'copy_id' => $copy->id]);
        $this->assertDatabaseHas('copies', ['id' => $copy->id, 'status' => 'reserved']);
        $this->assertDatabaseHas('fines', ['loan_id' => $loan->id, 'reason' => 'Retraso (2 días)', 'amount' => 1.00]);

        $this->post(route('admin.prestamos.guardar'), [
            'reader_id' => $reader->id,
            'copy_id' => $copy->id,
            'due_at' => '2026-11-20',
        ])->assertRedirect(route('admin.prestamos'));

        $newLoan = Loan::query()->where('reader_id', $reader->id)->firstOrFail();
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'completed']);
        $this->assertDatabaseHas('copies', ['id' => $copy->id, 'status' => 'loaned']);

        Carbon::setTestNow('2026-11-23 10:00:00');
        $this->post(route('admin.prestamos.devolver', $newLoan), ['copy_status' => 'available'])
            ->assertRedirect();

        $this->assertDatabaseHas('fines', ['loan_id' => $newLoan->id, 'reason' => 'Retraso (3 días)', 'amount' => 1.50]);
        Carbon::setTestNow();
    }

    public function test_reader_cannot_reserve_a_book_with_only_damaged_or_lost_copies(): void
    {
        $book = Book::create(['title' => 'Sin ejemplares circulantes', 'author' => 'Autora']);
        Copy::create(['book_id' => $book->id, 'code' => 'D-001', 'status' => 'damaged']);
        Copy::create(['book_id' => $book->id, 'code' => 'P-001', 'status' => 'lost']);
        $reader = $this->reader('Lector');

        $this->actingAs($reader->user)
            ->post(route('libro.reservar', $book))
            ->assertUnprocessable();

        $this->assertDatabaseCount('reservations', 0);
    }

    private function reader(string $name): Reader
    {
        $user = User::create([
            'name' => $name,
            'email' => str($name)->lower()->ascii().'@example.test',
            'password' => 'password',
            'role' => 'reader',
        ]);

        return Reader::create([
            'user_id' => $user->id,
            'first_name' => $name,
            'last_name' => 'Prueba',
            'card_number' => 'L-'.str($name)->upper()->ascii(),
            'status' => 'active',
        ]);
    }
}
