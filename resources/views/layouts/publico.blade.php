<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Biblioteca')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/tema.css', 'resources/css/publico.css', 'resources/css/catalogo.css', 'resources/js/publico.js'])
</head>
<body>
    <nav class="navbar navbar-expand-xl navbar-light navbar-biblioteca" aria-label="Navegación principal">
        <div class="container-fluid container-xxl navbar-contenido">
            <a class="navbar-brand marca-biblioteca" href="{{ route('inicio') }}" aria-label="Biblioteca, ir al catálogo">
                <i class="bi bi-book-half" aria-hidden="true"></i>
                <span><strong>Biblioteca</strong><small>LEE · DESCUBRE · RESERVA</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPublico" aria-controls="menuPublico" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuPublico">
                <ul class="navbar-nav nav-categorias mx-xl-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inicio', 'libro') && !request()->query('categoria') ? 'active' : '' }}" href="{{ route('inicio') }}">Catálogo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-categoria-nav="Infantil" href="{{ route('inicio', ['categoria' => 'Infantil']) }}">Infantil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-categoria-nav="Clásicos" href="{{ route('inicio', ['categoria' => 'Clásicos']) }}">Clásicos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-categoria-nav="Realismo mágico" href="{{ route('inicio', ['categoria' => 'Realismo mágico']) }}">Realismo mágico</a>
                    </li>
                </ul>
                <form action="{{ route('inicio') }}" method="GET" class="nav-busqueda" role="search" aria-label="Buscar libros">
                    <label for="buscador-catalogo" class="visually-hidden">Buscar libros por título o autor</label>
                    <input id="buscador-catalogo" name="buscar" type="search" class="form-control buscador-catalogo" placeholder="Buscar libros..." value="{{ request()->query('buscar') }}">
                    <button type="submit" class="btn btn-dorado" aria-label="Buscar libros"><i class="bi bi-search" aria-hidden="true"></i></button>
                </form>
                <div class="nav-utilidades">
                    @auth
                        @if (auth()->user()->role === 'admin')
                            <a class="nav-icono" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i><span>Administración</span></a>
                        @else
                            <a class="nav-icono {{ request()->routeIs('lector.reservas') ? 'activo' : '' }}" href="{{ route('lector.reservas') }}" aria-label="Mis reservas" title="Mis reservas"><i class="bi bi-bookmark" aria-hidden="true"></i><span>Reservas</span></a>
                            <a class="nav-icono {{ request()->routeIs('lector.prestamos') ? 'activo' : '' }}" href="{{ route('lector.prestamos') }}" aria-label="Mis préstamos" title="Mis préstamos"><i class="bi bi-journal-bookmark" aria-hidden="true"></i><span>Préstamos</span></a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">@csrf<button class="nav-icono nav-perfil border-0 bg-transparent" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Salir</span></button></form>
                    @else
                        <a class="nav-icono nav-perfil" href="{{ route('login') }}" aria-label="Ingresar" title="Ingresar"><i class="bi bi-person" aria-hidden="true"></i><span>Ingresar</span></a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('success')) <div class="alert alert-success alert-auto" role="status">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="alert alert-danger" role="alert">{{ session('error') }}</div> @endif
        @if ($errors->any()) <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div> @endif
        @yield('contenido')
    </main>

    <footer class="text-center text-muted py-4 mt-4">
        &copy; {{ date('Y') }} Biblioteca &nbsp;|&nbsp;
        @guest<a href="{{ route('registro') }}" class="text-muted">Crear cuenta de lector</a>@endguest
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
