@extends('layouts.admin')

@section('titulo', 'Préstamos')

@section('contenido')
<section class="admin-listado" aria-labelledby="prestamos-subtitulo">
    <div class="admin-listado-intro">
        <div><span class="admin-eyebrow">Circulación de libros</span><h2 id="prestamos-subtitulo">Control de préstamos</h2><p>Revisa plazos, devoluciones y el estado de cada préstamo.</p></div>
        <a href="{{ route('admin.prestamos.nuevo') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Nuevo préstamo</a>
    </div>

    <div class="admin-metricas" aria-label="Resumen de préstamos">
        <article class="admin-metrica"><span class="admin-metrica-icono tono-cafe"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><span>Total registrados</span><strong>{{ $prestamosTotal }}</strong></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-verde"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span><div><span>Activos</span><strong>{{ $prestamosActivos }}</strong></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-rojo"><i class="bi bi-calendar-x" aria-hidden="true"></i></span><div><span>Vencidos</span><strong>{{ $prestamosVencidos }}</strong></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-oro"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><div><span>Devueltos</span><strong>{{ $prestamosDevueltos }}</strong></div></article>
    </div>

<section class="card admin-panel border-0 shadow-sm" aria-labelledby="prestamos-tabla-titulo">
    <div class="admin-panel-cabecera admin-panel-cabecera-wrap">
        <div><h3 id="prestamos-tabla-titulo">Préstamos registrados</h3><span>{{ $prestamos->total() }} registros</span></div>
        <form action="{{ route('admin.prestamos') }}" method="GET" class="admin-buscador">
            <label for="buscador" class="visually-hidden">Buscar por lector, libro o código de ejemplar</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="buscador" name="buscar" class="form-control" placeholder="Buscar préstamos…" value="{{ request('buscar') }}" autocomplete="off">
            @if (request()->filled('estado'))<input type="hidden" name="estado" value="{{ request('estado') }}">@endif
        </form>
        @php($filtrosPrestamos = array_filter(request()->only('buscar'), fn ($valor) => filled($valor)))
        <div class="btn-group admin-filtros" role="group" aria-label="Filtrar préstamos por estado">
            <a href="{{ route('admin.prestamos', $filtrosPrestamos) }}" class="btn btn-outline-primary {{ request()->missing('estado') ? 'active' : '' }}">Todos</a>
            <a href="{{ route('admin.prestamos', array_merge($filtrosPrestamos, ['estado' => 'active'])) }}" class="btn btn-outline-primary {{ request('estado') === 'active' ? 'active' : '' }}">Activos</a>
            <a href="{{ route('admin.prestamos', array_merge($filtrosPrestamos, ['estado' => 'overdue'])) }}" class="btn btn-outline-primary {{ request('estado') === 'overdue' ? 'active' : '' }}">Vencidos</a>
            <a href="{{ route('admin.prestamos', array_merge($filtrosPrestamos, ['estado' => 'returned'])) }}" class="btn btn-outline-primary {{ request('estado') === 'returned' ? 'active' : '' }}">Devueltos</a>
        </div>
    </div>
    <div class="table-responsive">
        <table id="tabla-datos" class="table table-hover mb-0 align-middle admin-tabla admin-tabla-prestamos">
            <thead>
                <tr><th scope="col">Lector</th><th scope="col">Libro</th><th scope="col">Ejemplar</th><th scope="col">Préstamo</th><th scope="col">Fecha límite</th><th scope="col">Devuelto</th><th scope="col">Estado</th><th scope="col" class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($prestamos as $p)
                <tr data-estado="{{ $p['estado'] }}">
                    <td>{{ $p['lector'] }}</td>
                    <td>{{ $p['libro'] }}</td>
                    <td>{{ $p['ejemplar'] }}</td>
                    <td>{{ $p['prestamo'] }}</td>
                    <td>{{ $p['limite'] }}</td>
                    <td>{{ $p['devolucion'] ?? '-' }}</td>
                    <td>@include('partials.estado', ['estado' => $p['estado']])</td>
                    <td class="text-end">
                        @if (! $p['devolucion'])
                            <button type="button" class="btn btn-sm btn-dorado btn-devolver"
                                    data-bs-toggle="modal" data-bs-target="#modalDevolucion"
                                    data-info="{{ $p['libro'] }} ({{ $p['ejemplar'] }}), prestado a {{ $p['lector'] }}"
                                    data-action="{{ route('admin.prestamos.devolver', $p['id']) }}"
                                    data-vencido="{{ $p['estado'] === 'Vencido' ? '1' : '0' }}">
                                Registrar devolución
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="admin-vacio"><i class="bi bi-arrow-left-right" aria-hidden="true"></i><strong>No hay préstamos registrados</strong><span>Los préstamos nuevos aparecerán aquí.</span><a href="{{ route('admin.prestamos.nuevo') }}" class="btn btn-primary btn-sm mt-2">Registrar préstamo</a></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $prestamos->links('pagination::bootstrap-5') }}</div>
</section>

<div class="modal fade" id="modalDevolucion" tabindex="-1" aria-labelledby="tituloModalDevolucion" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formDevolucion" action="" method="POST" class="modal-content admin-modal">
            @csrf
            <div class="modal-header">
                <div><span class="admin-eyebrow">Circulación de libros</span><h2 class="modal-title h5" id="tituloModalDevolucion">Registrar devolución</h2></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p id="devolucion-info" class="fw-semibold"></p>
                <div id="devolucion-aviso" class="alert alert-warning d-none mb-3">
                    Este préstamo está vencido. Al confirmar se generará una multa por el retraso.
                </div>
                <label for="estado-ejemplar" class="form-label">Estado del ejemplar al regresar</label>
                <select id="estado-ejemplar" name="copy_status" class="form-select">
                    <option value="available">Disponible</option>
                    <option value="damaged">Dañado</option>
                    <option value="lost">Perdido</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar devolución</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filtro por estado
    document.querySelectorAll('[data-filtro]').forEach(boton => {
        boton.addEventListener('click', () => {
            const filtro = boton.dataset.filtro;
            document.querySelectorAll('#tabla-datos tbody tr').forEach(fila => {
                fila.style.display = (filtro === 'todos' || fila.dataset.estado === filtro) ? '' : 'none';
            });
            document.querySelectorAll('[data-filtro]').forEach(b => b.classList.toggle('active', b === boton));
        });
    });

    // Datos del préstamo dentro del modal de devolución
    document.querySelectorAll('.btn-devolver').forEach(boton => {
        boton.addEventListener('click', () => {
            document.getElementById('devolucion-info').textContent = boton.dataset.info;
            document.getElementById('devolucion-aviso').classList.toggle('d-none', boton.dataset.vencido !== '1');
            document.getElementById('formDevolucion').action = boton.dataset.action;
        });
    });
</script>
@endpush
