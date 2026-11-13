@extends('layouts.admin')

@section('titulo', 'Multas')

@section('contenido')
<section class="admin-listado" aria-labelledby="multas-subtitulo">
    <div class="admin-listado-intro">
        <div><span class="admin-eyebrow">Seguimiento de pagos</span><h2 id="multas-subtitulo">Control de multas</h2><p>Consulta cargos pendientes y pagos registrados.</p></div>
    </div>

    <div class="admin-metricas admin-metricas-tres" aria-label="Resumen de multas">
        <article class="admin-metrica"><span class="admin-metrica-icono tono-rojo"><i class="bi bi-cash-coin" aria-hidden="true"></i></span><div><span>Por cobrar</span><strong>${{ number_format($pendienteTotal, 2) }}</strong><small>{{ $pendientesTotal }} {{ $pendientesTotal === 1 ? 'multa pendiente' : 'multas pendientes' }}</small></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-verde"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><div><span>Cobrado</span><strong>${{ number_format($cobradaTotal, 2) }}</strong><small>{{ $cobradasTotal }} {{ $cobradasTotal === 1 ? 'multa pagada' : 'multas pagadas' }}</small></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-cafe"><i class="bi bi-receipt" aria-hidden="true"></i></span><div><span>Total de multas</span><strong>{{ $multasTotal }}</strong><small>Registradas en el sistema</small></div></article>
    </div>

<section class="card admin-panel border-0 shadow-sm" aria-labelledby="multas-tabla-titulo">
    <div class="admin-panel-cabecera">
        <div><h3 id="multas-tabla-titulo">Multas registradas</h3><span>{{ $multas->total() }} registros</span></div>
        <form action="{{ route('admin.multas') }}" method="GET" class="admin-buscador">
            <label for="buscador" class="visually-hidden">Buscar por lector, libro o motivo</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="buscador" name="buscar" class="form-control" placeholder="Buscar multas…" value="{{ request('buscar') }}" autocomplete="off">
            @if (request()->filled('estado'))<input type="hidden" name="estado" value="{{ request('estado') }}">@endif
        </form>
        @php($filtrosMultas = array_filter(request()->only('buscar'), fn ($valor) => filled($valor)))
        <div class="btn-group admin-filtros" role="group" aria-label="Filtrar multas por estado">
            <a href="{{ route('admin.multas', $filtrosMultas) }}" class="btn btn-outline-primary {{ request()->missing('estado') ? 'active' : '' }}">Todas</a>
            <a href="{{ route('admin.multas', array_merge($filtrosMultas, ['estado' => 'pending'])) }}" class="btn btn-outline-primary {{ request('estado') === 'pending' ? 'active' : '' }}">Pendientes</a>
            <a href="{{ route('admin.multas', array_merge($filtrosMultas, ['estado' => 'paid'])) }}" class="btn btn-outline-primary {{ request('estado') === 'paid' ? 'active' : '' }}">Pagadas</a>
        </div>
    </div>
    <div class="table-responsive">
        <table id="tabla-datos" class="table table-hover mb-0 align-middle admin-tabla admin-tabla-multas">
            <thead>
                <tr><th scope="col">Lector</th><th scope="col">Libro</th><th scope="col">Motivo</th><th scope="col">Monto</th><th scope="col">Estado</th><th scope="col" class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($multas as $m)
                <tr>
                    <td>{{ $m['lector'] }}</td>
                    <td>{{ $m['libro'] }}</td>
                    <td class="texto-secundario">{{ $m['motivo'] }}</td>
                    <td class="fw-semibold">${{ number_format($m['monto'], 2) }}</td>
                    <td>@include('partials.estado', ['estado' => $m['estado']])</td>
                    <td class="text-end">
                        @if ($m['estado'] === 'Pendiente')
                            <form action="{{ route('admin.multas.pagar', $m['id']) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-dorado">Registrar pago</button></form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="admin-vacio"><i class="bi bi-receipt" aria-hidden="true"></i><strong>No hay multas registradas</strong><span>Cuando se genere una multa, aparecerá en este listado.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $multas->links('pagination::bootstrap-5') }}</div>
</section>
 </section>
@endsection
