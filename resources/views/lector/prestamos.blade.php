@extends('layouts.publico')

@section('titulo', 'Mis préstamos')

@section('contenido')
<div class="cuenta-layout">
    @include('lector._menu')
    <div class="cuenta-contenido">
<section class="cuenta-panel" aria-labelledby="titulo-mis-prestamos">
    <header class="cuenta-panel-cabecera">
        <div class="cuenta-panel-icono"><i class="bi bi-journal-bookmark" aria-hidden="true"></i></div>
        <div>
            <span class="cuenta-eyebrow">Tu actividad</span>
            <h2 id="titulo-mis-prestamos">Mis préstamos</h2>
            <p>Revisa tus libros y las fechas de devolución.</p>
        </div>
        <span class="cuenta-total">{{ $prestamos->total() }} {{ $prestamos->total() === 1 ? 'préstamo' : 'préstamos' }}</span>
    </header>

    <div class="lista-reservas">
        @forelse ($prestamos as $p)
        <article class="reserva-fila">
            <div class="reserva-icono"><i class="bi bi-book" aria-hidden="true"></i></div>
            <div class="reserva-libro">
                <span class="reserva-etiqueta">Ejemplar {{ $p['ejemplar'] }}</span>
                <h3>{{ $p['libro'] }}</h3>
            </div>
            <div class="reserva-fecha">
                <span>Prestado</span>
                <strong>{{ $p['prestamo'] }}</strong>
            </div>
            <div class="reserva-fecha">
                <span>{{ $p['devolucion'] ? 'Devuelto el' : 'Devolver antes del' }}</span>
                <strong class="{{ $p['estado'] === 'Vencido' ? 'text-danger' : '' }}">{{ $p['devolucion'] ?? $p['limite'] }}</strong>
            </div>
            <div class="reserva-estado">
                @include('partials.estado', ['estado' => $p['estado']])
            </div>
        </article>
        @empty
        <div class="cuenta-vacia">
            <i class="bi bi-journal-plus" aria-hidden="true"></i>
            <h3>Aún no tienes préstamos</h3>
            <p>Cuando te lleves un libro de la biblioteca, aquí verás cuándo debes devolverlo.</p>
            <a href="{{ route('inicio') }}" class="btn btn-primary">Explorar catálogo</a>
        </div>
        @endforelse
    </div>
    <div class="p-3">{{ $prestamos->links('pagination::bootstrap-5') }}</div>
</section>
</div>
</div>
@endsection
