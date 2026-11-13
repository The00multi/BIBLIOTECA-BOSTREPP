<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Reader;
use App\Models\Reservation;
use App\Services\ReservationQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class LibraryController extends Controller
{
    public function dashboard()
    {
        $latestLoans = Loan::with(['reader', 'copy.book'])->latest('borrowed_at')->limit(4)->get();

        return view('admin.dashboard', [
            'totalLibros' => Book::count(),
            'totalLectores' => Reader::count(),
            'prestamosActivos' => Loan::whereNull('returned_at')->whereDate('due_at', '>=', today())->count(),
            'prestamosVencidos' => Loan::whereNull('returned_at')->whereDate('due_at', '<', today())->count(),
            'multasPendientes' => Fine::where('status', 'pending')->count(),
            'ultimosPrestamos' => $latestLoans->map(fn (Loan $loan) => [
                'id' => $loan->id,
                'lector' => $loan->reader->first_name.' '.$loan->reader->last_name,
                'libro' => $loan->copy->book->title,
                'ejemplar' => $loan->copy->code,
                'prestamo' => $loan->borrowed_at->format('d/m/Y'),
                'limite' => $loan->due_at->format('d/m/Y'),
                'devolucion' => $loan->returned_at?->format('d/m/Y'),
                'estado' => $loan->returned_at ? 'Devuelto' : ($loan->due_at->lt(today()) ? 'Vencido' : 'Activo'),
            ])->all(),
        ]);
    }

    public function catalog(Request $request)
    {
        $books = Book::query()->with('categories')->withCount([
            'copies as available_copies_count' => fn ($query) => $query->where('status', 'available'),
            'copies as total_copies_count',
        ])
            ->when($request->filled('buscar'), fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', '%'.$request->string('buscar')->toString().'%')
                ->orWhere('author', 'like', '%'.$request->string('buscar')->toString().'%')))
            ->when($request->filled('categoria'), fn ($query) => $query->whereHas('categories', fn ($query) => $query->where('name', $request->string('categoria')->toString())))
            ->when($request->boolean('disponible'), fn ($query) => $query->whereHas('copies', fn ($query) => $query->where('status', 'available')))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Book $book) => $this->bookData($book));

        $newArrivals = Book::query()->with('categories')->withCount([
            'copies as available_copies_count' => fn ($query) => $query->where('status', 'available'),
            'copies as total_copies_count',
        ])->where('is_new', true)->latest('created_at')->limit(8)->get()
            ->map(fn (Book $book) => $this->bookData($book));

        return view('catalogo.index', [
            'libros' => $books,
            'categorias' => Category::query()->orderBy('name')->pluck('name')->all(),
            'novedades' => $newArrivals->all(),
        ]);
    }

    public function showBook(Book $book)
    {
        $book->load(['categories', 'copies']);
        $book->loadCount([
            'copies as available_copies_count' => fn ($query) => $query->where('status', 'available'),
            'copies as total_copies_count',
        ]);
        $data = $this->bookData($book);
        $copies = $book->copies->map(fn (Copy $copy) => [
            'codigo' => $copy->code,
            'ubicacion' => $copy->location,
            'estado' => $this->copyStatusLabel($copy->status),
        ])->all();
        $related = Book::query()->with('categories')->withCount([
            'copies as available_copies_count' => fn ($query) => $query->where('status', 'available'),
            'copies as total_copies_count',
        ])->where('id', '<>', $book->id)->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $book->categories->pluck('id')))
            ->limit(4)->get()->map(fn (Book $item) => $this->bookData($item))->all();

        return view('catalogo.show', ['libro' => $data, 'ejemplares' => $copies, 'relacionados' => $related]);
    }

    public function reserve(Book $book, Request $request): RedirectResponse
    {
        $reader = $request->user()->reader;
        abort_unless($reader && $reader->status === 'active', 403, 'Tu cuenta no puede reservar libros.');
        DB::transaction(function () use ($book, $reader) {
            $lockedBook = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedBook->copies()->whereIn('status', ['available', 'loaned', 'reserved'])->exists(), 422, 'Este título no tiene ejemplares que puedan volver a circular.');
            abort_if($lockedBook->copies()->where('status', 'available')->exists(), 422, 'Este libro tiene ejemplares disponibles.');
            $alreadyReserved = $reader->reservations()->where('book_id', $book->id)->whereIn('status', ['pending', 'ready'])->exists();
            abort_if($alreadyReserved, 422, 'Ya tienes una reserva activa para este libro.');
            Reservation::create([
                'reader_id' => $reader->id,
                'book_id' => $book->id,
                'reserved_at' => today(),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('lector.reservas')->with('success', 'Reserva registrada.');
    }

    public function myLoans(Request $request)
    {
        $reader = $request->user()->reader;
        abort_unless($reader, 403);
        $loans = $reader->loans()->with('copy.book')->latest('borrowed_at')->paginate(10)->withQueryString()->through(fn (Loan $loan) => [
            'id' => $loan->id, 'libro' => $loan->copy->book->title, 'ejemplar' => $loan->copy->code,
            'prestamo' => $loan->borrowed_at->format('d/m/Y'), 'limite' => $loan->due_at->format('d/m/Y'),
            'devolucion' => $loan->returned_at?->format('d/m/Y'),
            'estado' => $loan->returned_at ? 'Devuelto' : ($loan->due_at->lt(today()) ? 'Vencido' : 'Activo'),
        ]);

        return view('lector.prestamos', ['prestamos' => $loans, 'reader' => $reader]);
    }

    public function myReservations(Request $request, ReservationQueue $reservationQueue)
    {
        $reservationQueue->releaseExpired();
        $reader = $request->user()->reader;
        abort_unless($reader, 403);
        $reservations = $reader->reservations()->with('book')->latest('reserved_at')->paginate(10)->withQueryString()->through(fn (Reservation $reservation) => [
            'id' => $reservation->id, 'libro' => $reservation->book->title,
            'reservado' => $reservation->reserved_at->format('d/m/Y'),
            'vence' => $reservation->expires_at?->format('d/m/Y') ?? 'Pendiente de disponibilidad',
            'estado' => ['pending' => 'Pendiente', 'ready' => 'Lista para retirar', 'completed' => 'Completada', 'cancelled' => 'Cancelada', 'expired' => 'Vencida'][$reservation->status] ?? $reservation->status,
        ]);

        return view('lector.reservas', ['reservas' => $reservations, 'reader' => $reader]);
    }

    public function cancelReservation(Reservation $reservation, Request $request, ReservationQueue $reservationQueue): RedirectResponse
    {
        abort_unless($reservation->reader_id === $request->user()->reader?->id, 403);
        $reservationQueue->cancel($reservation);

        return back()->with('success', 'Reserva cancelada.');
    }

    public function myFines(Request $request)
    {
        $reader = $request->user()->reader;
        abort_unless($reader, 403);
        $fines = Fine::with(['loan.copy.book'])->whereHas('loan', fn ($query) => $query->where('reader_id', $reader->id))->latest()->paginate(10)->withQueryString()->through(fn (Fine $fine) => [
            'id' => $fine->id, 'libro' => $fine->loan->copy->book->title, 'motivo' => $fine->reason,
            'monto' => (float) $fine->amount, 'estado' => $fine->status === 'paid' ? 'Pagada' : 'Pendiente',
        ]);
        $pending = Fine::where('status', 'pending')->whereHas('loan', fn ($query) => $query->where('reader_id', $reader->id))->sum('amount');

        return view('lector.multas', ['multas' => $fines, 'pendiente' => (float) $pending, 'reader' => $reader]);
    }

    public function books(Request $request)
    {
        $books = Book::query()->with('categories')->withCount([
            'copies as available_copies_count' => fn ($query) => $query->where('status', 'available'),
            'copies as total_copies_count',
        ])->when($request->filled('buscar'), fn ($query) => $query->where(function ($query) use ($request): void {
            $term = '%'.$request->string('buscar')->toString().'%';
            $query->where('title', 'like', $term)->orWhere('author', 'like', $term)->orWhere('isbn', 'like', $term);
        }))->orderBy('title')->paginate(15)->withQueryString()->through(fn (Book $book) => $this->bookData($book));

        return view('admin.libros', [
            'libros' => $books,
            'totalTitulos' => Book::count(),
            'totalEjemplares' => Copy::count(),
            'ejemplaresDisponibles' => Copy::where('status', 'available')->count(),
        ]);
    }

    public function createBook()
    {
        return view('admin.libro-form', ['libro' => null, 'categorias' => $this->categoryNames()]);
    }

    public function editBook(Book $book)
    {
        $book->load('categories');

        return view('admin.libro-form', [
            'libro' => $this->bookData($book),
            'categorias' => $this->categoryNames(),
        ]);
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $data = $this->validateBook($request);
        $names = $data['categories'] ?? [];
        unset($data['categories'], $data['cover']);
        $data['cover_path'] = $request->file('cover')?->store('covers', 'public');
        $book = DB::transaction(function () use ($data, $names) {
            $book = Book::create($data);
            $book->categories()->sync($this->categoryIds($names));

            return $book;
        });

        return redirect()->route('admin.libros')->with('success', 'Libro registrado correctamente.');
    }

    public function updateBook(Request $request, Book $book): RedirectResponse
    {
        $data = $this->validateBook($request, $book);
        $names = $data['categories'] ?? [];
        unset($data['categories'], $data['cover']);
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }
        DB::transaction(function () use ($book, $data, $names) {
            $book->update($data);
            $book->categories()->sync($this->categoryIds($names));
        });

        return redirect()->route('admin.libros')->with('success', 'Libro actualizado correctamente.');
    }

    public function deleteBook(Book $book): RedirectResponse
    {
        if ($book->copies()->exists() || $book->reservations()->exists()) {
            return back()->with('error', 'No se puede eliminar un libro que ya tiene ejemplares o reservas.');
        }
        $book->delete();

        return back()->with('success', 'Libro eliminado.');
    }

    public function readers(Request $request)
    {
        $readers = Reader::query()->when($request->filled('buscar'), fn ($query) => $query->where(function ($query) use ($request): void {
            $term = '%'.$request->string('buscar')->toString().'%';
            $query->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)
                ->orWhere('card_number', 'like', $term)->orWhere('document_number', 'like', $term)->orWhere('email', 'like', $term);
        }))->orderBy('last_name')->orderBy('first_name')->paginate(15)->withQueryString()
            ->through(fn (Reader $reader) => $this->readerData($reader));

        return view('admin.lectores', [
            'lectores' => $readers,
            'lectoresTotal' => Reader::count(),
            'lectoresActivos' => Reader::where('status', 'active')->count(),
            'lectoresSuspendidos' => Reader::where('status', 'suspended')->count(),
        ]);
    }

    public function createReader()
    {
        return view('admin.lector-form', ['lector' => null]);
    }

    public function editReader(Reader $reader)
    {
        return view('admin.lector-form', ['lector' => $this->readerData($reader)]);
    }

    public function storeReader(Request $request): RedirectResponse
    {
        $data = $this->validateReader($request);

        if ($request->filled('email')) {
            $request->validate(['password' => ['required', 'confirmed', Password::min(8)]]);
        }

        DB::transaction(function () use ($data, $request): void {
            $user = $data['email'] ? User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => $request->input('password'),
                'role' => 'reader',
            ]) : null;

            Reader::create([...$data, 'user_id' => $user?->id]);
        });

        return redirect()->route('admin.lectores')->with('success', 'Lector registrado correctamente.');
    }

    public function updateReader(Request $request, Reader $reader): RedirectResponse
    {
        $data = $this->validateReader($request, $reader);
        DB::transaction(function () use ($reader, $data) {
            $reader->update($data);
            if ($reader->user) {
                $reader->user->update(['name' => $data['first_name'].' '.$data['last_name'], 'email' => $data['email']]);
            }
        });

        return redirect()->route('admin.lectores')->with('success', 'Lector actualizado correctamente.');
    }

    public function deleteReader(Reader $reader): RedirectResponse
    {
        if ($reader->loans()->exists() || $reader->reservations()->exists()) {
            return back()->with('error', 'No se puede eliminar un lector con préstamos o reservas registrados.');
        }
        DB::transaction(function () use ($reader) {
            $user = $reader->user;
            $reader->delete();
            $user?->delete();
        });

        return back()->with('success', 'Lector eliminado.');
    }

    public function copies(Request $request)
    {
        $copies = Copy::with('book')->when($request->filled('buscar'), fn ($query) => $query->where(function ($query) use ($request): void {
            $term = '%'.$request->string('buscar')->toString().'%';
            $query->where('code', 'like', $term)->orWhere('location', 'like', $term)
                ->orWhereHas('book', fn ($query) => $query->where('title', 'like', $term));
        }))->orderBy('code')->paginate(15)->withQueryString()->through(fn (Copy $copy) => [
            'id' => $copy->id, 'codigo' => $copy->code, 'libro' => $copy->book->title,
            'ubicacion' => $copy->location, 'estado' => $this->copyStatusLabel($copy->status), 'estadoValor' => $copy->status,
        ]);
        $books = Book::orderBy('title')->get()->map(fn (Book $book) => ['id' => $book->id, 'titulo' => $book->title])->all();

        return view('admin.ejemplares', [
            'ejemplares' => $copies,
            'libros' => $books,
            'ejemplaresTotal' => Copy::count(),
            'disponiblesTotal' => Copy::where('status', 'available')->count(),
            'prestadosTotal' => Copy::where('status', 'loaned')->count(),
            'incidenciasTotal' => Copy::whereIn('status', ['damaged', 'lost'])->count(),
        ]);
    }

    public function storeCopy(Request $request, ReservationQueue $reservationQueue): RedirectResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'code' => ['required', 'string', 'max:255', 'unique:copies,code'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['available', 'damaged', 'lost'])],
        ]);
        DB::transaction(function () use ($data, $reservationQueue): void {
            $copy = Copy::create($data);
            if ($copy->status === 'available') {
                $reservationQueue->assignNext($copy);
            }
        });

        return back()->with('success', 'Ejemplar registrado correctamente.');
    }

    public function deleteCopy(Copy $copy): RedirectResponse
    {
        if ($copy->loans()->exists() || $copy->reservations()->where('status', 'ready')->exists()) {
            return back()->with('error', 'No se puede eliminar un ejemplar con historial de préstamos o una reserva lista para retirar.');
        }
        $copy->delete();

        return back()->with('success', 'Ejemplar eliminado.');
    }

    public function updateCopy(Request $request, Copy $copy, ReservationQueue $reservationQueue): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['available', 'damaged', 'lost'])],
        ]);

        DB::transaction(function () use ($copy, $data, $reservationQueue): void {
            $lockedCopy = Copy::query()->whereKey($copy->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedCopy->status !== 'loaned', 422, 'Un ejemplar prestado solo cambia de estado al registrarse su devolución.');

            if ($lockedCopy->status === 'reserved' && $data['status'] !== 'available') {
                $hold = Reservation::query()
                    ->where('copy_id', $lockedCopy->id)
                    ->where('status', 'ready')
                    ->lockForUpdate()
                    ->first();
                $hold?->update(['copy_id' => null, 'status' => 'pending', 'expires_at' => null]);
            } elseif ($lockedCopy->status === 'reserved' && $data['status'] === 'available') {
                abort(422, 'Cancela primero la reserva que tiene asignado este ejemplar.');
            }

            $lockedCopy->update(['status' => $data['status']]);

            if ($data['status'] === 'available') {
                $reservationQueue->assignNext($lockedCopy);
            }
        }, attempts: 3);

        return back()->with('success', 'Estado del ejemplar actualizado.');
    }

    public function loans(Request $request)
    {
        $loans = Loan::with(['reader', 'copy.book'])
            ->when($request->filled('buscar'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('buscar')->toString().'%';
                $query->whereHas('reader', fn ($query) => $query->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)->orWhere('card_number', 'like', $term))
                    ->orWhereHas('copy', fn ($query) => $query->where('code', 'like', $term)->orWhereHas('book', fn ($query) => $query->where('title', 'like', $term)));
            }))
            ->when($request->input('estado') === 'active', fn ($query) => $query->whereNull('returned_at')->whereDate('due_at', '>=', today()))
            ->when($request->input('estado') === 'overdue', fn ($query) => $query->whereNull('returned_at')->whereDate('due_at', '<', today()))
            ->when($request->input('estado') === 'returned', fn ($query) => $query->whereNotNull('returned_at'))
            ->latest('borrowed_at')->paginate(15)->withQueryString()->through(fn (Loan $loan) => $this->loanData($loan));

        return view('admin.prestamos', [
            'prestamos' => $loans,
            'prestamosTotal' => Loan::count(),
            'prestamosActivos' => Loan::whereNull('returned_at')->whereDate('due_at', '>=', today())->count(),
            'prestamosVencidos' => Loan::whereNull('returned_at')->whereDate('due_at', '<', today())->count(),
            'prestamosDevueltos' => Loan::whereNotNull('returned_at')->count(),
        ]);
    }

    public function newLoan(ReservationQueue $reservationQueue)
    {
        $reservationQueue->releaseExpired();
        $readers = Reader::where('status', 'active')->orderBy('last_name')->get()->map(fn (Reader $reader) => $this->readerData($reader))->all();
        $copies = Copy::with(['book', 'reservations' => fn ($query) => $query->where('status', 'ready')->with('reader')])
            ->whereIn('status', ['available', 'reserved'])->orderBy('code')->get()->map(fn (Copy $copy) => [
                'id' => $copy->id, 'codigo' => $copy->code, 'libro' => $copy->book->title,
                'reserved_reader_id' => $copy->reservations->first()?->reader_id,
                'reserved_reader' => $copy->reservations->first() ? $copy->reservations->first()->reader->first_name.' '.$copy->reservations->first()->reader->last_name : null,
            ])->all();

        return view('admin.prestamo-nuevo', ['lectores' => $readers, 'ejemplares' => $copies]);
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reader_id' => ['required', 'exists:readers,id'],
            'copy_id' => ['required', 'exists:copies,id'],
            'due_at' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.today()->addDays(config('biblioteca.loan_period_days', 14))->toDateString()],
        ]);

        DB::transaction(function () use ($data) {
            $reader = Reader::whereKey($data['reader_id'])->lockForUpdate()->firstOrFail();
            abort_unless($reader->status === 'active', 422, 'El lector no está activo.');
            $activeLoans = Loan::query()->where('reader_id', $reader->id)->whereNull('returned_at');
            abort_if($activeLoans->count() >= config('biblioteca.max_active_loans', 5), 422, 'El lector ya alcanzó el máximo de préstamos activos.');
            abort_if($activeLoans->whereDate('due_at', '<', today())->exists(), 422, 'El lector debe devolver sus préstamos vencidos antes de recibir otro.');
            abort_if(Fine::query()->where('status', 'pending')->whereHas('loan', fn ($query) => $query->where('reader_id', $reader->id))->exists(), 422, 'El lector tiene multas pendientes.');
            $copy = Copy::whereKey($data['copy_id'])->lockForUpdate()->firstOrFail();
            $hold = Reservation::where('copy_id', $copy->id)->where('status', 'ready')->lockForUpdate()->first();
            if ($copy->status === 'reserved') {
                abort_unless($hold && (! $hold->expires_at || $hold->expires_at->gte(today())), 422, 'El plazo de esta reserva terminó; actualiza la lista de préstamos.');
                abort_unless($hold && $hold->reader_id === $reader->id, 422, 'Este ejemplar está reservado para otro lector.');
            } else {
                abort_unless($copy->status === 'available' && ! $hold, 422, 'El ejemplar ya no está disponible.');
            }
            $copy->update(['status' => 'loaned']);
            Loan::create(['reader_id' => $reader->id, 'copy_id' => $copy->id, 'borrowed_at' => today(), 'due_at' => $data['due_at']]);
            $hold?->update(['status' => 'completed', 'expires_at' => null]);
        });

        return redirect()->route('admin.prestamos')->with('success', 'Préstamo registrado correctamente.');
    }

    public function returnLoan(Request $request, Loan $loan, ReservationQueue $reservationQueue): RedirectResponse
    {
        $data = $request->validate(['copy_status' => ['required', Rule::in(['available', 'damaged', 'lost'])]]);
        DB::transaction(function () use ($loan, $data, $reservationQueue): void {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();
            abort_if($loan->returned_at, 422, 'Este préstamo ya fue devuelto.');
            $copy = Copy::whereKey($loan->copy_id)->lockForUpdate()->firstOrFail();
            $loan->update(['returned_at' => today(), 'status' => 'returned']);
            $copy->update(['status' => $data['copy_status']]);
            if ($data['copy_status'] === 'available') {
                $reservationQueue->assignNext($copy);
            }
            $daysLate = $loan->due_at->lt(today()) ? (int) $loan->due_at->diffInDays(today()) : 0;
            if ($daysLate > 0) {
                Fine::create([
                    'loan_id' => $loan->id,
                    'reason' => 'Retraso ('.$daysLate.' '.($daysLate === 1 ? 'día' : 'días').')',
                    'amount' => $daysLate * config('biblioteca.fine_per_day', 0.50),
                    'status' => 'pending',
                ]);
            }
        });

        return back()->with('success', 'Devolución registrada.');
    }

    public function fines(Request $request)
    {
        $fines = Fine::with(['loan.reader', 'loan.copy.book'])
            ->when($request->filled('buscar'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('buscar')->toString().'%';
                $query->where('reason', 'like', $term)
                    ->orWhereHas('loan.reader', fn ($query) => $query->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)->orWhere('card_number', 'like', $term))
                    ->orWhereHas('loan.copy.book', fn ($query) => $query->where('title', 'like', $term));
            }))
            ->when($request->input('estado') === 'pending', fn ($query) => $query->where('status', 'pending'))
            ->when($request->input('estado') === 'paid', fn ($query) => $query->where('status', 'paid'))
            ->latest()->paginate(15)->withQueryString()->through(fn (Fine $fine) => [
                'id' => $fine->id,
                'lector' => $fine->loan->reader->first_name.' '.$fine->loan->reader->last_name,
                'libro' => $fine->loan->copy->book->title,
                'motivo' => $fine->reason,
                'monto' => (float) $fine->amount,
                'estado' => $fine->status === 'paid' ? 'Pagada' : 'Pendiente',
            ]);

        return view('admin.multas', [
            'multas' => $fines,
            'pendienteTotal' => (float) Fine::where('status', 'pending')->sum('amount'),
            'pendientesTotal' => Fine::where('status', 'pending')->count(),
            'cobradaTotal' => (float) Fine::where('status', 'paid')->sum('amount'),
            'cobradasTotal' => Fine::where('status', 'paid')->count(),
            'multasTotal' => Fine::count(),
        ]);
    }

    public function reservations(Request $request, ReservationQueue $reservationQueue)
    {
        $reservationQueue->releaseExpired();
        $reservations = Reservation::with(['reader', 'book', 'copy'])
            ->when($request->filled('buscar'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('buscar')->toString().'%';
                $query->whereHas('reader', fn ($query) => $query->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)->orWhere('card_number', 'like', $term))
                    ->orWhereHas('book', fn ($query) => $query->where('title', 'like', $term));
            }))
            ->when($request->filled('estado'), fn ($query) => $query->where('status', $request->string('estado')->toString()))
            ->latest('reserved_at')->paginate(15)->withQueryString()->through(fn (Reservation $reservation) => [
                'id' => $reservation->id,
                'lector' => $reservation->reader->first_name.' '.$reservation->reader->last_name,
                'carnet' => $reservation->reader->card_number,
                'libro' => $reservation->book->title,
                'ejemplar' => $reservation->copy?->code,
                'reservado' => $reservation->reserved_at->format('d/m/Y'),
                'vence' => $reservation->expires_at?->format('d/m/Y'),
                'estadoValor' => $reservation->status,
                'estado' => ['pending' => 'Pendiente', 'ready' => 'Lista para retirar', 'completed' => 'Completada', 'cancelled' => 'Cancelada', 'expired' => 'Vencida'][$reservation->status] ?? $reservation->status,
            ]);

        return view('admin.reservas', ['reservas' => $reservations]);
    }

    public function cancelAdminReservation(Reservation $reservation, ReservationQueue $reservationQueue): RedirectResponse
    {
        $reservationQueue->cancel($reservation);

        return back()->with('success', 'Reserva cancelada.');
    }

    public function payFine(Fine $fine): RedirectResponse
    {
        if ($fine->status === 'pending') {
            $fine->update(['status' => 'paid', 'paid_at' => today()]);
        }

        return back()->with('success', 'Pago de multa registrado.');
    }

    private function validateBook(Request $request, ?Book $book = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book?->id)],
            'publication_year' => ['nullable', 'integer', 'between:1000,'.(date('Y') + 1)],
            'categories' => ['array'],
            'categories.*' => ['required', 'string', 'max:100'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_new' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]) + ['is_new' => $request->boolean('is_new'), 'is_featured' => $request->boolean('is_featured')];
    }

    private function validateReader(Request $request, ?Reader $reader = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'max:255', Rule::unique('readers', 'card_number')->ignore($reader?->id)],
            'document_number' => ['nullable', 'string', 'max:255', Rule::unique('readers', 'document_number')->ignore($reader?->id)],
            'email' => [Rule::requiredIf($reader?->user_id !== null), 'nullable', 'email', 'max:255', Rule::unique('readers', 'email')->ignore($reader?->id), Rule::unique('users', 'email')->ignore($reader?->user_id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);
    }

    private function categoryIds(array $names): array
    {
        return collect($names)->unique()->map(fn ($name) => Category::firstOrCreate(['name' => $name])->id)->all();
    }

    private function categoryNames(): array
    {
        $names = Category::orderBy('name')->pluck('name')->all();

        return $names ?: ['Novela', 'Cuento', 'Poesía', 'Realismo mágico', 'Infantil', 'Clásicos', 'Ensayo', 'Historia', 'Ciencia'];
    }

    private function bookData(Book $book): array
    {
        return [
            'id' => $book->id, 'titulo' => $book->title, 'autor' => $book->author,
            'editorial' => $book->publisher, 'isbn' => $book->isbn, 'anio' => $book->publication_year,
            'categorias' => $book->categories->pluck('name')->all(),
            'disponibles' => (int) ($book->available_copies_count ?? $book->copies()->where('status', 'available')->count()),
            'total' => (int) ($book->total_copies_count ?? $book->copies()->count()),
            'popular' => $book->is_featured, 'novedad' => $book->is_new, 'portada' => $book->cover_path,
        ];
    }

    private function readerData(Reader $reader): array
    {
        return [
            'id' => $reader->id, 'nombres' => $reader->first_name, 'apellidos' => $reader->last_name,
            'carnet' => $reader->card_number, 'documento' => $reader->document_number,
            'telefono' => $reader->phone, 'email' => $reader->email,
            'estado' => $reader->status === 'active' ? 'Activo' : 'Suspendido',
        ];
    }

    private function loanData(Loan $loan): array
    {
        return [
            'id' => $loan->id,
            'lector' => $loan->reader->first_name.' '.$loan->reader->last_name,
            'libro' => $loan->copy->book->title,
            'ejemplar' => $loan->copy->code,
            'prestamo' => $loan->borrowed_at->format('d/m/Y'),
            'limite' => $loan->due_at->format('d/m/Y'),
            'devolucion' => $loan->returned_at?->format('d/m/Y'),
            'estado' => $loan->returned_at ? 'Devuelto' : ($loan->due_at->lt(today()) ? 'Vencido' : 'Activo'),
        ];
    }

    private function copyStatusLabel(string $status): string
    {
        return ['available' => 'Disponible', 'loaned' => 'Prestado', 'reserved' => 'Reservado', 'damaged' => 'Dañado', 'lost' => 'Perdido'][$status] ?? $status;
    }
}
