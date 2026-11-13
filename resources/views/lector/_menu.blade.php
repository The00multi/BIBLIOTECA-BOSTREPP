<aside class="cuenta-sidebar" aria-label="Menú de mi cuenta">
    <div class="cuenta-sidebar-perfil">
        <div class="cuenta-avatar" aria-hidden="true">{{ strtoupper(substr($reader->first_name, 0, 1).substr($reader->last_name, 0, 1)) }}</div>
        <span class="cuenta-eyebrow">Espacio del lector</span>
        <h1>Mi cuenta</h1>
        <p>{{ $reader->first_name }} {{ $reader->last_name }}</p>
        <span class="cuenta-carnet"><i class="bi bi-person-vcard" aria-hidden="true"></i> Carnet {{ $reader->card_number }}</span>
    </div>
    <nav class="cuenta-nav" aria-label="Secciones de mi cuenta">
        @foreach ([['lector.reservas', 'Mis reservas', 'bi-bookmark-check'], ['lector.prestamos', 'Mis préstamos', 'bi-journal-bookmark'], ['lector.multas', 'Mis multas', 'bi-receipt']] as [$ruta, $texto, $icono])
            <a class="cuenta-tab {{ request()->routeIs($ruta) ? 'active' : '' }}" href="{{ route($ruta) }}" @if (request()->routeIs($ruta)) aria-current="page" @endif>
                <i class="bi {{ $icono }}" aria-hidden="true"></i><span>{{ $texto }}</span>
            </a>
        @endforeach
    </nav>
    <a href="{{ route('inicio') }}" class="cuenta-sidebar-catalogo"><i class="bi bi-search" aria-hidden="true"></i> Explorar catálogo</a>
</aside>
