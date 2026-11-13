<div class="card h-100 shadow-sm card-libro position-relative">
    @if ($libro['novedad'] ?? false)
        <span class="etiqueta etiqueta-novedad">Novedad</span>
    @elseif ($libro['popular'] ?? false)
        <span class="etiqueta etiqueta-popular">Destacado</span>
    @endif

    <a href="{{ route('libro', $libro['id']) }}" class="portada-enlace" aria-label="Ver {{ $libro['titulo'] }}">
        <div class="portada-ilustrada portada-variante-{{ (($libro['id'] - 1) % 6) + 1 }}" aria-hidden="true">
            @if (!empty($libro['portada']))
                <img src="{{ asset('storage/'.$libro['portada']) }}" alt="" loading="lazy" class="portada-foto">
            @else
                <img src="{{ asset('images/portada-sin-portada.png') }}" alt="" loading="lazy" class="portada-foto">
            @endif
        </div>
    </a>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-1 mb-2">
            @foreach ($libro['categorias'] as $categoria)
                <span class="badge etiqueta-categoria">{{ $categoria }}</span>
            @endforeach
        </div>
        <h3 class="card-title h5">{{ $libro['titulo'] }}</h3>
        <p class="card-text text-muted mb-2">{{ $libro['autor'] }}</p>
        <span class="estado-disponibilidad {{ $libro['disponibles'] > 0 ? 'esta-disponible' : 'sin-disponibilidad' }}">
            <i class="bi {{ $libro['disponibles'] > 0 ? 'bi-check-circle-fill' : 'bi-clock' }}" aria-hidden="true"></i>
            {{ $libro['disponibles'] > 0 ? $libro['disponibles'].' disponibles' : 'Puedes solicitar reserva' }}
        </span>
    </div>
    <div class="card-footer bg-white border-0 pb-3">
        <a href="{{ route('libro', $libro['id']) }}" class="btn btn-primary w-100">
            {{ $libro['disponibles'] > 0 ? 'Ver disponibilidad' : 'Ver opción de reserva' }}
        </a>
    </div>
</div>
