@extends('layouts.publico')

@section('titulo', 'Catálogo')

@section('contenido')
<div id="destacados" class="carousel slide mb-5" aria-label="Destacados de la biblioteca">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#destacados" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Destacados"></button>
        <button type="button" data-bs-target="#destacados" data-bs-slide-to="1" aria-label="Reto literario"></button>
        <button type="button" data-bs-target="#destacados" data-bs-slide-to="2" aria-label="Explorar catálogo"></button>
    </div>
    <div class="carousel-inner rounded-4">
        <div class="carousel-item active">
            <section class="hero hero-banner-novedades m-0 pb-5">
                <span class="hero-ilustracion" aria-hidden="true"><i class="bi bi-book-half"></i></span>
                <span class="hero-etiqueta"><i class="bi bi-stars" aria-hidden="true"></i> Biblioteca en línea</span>
                <h1 class="display-6">Novedades de la biblioteca</h1>
                <p class="lead mb-4">Descubre los últimos títulos que llegaron a nuestras salas.</p>
                <a href="#novedades" class="btn btn-dorado">Ver novedades</a>
            </section>
        </div>
        <div class="carousel-item">
            <section class="hero hero-banner-nocturno m-0 pb-5">
                <span class="hero-ilustracion" aria-hidden="true"><i class="bi bi-moon-stars"></i></span>
                <span class="hero-etiqueta"><i class="bi bi-book-half" aria-hidden="true"></i> Una lectura para descubrir</span>
                <h2 class="display-6">Déjate llevar por el realismo mágico</h2>
                <p class="lead mb-4">Explora historias extraordinarias y consulta cuáles están disponibles.</p>
                <a href="#catalogo" class="btn btn-dorado" data-ir-categoria="Realismo mágico">Ver libros de la categoría</a>
            </section>
        </div>
        <div class="carousel-item">
            <section class="hero hero-banner-explora m-0 pb-5">
                <span class="hero-ilustracion" aria-hidden="true"><i class="bi bi-bookmarks"></i></span>
                <span class="hero-etiqueta"><i class="bi bi-bookmark-check" aria-hidden="true"></i> Consulta el catálogo</span>
                <h2 class="display-6">Encuentra tu próxima lectura</h2>
                <p class="lead mb-4">Busca por título o autor, revisa los ejemplares y conoce cómo solicitar una reserva.</p>
                <a href="#catalogo" class="btn btn-dorado">Explorar libros</a>
            </section>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#destacados" data-bs-slide="prev" aria-label="Destacado anterior">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#destacados" data-bs-slide="next" aria-label="Siguiente destacado">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
</div>

@if (count($novedades))
<section id="novedades" class="mb-5" aria-labelledby="titulo-novedades">
    <div class="d-flex align-items-end justify-content-between mb-3">
        <div>
            <span class="seccion-eyebrow">Recién incorporados</span>
            <h2 id="titulo-novedades" class="h3 mb-0">Novedades</h2>
        </div>
        <a href="#catalogo" class="link-biblioteca">Ver catálogo <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
    <div class="tira">
        @foreach ($novedades as $libro)
        <a href="{{ route('libro', $libro['id']) }}" class="item-tira">
            <div class="portada-ilustrada portada-tira portada-variante-{{ (($libro['id'] - 1) % 6) + 1 }}" aria-hidden="true">
                @if (!empty($libro['portada']))
                    <img src="{{ asset('storage/'.$libro['portada']) }}" alt="" loading="lazy" class="portada-foto">
                @else
                    <img src="{{ asset('images/portada-sin-portada.png') }}" alt="" loading="lazy" class="portada-foto">
                @endif
            </div>
            <div class="titulo">{{ $libro['titulo'] }}</div>
            <div class="autor">{{ $libro['autor'] }}</div>
            <span class="small {{ $libro['disponibles'] > 0 ? 'texto-disponible' : 'texto-reserva' }}">
                {{ $libro['disponibles'] > 0 ? $libro['disponibles'].' disponibles' : 'Admite reserva' }}
            </span>
        </a>
        @endforeach
    </div>
</section>
@endif

<section id="catalogo" aria-labelledby="titulo-catalogo">
    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
        <div>
            <span class="seccion-eyebrow">Libros infantiles, clásicos y realismo mágico</span>
            <h2 id="titulo-catalogo" class="h3 mb-0">Catálogo de libros</h2>
        </div>
        <form action="{{ route('inicio') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
            @if (request()->filled('buscar'))<input type="hidden" name="buscar" value="{{ request('buscar') }}">@endif
            @if (request()->filled('categoria'))<input type="hidden" name="categoria" value="{{ request('categoria') }}">@endif
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="solo-disponibles" name="disponible" value="1" @checked(request()->boolean('disponible'))>
                <label class="form-check-label" for="solo-disponibles">Solo disponibles</label>
            </div>
            <button class="btn btn-outline-primary btn-sm" type="submit">Aplicar filtro</button>
        </form>
    </div>

    @php($filtrosCatalogo = array_filter(request()->only(['buscar', 'disponible']), fn ($valor) => filled($valor)))
    <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Filtrar por categoría">
        <a href="{{ route('inicio', $filtrosCatalogo) }}" class="chip {{ request()->filled('categoria') ? '' : 'active' }}" @if (!request()->filled('categoria')) aria-current="true" @endif>Todas</a>
        @foreach (['Infantil', 'Clásicos', 'Realismo mágico'] as $categoria)
            <a href="{{ route('inicio', array_merge($filtrosCatalogo, ['categoria' => $categoria])) }}" class="chip {{ request('categoria') === $categoria ? 'active' : '' }}" @if (request('categoria') === $categoria) aria-current="true" @endif>{{ $categoria }}</a>
        @endforeach
    </div>

    <p class="small text-muted mb-3">{{ $libros->total() }} {{ $libros->total() === 1 ? 'libro encontrado' : 'libros encontrados' }}</p>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
        @foreach ($libros as $libro)
        <div class="col">
            @include('partials.tarjeta-libro', ['libro' => $libro])
        </div>
        @endforeach
    </div>

    @if ($libros->isEmpty())
        <div class="text-center text-muted py-5" role="status">
            <i class="bi bi-search fs-1 d-block mb-2" aria-hidden="true"></i>
            <p class="mb-0">No encontramos libros con esos filtros. Prueba con otra palabra o categoría.</p>
        </div>
    @endif

    <div class="mt-4">{{ $libros->links('pagination::bootstrap-5') }}</div>
</section>
@endsection
