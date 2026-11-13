<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Reader;
use App\Models\Reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = config('biblioteca.admin_email');
        $adminPassword = config('biblioteca.admin_password');
        if ($adminEmail && $adminPassword) {
            User::updateOrCreate(['email' => $adminEmail], [
                'name' => config('biblioteca.admin_name', 'Administración'),
                'password' => $adminPassword,
                'role' => 'admin',
            ]);
        }

        $catalog = [
            ['Cien años de soledad', 'Gabriel García Márquez', 'Sudamericana', '9780307474728', 1967, ['Novela', 'Realismo mágico'], true, false],
            ['El Principito', 'Antoine de Saint-Exupéry', 'Reynal & Hitchcock', '9780156012195', 1943, ['Infantil', 'Novela'], true, false],
            ['Don Quijote de la Mancha', 'Miguel de Cervantes', 'Francisco de Robles', '9788420412146', 1605, ['Novela', 'Clásicos'], false, false],
            ['La casa de los espíritus', 'Isabel Allende', 'Plaza & Janés', '9780553383805', 1982, ['Novela', 'Realismo mágico'], false, true],
            ['Pedro Páramo', 'Juan Rulfo', 'Fondo de Cultura Económica', '9786071602046', 1955, ['Novela', 'Realismo mágico', 'Clásicos'], false, true],
            ['Rayuela', 'Julio Cortázar', 'Sudamericana', '9788437604572', 1963, ['Novela', 'Clásicos'], true, false],
            ['Veinte poemas de amor y una canción desesperada', 'Pablo Neruda', 'Nascimento', '9788432217500', 1924, ['Poesía', 'Clásicos'], false, false],
            ['Las venas abiertas de América Latina', 'Eduardo Galeano', 'Siglo XXI', '9788432305337', 1971, ['Historia', 'Ensayo'], false, true],
        ];
        $books = [];
        foreach ($catalog as [$title, $author, $publisher, $isbn, $year, $categories, $featured, $new]) {
            $book = Book::updateOrCreate(['isbn' => $isbn], [
                'title' => $title, 'author' => $author, 'publisher' => $publisher,
                'isbn' => $isbn, 'publication_year' => $year,
                'is_featured' => $featured, 'is_new' => $new,
            ]);
            $categoryIds = collect($categories)->map(fn ($name) => Category::firstOrCreate(['name' => $name])->id)->all();
            $book->categories()->sync($categoryIds);
            $books[$title] = $book;
        }

        $readerRows = [
            ['Ana', 'Pérez', 'L-0001', '04512367-8', '7000-1111', 'active'],
            ['Luis', 'Gómez', 'L-0002', '03321456-2', '7000-2222', 'active'],
            ['María', 'López', 'L-0003', '05123789-0', '7000-3333', 'active'],
            ['Carlos', 'Ruiz', 'L-0004', '02987654-1', '7000-4444', 'active'],
            ['Sofía', 'Mejía', 'L-0005', '06543210-5', '7000-5555', 'suspended'],
        ];
        $readers = [];
        foreach ($readerRows as [$first, $last, $card, $document, $phone, $status]) {
            $readers[$first] = Reader::updateOrCreate(['card_number' => $card], [
                'first_name' => $first, 'last_name' => $last, 'card_number' => $card,
                'document_number' => $document, 'phone' => $phone, 'status' => $status,
            ]);
        }

        $copyRows = [
            ['CA-0001', 'Cien años de soledad', 'Sala principal, estante A-01', 'loaned'],
            ['CA-0002', 'Cien años de soledad', 'Sala principal, estante A-01', 'available'],
            ['CA-0003', 'Cien años de soledad', 'Sala principal, estante A-01', 'available'],
            ['CA-0004', 'Cien años de soledad', 'Sala principal, estante A-01', 'available'],
            ['EP-0001', 'El Principito', 'Sala infantil, estante B-02', 'loaned'],
            ['EP-0002', 'El Principito', 'Sala infantil, estante B-02', 'damaged'],
            ['DQ-0001', 'Don Quijote de la Mancha', 'Sala principal, estante A-04', 'available'],
            ['DQ-0002', 'Don Quijote de la Mancha', 'Sala principal, estante A-04', 'available'],
            ['DQ-0003', 'Don Quijote de la Mancha', 'Sala principal, estante A-04', 'loaned'],
            ['CE-0001', 'La casa de los espíritus', 'Sala principal, estante A-02', 'available'],
            ['PP-0001', 'Pedro Páramo', 'Sala principal, estante A-05', 'available'],
            ['PP-0002', 'Pedro Páramo', 'Sala principal, estante A-05', 'available'],
            ['RA-0001', 'Rayuela', 'Sala principal, estante A-05', 'loaned'],
            ['VP-0001', 'Veinte poemas de amor y una canción desesperada', 'Sala de poesía, estante C-01', 'available'],
            ['VP-0002', 'Veinte poemas de amor y una canción desesperada', 'Sala de poesía, estante C-01', 'available'],
            ['VP-0003', 'Veinte poemas de amor y una canción desesperada', 'Sala de poesía, estante C-01', 'available'],
            ['LV-0001', 'Las venas abiertas de América Latina', 'Sala principal, estante A-06', 'available'],
            ['LV-0002', 'Las venas abiertas de América Latina', 'Sala principal, estante A-06', 'loaned'],
        ];
        $copies = [];
        foreach ($copyRows as [$code, $title, $location, $status]) {
            $copies[$code] = Copy::updateOrCreate(['code' => $code], [
                'book_id' => $books[$title]->id, 'code' => $code,
                'location' => $location, 'status' => $status,
            ]);
        }

        $loanRows = [
            ['Ana', 'CA-0001', '2026-11-06', '2026-11-20', null],
            ['Luis', 'EP-0001', '2026-10-22', '2026-11-05', null],
            ['María', 'DQ-0003', '2026-11-01', '2026-11-15', null],
            ['Carlos', 'RA-0001', '2026-11-08', '2026-11-22', null],
            ['Luis', 'LV-0002', '2026-11-10', '2026-11-24', null],
            ['Carlos', 'CE-0001', '2026-10-10', '2026-10-24', '2026-10-23'],
            ['Ana', 'EP-0002', '2026-09-15', '2026-09-29', '2026-10-02'],
        ];
        $loans = [];
        foreach ($loanRows as [$reader, $copy, $borrowed, $due, $returned]) {
            $loan = Loan::updateOrCreate(['copy_id' => $copies[$copy]->id, 'borrowed_at' => $borrowed], [
                'reader_id' => $readers[$reader]->id, 'copy_id' => $copies[$copy]->id,
                'borrowed_at' => $borrowed, 'due_at' => $due, 'returned_at' => $returned,
                'status' => $returned ? 'returned' : 'active',
            ]);
            $loans[$reader.'-'.$copy] = $loan;
        }
        Fine::updateOrCreate(['loan_id' => $loans['Luis-EP-0001']->id, 'reason' => 'Retraso (6 días)'], ['amount' => 3.00, 'status' => 'pending']);
        Fine::updateOrCreate(['loan_id' => $loans['Ana-EP-0002']->id, 'reason' => 'Retraso (3 días)'], ['amount' => 1.50, 'status' => 'pending']);
        Fine::updateOrCreate(['loan_id' => $loans['Carlos-CE-0001']->id, 'reason' => 'Retraso (2 días)'], ['amount' => 1.00, 'status' => 'paid', 'paid_at' => '2026-10-25']);

        Reservation::updateOrCreate(
            ['reader_id' => $readers['Ana']->id, 'book_id' => $books['El Principito']->id, 'reserved_at' => '2026-11-09'],
            ['expires_at' => '2026-11-18', 'status' => 'pending'],
        );
    }
}
