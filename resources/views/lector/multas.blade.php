@extends('layouts.publico')

@section('titulo', 'Mis multas')

@section('contenido')
<div class="cuenta-layout">
    @include('lector._menu')
    <div class="cuenta-contenido">
@if ($pendiente > 0)
    <div class="alert alert-warning d-flex align-items-start gap-2" role="status">
        <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
        <div>
            Tienes <strong>${{ number_format($pendiente, 2) }}</strong> en multas pendientes.
            Págalas en el mostrador de la biblioteca para poder pedir nuevos préstamos.
        </div>
    </div>
@endif

<section class="cuenta-panel" aria-labelledby="titulo-mis-multas">
    <header class="cuenta-panel-cabecera">
        <div class="cuenta-panel-icono"><i class="bi bi-receipt" aria-hidden="true"></i></div>
        <div>
            <span class="cuenta-eyebrow">Tu actividad</span>
            <h2 id="titulo-mis-multas">Mis multas</h2>
            <p>Consulta el motivo, el monto y si ya fueron pagadas.</p>
        </div>
        <span class="cuenta-total">
            {{ $pendiente > 0 ? 'Pendiente: $'.number_format($pendiente, 2) : 'Sin deudas' }}
        </span>
    </header>

    <div class="lista-reservas">
        @forelse ($multas as $m)
        <article class="reserva-fila">
            <div class="reserva-icono"><i class="bi bi-receipt" aria-hidden="true"></i></div>
            <div class="reserva-libro">
                <span class="reserva-etiqueta">Multa por</span>
                <h3>{{ $m['libro'] }}</h3>
            </div>
            <div class="reserva-fecha">
                <span>Motivo</span>
                <strong>{{ $m['motivo'] }}</strong>
            </div>
            <div class="reserva-fecha">
                <span>Monto</span>
                <strong>${{ number_format($m['monto'], 2) }}</strong>
            </div>
            <div class="reserva-estado">
                @include('partials.estado', ['estado' => $m['estado']])
            </div>
        </article>
        @empty
        <div class="cuenta-vacia">
            <i class="bi bi-emoji-smile" aria-hidden="true"></i>
            <h3>No tienes multas</h3>
            <p>Devuelve tus libros a tiempo y mantén tu cuenta al día.</p>
            <a href="{{ route('lector.prestamos') }}" class="btn btn-primary">Ver mis préstamos</a>
        </div>
        @endforelse
    </div>
    <div class="p-3">{{ $multas->links('pagination::bootstrap-5') }}</div>
</section>
</div>
</div>
@endsection
