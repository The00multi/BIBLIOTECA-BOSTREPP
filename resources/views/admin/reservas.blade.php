@extends('layouts.admin')

@section('titulo', 'Reservas')

@section('contenido')
<section class="admin-listado" aria-labelledby="reservas-subtitulo">
    <div class="admin-listado-intro">
        <div><span class="admin-eyebrow">Solicitudes de lectura</span><h2 id="reservas-subtitulo">Control de reservas</h2><p>Los ejemplares devueltos se asignan a la reserva pendiente más antigua.</p></div>
    </div>
    <section class="card admin-panel border-0 shadow-sm" aria-labelledby="reservas-tabla-titulo">
        <div class="admin-panel-cabecera">
            <div><h3 id="reservas-tabla-titulo">Reservas registradas</h3><span>{{ $reservas->total() }} solicitudes</span></div>
            <form action="{{ route('admin.reservas') }}" method="GET" class="admin-buscador">
                <label for="buscador" class="visually-hidden">Buscar por lector o título</label>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="buscador" name="buscar" class="form-control" placeholder="Buscar reservas…" value="{{ request('buscar') }}" autocomplete="off">
                @if (request()->filled('estado'))<input type="hidden" name="estado" value="{{ request('estado') }}">@endif
            </form>
            @php($filtrosReservas = array_filter(request()->only('buscar'), fn ($valor) => filled($valor)))
            <div class="btn-group admin-filtros" role="group" aria-label="Filtrar reservas por estado">
                <a href="{{ route('admin.reservas', $filtrosReservas) }}" class="btn btn-outline-primary {{ request()->missing('estado') ? 'active' : '' }}">Todas</a>
                <a href="{{ route('admin.reservas', array_merge($filtrosReservas, ['estado' => 'pending'])) }}" class="btn btn-outline-primary {{ request('estado') === 'pending' ? 'active' : '' }}">Pendientes</a>
                <a href="{{ route('admin.reservas', array_merge($filtrosReservas, ['estado' => 'ready'])) }}" class="btn btn-outline-primary {{ request('estado') === 'ready' ? 'active' : '' }}">Listas</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle admin-tabla">
                <thead><tr><th>Lector</th><th>Carnet</th><th>Libro</th><th>Ejemplar asignado</th><th>Solicitada</th><th>Retirar antes del</th><th>Estado</th></tr></thead>
                <tbody>
                    @forelse ($reservas as $reserva)
                    <tr>
                        <td>{{ $reserva['lector'] }}</td><td><span class="codigo-inventario">{{ $reserva['carnet'] }}</span></td>
                        <td>{{ $reserva['libro'] }}</td><td>{{ $reserva['ejemplar'] ?? 'En espera' }}</td>
                        <td>{{ $reserva['reservado'] }}</td><td>{{ $reserva['vence'] ?? '—' }}</td>
                        <td>
                            @include('partials.estado', ['estado' => $reserva['estado']])
                            @if (in_array($reserva['estadoValor'], ['pending', 'ready'], true))
                                <form action="{{ route('admin.reservas.cancelar', $reserva['id']) }}" method="POST" class="mt-2 form-eliminar">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar reserva</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="admin-vacio"><i class="bi bi-bookmark-check" aria-hidden="true"></i><strong>No hay reservas</strong><span>Las solicitudes de los lectores aparecerán aquí.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-3">{{ $reservas->links('pagination::bootstrap-5') }}</div>
</section>
@endsection
