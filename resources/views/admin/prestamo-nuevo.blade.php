@extends('layouts.admin')

@section('titulo', 'Nuevo préstamo')

@section('contenido')
<form action="{{ route('admin.prestamos.guardar') }}" method="POST" class="card admin-form-card admin-form-card-limited border-0 shadow-sm">
    @csrf
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label for="lector" class="form-label">Lector</label>
                <select name="reader_id" id="lector" class="form-select" required>
                    <option value="">Selecciona un lector</option>
                    @foreach ($lectores as $l)
                        <option value="{{ $l['id'] }}" @selected(old('reader_id') == $l['id'])>{{ $l['carnet'] }} - {{ $l['nombres'] }} {{ $l['apellidos'] }}</option>
                    @endforeach
                </select>
                <div class="form-text">Solo aparecen lectores activos.</div>
            </div>

            <div class="col-12">
                <label for="ejemplar" class="form-label">Ejemplar</label>
                <select name="copy_id" id="ejemplar" class="form-select" required>
                    <option value="">Selecciona un ejemplar</option>
                    @foreach ($ejemplares as $e)
                        <option value="{{ $e['id'] }}" data-reserved-reader="{{ $e['reserved_reader_id'] ?? '' }}" @selected(old('copy_id') == $e['id'])>{{ $e['codigo'] }} - {{ $e['libro'] }}{{ $e['reserved_reader'] ? ' — reservado para '.$e['reserved_reader'] : '' }}</option>
                    @endforeach
                </select>
                <div class="form-text">Se muestran ejemplares disponibles y reservas asignadas a ese lector.</div>
            </div>

            <div class="col-md-6">
                <label for="fecha-prestamo" class="form-label">Fecha de préstamo</label>
                <input type="date" id="fecha-prestamo" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
            </div>
            <div class="col-md-6">
                <label for="fecha-limite" class="form-label">Fecha límite de devolución</label>
                <input type="date" name="due_at" id="fecha-limite" class="form-control" value="{{ old('due_at', now()->addDays(config('biblioteca.loan_period_days'))->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" max="{{ now()->addDays(config('biblioteca.loan_period_days'))->format('Y-m-d') }}" required>
                <div class="form-text">Plazo máximo: {{ config('biblioteca.loan_period_days') }} días.</div>
            </div>
        </div>
    </div>
    <div class="card-footer admin-form-footer border-0 p-4 pt-0 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Registrar préstamo</button>
        <a href="{{ route('admin.prestamos') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const lectorPrestamo = document.getElementById('lector');
    const ejemplarPrestamo = document.getElementById('ejemplar');
    const filtrarEjemplares = () => {
        const lectorId = lectorPrestamo.value;
        [...ejemplarPrestamo.options].forEach(opcion => {
            if (!opcion.value) return;
            const reservadoPara = opcion.dataset.reservedReader;
            opcion.disabled = reservadoPara !== '' && reservadoPara !== lectorId;
            if (opcion.disabled && opcion.selected) ejemplarPrestamo.value = '';
        });
    };
    lectorPrestamo.addEventListener('change', filtrarEjemplares);
    filtrarEjemplares();
</script>
@endpush
