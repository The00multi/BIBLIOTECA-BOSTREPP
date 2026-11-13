@php
    $colores = [
        'Activo' => 'success',
        'Disponible' => 'success',
        'Pagada' => 'success',
        'Completada' => 'success',
        'Pendiente' => 'warning',
        'Prestado' => 'warning',
        'Reservado' => 'info',
        'Vencido' => 'danger',
        'Lista para retirar' => 'info',
        'Vencida' => 'danger',
        'Dañado' => 'danger',
        'Suspendido' => 'danger',
        'Devuelto' => 'secondary',
        'Cancelada' => 'secondary',
        'Perdido' => 'dark',
    ];
@endphp
<span class="badge text-bg-{{ $colores[$estado] ?? 'secondary' }}">{{ $estado }}</span>
