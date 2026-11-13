<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Administración')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/tema.css', 'resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body>
@php
    // [ruta, ícono, texto, patrón para marcar el botón activo]
    $menu = [
        ['admin.dashboard', 'bi-speedometer2', 'Panel principal', 'admin.dashboard'],
        ['admin.libros', 'bi-journal-bookmark', 'Libros', 'admin.libros*'],
        ['admin.ejemplares', 'bi-collection', 'Inventario', 'admin.ejemplares'],
        ['admin.lectores', 'bi-people', 'Lectores', 'admin.lectores*'],
        ['admin.prestamos', 'bi-arrow-left-right', 'Préstamos', 'admin.prestamos*'],
        ['admin.reservas', 'bi-bookmark-check', 'Reservas', 'admin.reservas'],
        ['admin.multas', 'bi-cash-coin', 'Multas', 'admin.multas'],
    ];
@endphp
<div class="d-flex">

    <aside class="offcanvas-md offcanvas-start sidebar" tabindex="-1" id="menu">
        <div class="offcanvas-header d-md-none">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-marca">
                <i class="bi bi-book-half" aria-hidden="true"></i>
                <span><strong>Biblioteca</strong><small>ADMINISTRACIÓN</small></span>
            </a>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#menu" aria-label="Cerrar menú"></button>
        </div>
        <div class="offcanvas-body flex-column p-3">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-marca d-none d-md-flex">
                <i class="bi bi-book-half" aria-hidden="true"></i>
                <span><strong>Biblioteca</strong><small>ADMINISTRACIÓN</small></span>
            </a>
            <ul class="nav nav-pills flex-column gap-1">
                @foreach ($menu as [$ruta, $icono, $texto, $patron])
                <li>
                    <a class="nav-link {{ request()->routeIs($patron) ? 'active' : '' }}" href="{{ route($ruta) }}">
                        <i class="bi {{ $icono }}"></i> {{ $texto }}
                    </a>
                </li>
                @endforeach
            </ul>
            <a href="{{ route('inicio') }}" class="nav-link mt-3 pt-3 text-white-50">
                <i class="bi bi-box-arrow-up-right"></i> Ver sitio público
            </a>
            <form action="{{ route('admin.cerrar-sesion') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="nav-link w-100 text-start text-white-50">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-grow-1 contenido">
        <header class="cabecera px-4 py-3 d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center min-w-0">
            <button class="btn btn-outline-primary d-md-none me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#menu" aria-controls="menu" aria-label="Abrir menú de administración">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="h4 mb-0">@yield('titulo')</h1>
            </div>
            <div class="admin-usuario"><i class="bi bi-person-circle" aria-hidden="true"></i><span>{{ auth()->user()->name }}</span></div>
        </header>
        <main class="admin-main p-4">
            @if (session('success')) <div class="alert alert-success alert-auto" role="status">{{ session('success') }}</div> @endif
            @if (session('error')) <div class="alert alert-danger" role="alert">{{ session('error') }}</div> @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><strong>Revisa los datos ingresados:</strong><ul class="mb-0 mt-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('contenido')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
