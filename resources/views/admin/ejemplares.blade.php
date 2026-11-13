@extends('layouts.admin')

@section('titulo', 'Inventario')

@section('contenido')
<section class="admin-listado" aria-labelledby="inventario-subtitulo">
    <div class="admin-listado-intro">
        <div>
            <span class="admin-eyebrow">Control de existencias</span>
            <h2 id="inventario-subtitulo">Inventario de ejemplares</h2>
            <p>Consulta cada copia física, su ubicación y estado.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEjemplar">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Nuevo ejemplar
        </button>
    </div>

    <div class="admin-metricas" aria-label="Resumen del inventario">
        <article class="admin-metrica"><span class="admin-metrica-icono tono-cafe"><i class="bi bi-collection" aria-hidden="true"></i></span><div><span>Total de ejemplares</span><strong>{{ $ejemplaresTotal }}</strong></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-verde"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><div><span>Disponibles</span><strong>{{ $disponiblesTotal }}</strong></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-oro"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><span>Prestados</span><strong>{{ $prestadosTotal }}</strong></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-rojo"><i class="bi bi-exclamation-circle" aria-hidden="true"></i></span><div><span>Con incidencias</span><strong>{{ $incidenciasTotal }}</strong></div></article>
    </div>

    <section class="card admin-panel border-0 shadow-sm" aria-labelledby="inventario-tabla-titulo">
        <div class="admin-panel-cabecera">
            <div><h3 id="inventario-tabla-titulo">Ejemplares registrados</h3><span>{{ $ejemplares->total() }} copias físicas</span></div>
            <form action="{{ route('admin.ejemplares') }}" method="GET" class="admin-buscador"><label for="buscador" class="visually-hidden">Buscar por código o título del libro</label><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="buscador" name="buscar" class="form-control" placeholder="Buscar ejemplares…" value="{{ request('buscar') }}" autocomplete="off"></form>
        </div>
        <div class="table-responsive">
            <table id="tabla-datos" class="table table-hover mb-0 align-middle admin-tabla admin-tabla-inventario">
                <thead><tr><th scope="col">Código</th><th scope="col">Libro</th><th scope="col">Ubicación</th><th scope="col">Estado</th><th scope="col" class="text-end">Acciones</th></tr></thead>
                <tbody>
                    @forelse ($ejemplares as $e)
                    <tr>
                        <td><span class="codigo-inventario">{{ $e['codigo'] }}</span></td>
                        <td class="fw-semibold">{{ $e['libro'] }}</td>
                        <td class="texto-secundario"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i>{{ $e['ubicacion'] }}</td>
                        <td>@include('partials.estado', ['estado' => $e['estado']])</td>
                        <td class="text-end text-nowrap">
                            @if ($e['estadoValor'] !== 'loaned')
                            <form action="{{ route('admin.ejemplares.actualizar', $e['id']) }}" method="POST" class="d-inline-flex align-items-center gap-1">
                                @csrf @method('PUT')
                                <label for="estado-{{ $e['id'] }}" class="visually-hidden">Cambiar estado de {{ $e['codigo'] }}</label>
                                <select name="status" id="estado-{{ $e['id'] }}" class="form-select form-select-sm" aria-label="Nuevo estado de {{ $e['codigo'] }}">
                                    @if ($e['estadoValor'] === 'reserved')
                                        <option value="" selected disabled>Ejemplar reservado</option>
                                    @else
                                        <option value="available" @selected($e['estadoValor'] === 'available')>Disponible</option>
                                        <option value="damaged" @selected($e['estadoValor'] === 'damaged')>Dañado</option>
                                        <option value="lost" @selected($e['estadoValor'] === 'lost')>Perdido</option>
                                    @endif
                                    @if ($e['estadoValor'] === 'reserved')
                                        <option value="damaged">Marcar dañado</option>
                                        <option value="lost">Marcar perdido</option>
                                    @endif
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                            </form>
                            @endif
                            <form action="{{ route('admin.ejemplares.eliminar', $e['id']) }}" method="POST" class="d-inline form-eliminar">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar ejemplar" aria-label="Eliminar ejemplar {{ $e['codigo'] }}"><i class="bi bi-trash" aria-hidden="true"></i></button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="admin-vacio"><i class="bi bi-collection" aria-hidden="true"></i><strong>No hay ejemplares registrados</strong><span>Registra la primera copia para incorporarla al inventario.</span><button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalEjemplar">Registrar ejemplar</button></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-3">{{ $ejemplares->links('pagination::bootstrap-5') }}</div>
</section>

<div class="modal fade" id="modalEjemplar" tabindex="-1" aria-labelledby="tituloModalEjemplar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.ejemplares.guardar') }}" method="POST" class="modal-content admin-modal">
            @csrf
            <div class="modal-header"><div><span class="admin-eyebrow">Inventario</span><h2 class="modal-title h5" id="tituloModalEjemplar">Nuevo ejemplar</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label for="ej-libro" class="form-label">Libro</label><select name="book_id" id="ej-libro" class="form-select" required><option value="">Selecciona un libro</option>@foreach ($libros as $libro)<option value="{{ $libro['id'] }}" @selected(old('book_id') == $libro['id'])>{{ $libro['titulo'] }}</option>@endforeach</select></div>
                <div class="mb-3"><label for="ej-codigo" class="form-label">Código de inventario</label><input type="text" name="code" id="ej-codigo" class="form-control" placeholder="Ej.: CA-0005" value="{{ old('code') }}" required></div>
                <div class="mb-3"><label for="ej-estante" class="form-label">Ubicación</label><input type="text" name="location" id="ej-estante" class="form-control" value="{{ old('location') }}"></div>
                <div><label for="ej-estado" class="form-label">Estado</label><select name="status" id="ej-estado" class="form-select"><option value="available">Disponible</option><option value="damaged">Dañado</option><option value="lost">Perdido</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar ejemplar</button></div>
        </form>
    </div>
</div>
@endsection
