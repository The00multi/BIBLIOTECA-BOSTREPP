@extends('layouts.publico')

@section('titulo', $libro['titulo'])

@section('contenido')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Catálogo</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $libro['titulo'] }}</li>
    </ol>
</nav>

<div class="row g-4">
    <div class="col-md-4 col-lg-3">
        <div class="portada-ilustrada portada-detalle portada-variante-{{ (($libro['id'] - 1) % 6) + 1 }}" role="img" aria-label="Portada ilustrada de {{ $libro['titulo'] }}">
            @if (!empty($libro['portada']))
                <img src="{{ asset('storage/'.$libro['portada']) }}" alt="" class="portada-foto">
            @else
                <img src="{{ asset('images/portada-sin-portada.png') }}" alt="" class="portada-foto">
            @endif
        </div>
    </div>

    <div class="col-md-8 col-lg-9">
        <h1 class="h2">{{ $libro['titulo'] }}</h1>
        <p class="fs-5 text-muted">{{ $libro['autor'] }}</p>

        <div class="mb-3">
            @foreach ($libro['categorias'] as $categoria)
                <span class="badge text-bg-light border">{{ $categoria }}</span>
            @endforeach
        </div>

        <dl class="row mb-4">
            <dt class="col-sm-3">Editorial</dt><dd class="col-sm-9">{{ $libro['editorial'] }}</dd>
            <dt class="col-sm-3">ISBN</dt><dd class="col-sm-9">{{ $libro['isbn'] }}</dd>
            <dt class="col-sm-3">Año de publicación</dt><dd class="col-sm-9">{{ $libro['anio'] }}</dd>
        </dl>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                @if ($libro['disponibles'] > 0)
                    <span class="badge text-bg-success fs-6">{{ $libro['disponibles'] }} de {{ $libro['total'] }} ejemplares disponibles</span>
                    <span class="text-muted">Pasa por la biblioteca con tu carnet para llevártelo.</span>
                @else
                    <span class="badge text-bg-secondary fs-6">Sin ejemplares disponibles</span>
                    <span class="text-muted">Reserva este título y revisa el estado en tu cuenta. Si vuelve un ejemplar, tendrás tres días para retirarlo.</span>
                    @auth
                        @if (auth()->user()->role === 'reader')
                            <form action="{{ route('libro.reservar', $libro['id']) }}" method="POST" class="ms-auto">@csrf<button type="submit" class="btn btn-dorado"><i class="bi bi-bookmark-plus"></i> Reservar libro</button></form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-dorado ms-auto"><i class="bi bi-box-arrow-in-right"></i> Ingresa para reservar</a>
                    @endauth
                @endif
            </div>
        </div>

        <h2 class="h5">Ejemplares</h2>
        <div class="card border-0 shadow-sm mb-4">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr><th>Código</th><th>Ubicación</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($ejemplares as $ejemplar)
                        <tr>
                            <td>{{ $ejemplar['codigo'] }}</td>
                            <td>{{ $ejemplar['ubicacion'] }}</td>
                            <td>@include('partials.estado', ['estado' => $ejemplar['estado']])</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@if (count($relacionados))
<section class="mt-5" aria-labelledby="titulo-relacionados">
    <span class="seccion-eyebrow">Más lecturas para explorar</span>
    <h2 id="titulo-relacionados" class="h4 mb-3">También te puede interesar</h2>
    <div class="row row-cols-2 row-cols-md-4 g-4">
        @foreach ($relacionados as $relacionado)
        <div class="col">
            @include('partials.tarjeta-libro', ['libro' => $relacionado])
        </div>
        @endforeach
    </div>
</section>
@endif
@endsection
