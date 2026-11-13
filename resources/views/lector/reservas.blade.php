@extends('layouts.publico')

@section('titulo', 'Mis reservas')

@section('contenido')
<div class="cuenta-layout">
    @include('lector._menu')
    <div class="cuenta-contenido">
<section class="cuenta-panel" aria-labelledby="titulo-mis-reservas">
    <header class="cuenta-panel-cabecera">
        <div class="cuenta-panel-icono"><i class="bi bi-bookmark-heart" aria-hidden="true"></i></div>
        <div>
            <span class="cuenta-eyebrow">Tu actividad</span>
            <h2 id="titulo-mis-reservas">Mis reservas</h2>
            <p>Consulta el estado de tus solicitudes y sus fechas.</p>
        </div>
        <span class="cuenta-total">{{ $reservas->total() }} {{ $reservas->total() === 1 ? 'reserva' : 'reservas' }}</span>
    </header>

    <div class="lista-reservas">
        @forelse ($reservas as $r)
        <article class="reserva-fila">
            <div class="reserva-icono"><i class="bi bi-book" aria-hidden="true"></i></div>
            <div class="reserva-libro">
                <span class="reserva-etiqueta">Libro reservado</span>
                <h3>{{ $r['libro'] }}</h3>
            </div>
            <div class="reserva-fecha">
                <span>Solicitada</span>
                <strong>{{ $r['reservado'] }}</strong>
            </div>
            <div class="reserva-fecha">
                <span>Vence</span>
                <strong>{{ $r['vence'] }}</strong>
            </div>
            <div class="reserva-estado">
                @include('partials.estado', ['estado' => $r['estado']])
                @if (in_array($r['estado'], ['Pendiente', 'Lista para retirar'], true))
                    <form action="{{ route('lector.reservas.cancelar', $r['id']) }}" method="POST" class="mt-2">@csrf<button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button></form>
                @endif
            </div>
        </article>
        @empty
        <div class="cuenta-vacia">
            <i class="bi bi-bookmark-plus" aria-hidden="true"></i>
            <h3>Aún no tienes reservas</h3>
            <p>Explora el catálogo y solicita reservar un título que no tenga ejemplares disponibles.</p>
            <a href="{{ route('inicio') }}" class="btn btn-primary">Explorar catálogo</a>
        </div>
        @endforelse
    </div>
    <div class="p-3">{{ $reservas->links('pagination::bootstrap-5') }}</div>
</section>
</div>
</div>
@endsection
