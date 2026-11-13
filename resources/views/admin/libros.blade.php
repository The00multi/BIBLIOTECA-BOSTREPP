@extends('layouts.admin')

@section('titulo', 'Libros')

@section('contenido')
<section class="libros-page" aria-labelledby="libros-resumen-titulo">
    <div class="libros-intro">
        <div>
            <span class="admin-eyebrow">Colección bibliográfica</span>
            <h2 id="libros-resumen-titulo">Catálogo de libros</h2>
            <p>Consulta títulos, autores y disponibilidad de ejemplares.</p>
        </div>
        <a href="{{ route('admin.libros.crear') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Nuevo libro
        </a>
    </div>

    <div class="libros-resumen" aria-label="Resumen de la colección">
        <article class="libros-resumen-card">
            <span class="libros-resumen-icono libros-resumen-icono-cafe"><i class="bi bi-journal-bookmark" aria-hidden="true"></i></span>
            <div><span>Títulos registrados</span><strong>{{ $totalTitulos }}</strong></div>
        </article>
        <article class="libros-resumen-card">
            <span class="libros-resumen-icono libros-resumen-icono-oro"><i class="bi bi-collection" aria-hidden="true"></i></span>
            <div><span>Ejemplares en total</span><strong>{{ $totalEjemplares }}</strong></div>
        </article>
        <article class="libros-resumen-card">
            <span class="libros-resumen-icono libros-resumen-icono-verde"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
            <div><span>Disponibles</span><strong>{{ $ejemplaresDisponibles }}</strong></div>
        </article>
    </div>

    <section class="card libros-panel border-0 shadow-sm" aria-labelledby="tabla-libros-titulo">
        <div class="libros-panel-cabecera">
            <div>
                <h3 id="tabla-libros-titulo">Libros registrados</h3>
                <span>{{ $totalTitulos }} {{ $totalTitulos === 1 ? 'título' : 'títulos' }} en el catálogo</span>
            </div>
            <div class="libros-buscador">
                <label for="buscador" class="visually-hidden">Buscar por título, autor o ISBN</label>
                <i class="bi bi-search" aria-hidden="true"></i>
                <form action="{{ route('admin.libros') }}" method="GET" class="d-flex align-items-center">
                    <input type="search" id="buscador" name="buscar" class="form-control" placeholder="Buscar por título, autor o ISBN…" value="{{ request('buscar') }}" autocomplete="off">
                    <button class="btn btn-link text-decoration-none" type="submit" aria-label="Buscar libros"><i class="bi bi-search" aria-hidden="true"></i></button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tabla-datos" class="table table-hover mb-0 align-middle libros-table">
                <thead>
                    <tr>
                        <th scope="col">Libro</th>
                        <th scope="col">Autor</th>
                        <th scope="col">ISBN</th>
                        <th scope="col">Disponibilidad</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($libros as $libro)
                    <tr>
                        <td>
                            <div class="libro-identidad">
                                @if (!empty($libro['portada']))
                                    <img class="libro-portada" src="{{ asset('storage/'.$libro['portada']) }}" alt="">
                                @else
                                    <img class="libro-portada" src="{{ asset('images/portada-sin-portada.png') }}" alt="">
                                @endif
                                <div class="libro-titulo-celda">
                                    <strong>{{ $libro['titulo'] }}</strong>
                                    <span>{{ $libro['editorial'] ?? 'Editorial no especificada' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $libro['autor'] }}</td>
                        <td><span class="libro-isbn">{{ $libro['isbn'] }}</span></td>
                        <td>
                            <div class="libro-disponibilidad">
                                <span class="badge rounded-pill {{ $libro['disponibles'] > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $libro['disponibles'] > 0 ? 'Disponible' : 'No disponible' }}
                                </span>
                                <small>{{ $libro['disponibles'] }} de {{ $libro['total'] }} ejemplares</small>
                            </div>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.libros.editar', $libro['id']) }}" class="btn btn-sm btn-outline-primary" aria-label="Editar {{ $libro['titulo'] }}" title="Editar libro"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                            <form action="{{ route('admin.libros.eliminar', $libro['id']) }}" method="POST" class="d-inline form-eliminar">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Eliminar {{ $libro['titulo'] }}" title="Eliminar libro"><i class="bi bi-trash" aria-hidden="true"></i></button></form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="libros-vacio">
                                <i class="bi bi-journal-x" aria-hidden="true"></i>
                                <strong>Aún no hay libros registrados</strong>
                                <span>Agrega el primer título para comenzar a organizar la colección.</span>
                                <a href="{{ route('admin.libros.crear') }}" class="btn btn-primary btn-sm mt-2">Registrar primer libro</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-3">{{ $libros->links('pagination::bootstrap-5') }}</div>
</section>
@endsection
