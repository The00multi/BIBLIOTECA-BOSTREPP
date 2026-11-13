<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LibraryController::class, 'catalog'])->name('inicio');
Route::get('/libros/{book}', [LibraryController::class, 'showBook'])->name('libro');
Route::post('/libros/{book}/reservar', [LibraryController::class, 'reserve'])->middleware(['auth', 'role:reader'])->name('libro.reservar');
Route::view('/ingresar', 'auth.login')->name('login');
Route::post('/ingresar', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('login.procesar');
Route::view('/registro', 'auth.registro')->name('registro');
Route::post('/registro', [AuthController::class, 'register'])->middleware(['guest', 'throttle:register'])->name('registro.procesar');
Route::post('/salir', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('mi-cuenta')->name('lector.')->middleware(['auth', 'role:reader'])->controller(LibraryController::class)->group(function () {
    Route::get('/prestamos', 'myLoans')->name('prestamos');
    Route::get('/reservas', 'myReservations')->name('reservas');
    Route::post('/reservas/{reservation}/cancelar', 'cancelReservation')->name('reservas.cancelar');
    Route::get('/multas', 'myFines')->name('multas');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->controller(LibraryController::class)->group(function () {
    Route::post('/cerrar-sesion', [AuthController::class, 'logout'])->name('cerrar-sesion');

    Route::get('/', 'dashboard')->name('dashboard');

    Route::get('/libros', 'books')->name('libros');
    Route::get('/libros/nuevo', 'createBook')->name('libros.crear');
    Route::post('/libros', 'storeBook')->name('libros.guardar');
    Route::get('/libros/{book}/editar', 'editBook')->name('libros.editar');
    Route::put('/libros/{book}', 'updateBook')->name('libros.actualizar');
    Route::delete('/libros/{book}', 'deleteBook')->name('libros.eliminar');

    Route::get('/ejemplares', 'copies')->name('ejemplares');
    Route::post('/ejemplares', 'storeCopy')->name('ejemplares.guardar');
    Route::put('/ejemplares/{copy}', 'updateCopy')->name('ejemplares.actualizar');
    Route::delete('/ejemplares/{copy}', 'deleteCopy')->name('ejemplares.eliminar');

    Route::get('/lectores', 'readers')->name('lectores');
    Route::get('/lectores/nuevo', 'createReader')->name('lectores.crear');
    Route::post('/lectores', 'storeReader')->name('lectores.guardar');
    Route::get('/lectores/{reader}/editar', 'editReader')->name('lectores.editar');
    Route::put('/lectores/{reader}', 'updateReader')->name('lectores.actualizar');
    Route::delete('/lectores/{reader}', 'deleteReader')->name('lectores.eliminar');

    Route::get('/prestamos', 'loans')->name('prestamos');
    Route::get('/prestamos/nuevo', 'newLoan')->name('prestamos.nuevo');
    Route::post('/prestamos', 'storeLoan')->name('prestamos.guardar');
    Route::post('/prestamos/{loan}/devolucion', 'returnLoan')->name('prestamos.devolver');

    Route::get('/reservas', 'reservations')->name('reservas');
    Route::post('/reservas/{reservation}/cancelar', 'cancelAdminReservation')->name('reservas.cancelar');

    Route::get('/multas', 'fines')->name('multas');
    Route::post('/multas/{fine}/pago', 'payFine')->name('multas.pagar');
});
