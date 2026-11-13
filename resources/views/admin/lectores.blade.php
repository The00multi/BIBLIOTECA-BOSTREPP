@extends('layouts.admin')

@section('titulo', 'Lectores')

@section('contenido')
<section class="admin-listado" aria-labelledby="lectores-subtitulo">
    <div class="admin-listado-intro">
        <div><span class="admin-eyebrow">Comunidad lectora</span><h2 id="lectores-subtitulo">Directorio de lectores</h2><p>Administra carnés, datos de contacto y estado de cada lector.</p></div>
        <a href="{{ route('admin.lectores.crear') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Nuevo lector</a>
    </div>

    <div class="admin-metricas admin-metricas-tres" aria-label="Resumen de lectores">
        <article class="admin-metrica"><span class="admin-metrica-icono tono-cafe"><i class="bi bi-people" aria-hidden="true"></i></span><div><span>Total de lectores</span><strong>{{ $lectoresTotal }}</strong></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-verde"><i class="bi bi-person-check" aria-hidden="true"></i></span><div><span>Activos</span><strong>{{ $lectoresActivos }}</strong></div></article>
        <article class="admin-metrica"><span class="admin-metrica-icono tono-rojo"><i class="bi bi-person-exclamation" aria-hidden="true"></i></span><div><span>Suspendidos</span><strong>{{ $lectoresSuspendidos }}</strong></div></article>
    </div>

<section class="card admin-panel border-0 shadow-sm" aria-labelledby="lectores-tabla-titulo">
    <div class="admin-panel-cabecera">
        <div><h3 id="lectores-tabla-titulo">Lectores registrados</h3><span>{{ $lectoresTotal }} personas en el directorio</span></div>
        <form action="{{ route('admin.lectores') }}" method="GET" class="admin-buscador"><label for="buscador" class="visually-hidden">Buscar por nombre, apellido o carnet</label><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="buscador" name="buscar" class="form-control" placeholder="Buscar lectores…" value="{{ request('buscar') }}" autocomplete="off"></form>
    </div>
    <div class="table-responsive">
        <table id="tabla-datos" class="table table-hover mb-0 align-middle admin-tabla admin-tabla-lectores">
            <thead>
                <tr><th scope="col">Carnet</th><th scope="col">Nombre</th><th scope="col">Documento</th><th scope="col">Teléfono</th><th scope="col">Estado</th><th scope="col" class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($lectores as $l)
                <tr>
                    <td><span class="codigo-inventario">{{ $l['carnet'] }}</span></td>
                    <td>{{ $l['nombres'] }} {{ $l['apellidos'] }}</td>
                    <td>{{ $l['documento'] }}</td>
                    <td>{{ $l['telefono'] }}</td>
                    <td>@include('partials.estado', ['estado' => $l['estado']])</td>
                    <td class="text-end">
                        <a href="{{ route('admin.lectores.editar', $l['id']) }}" class="btn btn-sm btn-outline-primary" title="Editar" aria-label="Editar a {{ $l['nombres'] }} {{ $l['apellidos'] }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                        <form action="{{ route('admin.lectores.eliminar', $l['id']) }}" method="POST" class="d-inline form-eliminar">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar lector" aria-label="Eliminar a {{ $l['nombres'] }} {{ $l['apellidos'] }}"><i class="bi bi-trash" aria-hidden="true"></i></button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="admin-vacio"><i class="bi bi-people" aria-hidden="true"></i><strong>No hay lectores registrados</strong><span>Agrega un lector para comenzar a gestionar sus préstamos.</span><a href="{{ route('admin.lectores.crear') }}" class="btn btn-primary btn-sm mt-2">Registrar lector</a></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $lectores->links('pagination::bootstrap-5') }}</div>
</section>

@endsection
