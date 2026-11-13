@extends('layouts.admin')

@section('titulo', $lector ? 'Editar lector' : 'Nuevo lector')

@section('contenido')
<form action="{{ $lector ? route('admin.lectores.actualizar', $lector['id']) : route('admin.lectores.guardar') }}" method="POST" class="card admin-form-card border-0 shadow-sm">
    @csrf
    @if ($lector) @method('PUT') @endif
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombres" class="form-label">Nombres</label>
                <input type="text" name="first_name" id="nombres" class="form-control" value="{{ old('first_name', $lector['nombres'] ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label for="apellidos" class="form-label">Apellidos</label>
                <input type="text" name="last_name" id="apellidos" class="form-control" value="{{ old('last_name', $lector['apellidos'] ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label for="carnet" class="form-label">Carnet</label>
                <input type="text" name="card_number" id="carnet" class="form-control" value="{{ old('card_number', $lector['carnet'] ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label for="documento" class="form-label">Documento de identidad</label>
                <input type="text" name="document_number" id="documento" class="form-control" value="{{ old('document_number', $lector['documento'] ?? '') }}">
            </div>
            <div class="col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="tel" name="phone" id="telefono" class="form-control" value="{{ old('phone', $lector['telefono'] ?? '') }}">
            </div>
            <div class="col-md-4"><label for="email" class="form-label">Correo electrónico</label><input type="email" name="email" id="email" class="form-control" value="{{ old('email', $lector['email'] ?? '') }}"></div>
            @if (! $lector)
            <div class="col-12">
                <p class="form-text mb-1">Si agregas un correo, también crearás una cuenta de lector para iniciar sesión.</p>
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Contraseña inicial</label>
                <input type="password" name="password" id="password" class="form-control" minlength="8" autocomplete="new-password" aria-describedby="ayuda-password">
                <div id="ayuda-password" class="form-text">Obligatoria si agregas correo; mínimo 8 caracteres.</div>
            </div>
            <div class="col-md-6">
                <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="8" autocomplete="new-password">
            </div>
            @endif
            <div class="col-md-4">
                <label for="estado" class="form-label">Estado</label>
                <select name="status" id="estado" class="form-select">
                    <option value="active" @selected(old('status', ($lector['estado'] ?? 'Activo') === 'Activo' ? 'active' : 'suspended') === 'active')>Activo</option>
                    <option value="suspended" @selected(old('status', ($lector['estado'] ?? '') === 'Suspendido' ? 'suspended' : 'active') === 'suspended')>Suspendido</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer admin-form-footer border-0 p-4 pt-0 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar lector</button>
        <a href="{{ route('admin.lectores') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection
