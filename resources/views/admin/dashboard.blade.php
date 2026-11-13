@extends('layouts.admin')

@section('titulo', 'Panel principal')

@section('contenido')
<section class="dashboard-admin" aria-labelledby="titulo-resumen">
    <header class="dashboard-intro">
        <div>
            <h2 id="titulo-resumen">Resumen de actividad</h2>
        </div>
        <div class="dashboard-acciones">
            <a href="{{ route('admin.libros.crear') }}" class="btn btn-outline-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo libro</a>
            <a href="{{ route('admin.prestamos.nuevo') }}" class="btn btn-primary"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> Registrar préstamo</a>
        </div>
    </header>

    @if ($prestamosVencidos > 0)
    <div class="dashboard-aviso" role="status">
        <span class="dashboard-aviso-icono"><i class="bi bi-exclamation-circle" aria-hidden="true"></i></span>
        <span><strong>{{ $prestamosVencidos }} {{ $prestamosVencidos === 1 ? 'préstamo vencido' : 'préstamos vencidos' }}</strong> {{ $prestamosVencidos === 1 ? 'requiere' : 'requieren' }} seguimiento.</span>
        <a href="{{ route('admin.prestamos') }}">Revisar préstamos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
    @endif

    <div class="row g-3 mb-4">
        @foreach ([
            ['Libros', $totalLibros, 'bi-journal-bookmark', 'primary', 'admin.libros'],
            ['Lectores', $totalLectores, 'bi-people', 'success', 'admin.lectores'],
            ['Préstamos activos', $prestamosActivos, 'bi-arrow-left-right', 'warning', 'admin.prestamos'],
            ['Multas pendientes', $multasPendientes, 'bi-cash-coin', 'danger', 'admin.multas'],
        ] as [$nombre, $total, $icono, $color, $ruta])
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route($ruta) }}" class="card shadow-sm card-stat dashboard-stat-link h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">{{ $nombre }}</div>
                        <div class="fs-2 fw-bold text-dark">{{ $total }}</div>
                        <span class="dashboard-stat-accion">Ver detalle <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                    </div>
                    <span class="dashboard-stat-icon dashboard-stat-icon-{{ $color }}" aria-hidden="true">
                        <i class="bi {{ $icono }}"></i>
                    </span>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <section class="card border-0 shadow-sm dashboard-actividad" aria-labelledby="titulo-ultimos-prestamos">
        <header class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="dashboard-eyebrow">Movimiento de la colección</span>
                <h3 id="titulo-ultimos-prestamos" class="h5 mb-0">Préstamos recientes</h3>
            </div>
            <a href="{{ route('admin.prestamos') }}" class="dashboard-ver-todos">Ver todos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </header>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr><th>Lector</th><th>Libro</th><th>Fecha de préstamo</th><th>Fecha límite</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($ultimosPrestamos as $prestamo)
                    <tr>
                        <td class="fw-semibold">{{ $prestamo['lector'] }}</td>
                        <td>{{ $prestamo['libro'] }}</td>
                        <td>{{ $prestamo['prestamo'] }}</td>
                        <td>{{ $prestamo['limite'] }}</td>
                        <td>@include('partials.estado', ['estado' => $prestamo['estado']])</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Todavía no hay préstamos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
