@extends('layouts.publico')

@section('titulo', 'Crear cuenta')

@section('contenido')
<section class="login-pagina" aria-labelledby="titulo-registro">
    <div class="login-bienvenida login-bienvenida-registro">
        <div class="login-simbolo"><i class="bi bi-book-half" aria-hidden="true"></i></div>
        <span class="login-eyebrow">Únete a la biblioteca</span>
        <h1>Tu próxima lectura empieza aquí.</h1>
        <p>Crea tu cuenta de lector en un minuto.</p>
        <ul class="login-beneficios">
            <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Reserva libros que aún no están disponibles</li>
            <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Consulta tus préstamos y fechas de devolución</li>
            <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Comparte tu opinión sobre lo que lees</li>
        </ul>
        <a href="{{ route('inicio') }}" class="login-volver"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al catálogo</a>
    </div>

    <div class="login-formulario">
        <div class="login-card" style="max-width: 560px">
            <div class="login-card-icono"><i class="bi bi-person-plus" aria-hidden="true"></i></div>
            <span class="cuenta-eyebrow">Cuenta de lector</span>
            <h2 id="titulo-registro">Crear cuenta</h2>
            <p class="login-descripcion">Completa tus datos para registrarte.</p>

            <form action="{{ route('registro.procesar') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-sm-6">
                    <label for="nombres" class="form-label">Nombres</label>
                    <input type="text" name="first_name" id="nombres" class="form-control" value="{{ old('first_name') }}" autocomplete="given-name" required>
                </div>
                <div class="col-sm-6">
                    <label for="apellidos" class="form-label">Apellidos</label>
                    <input type="text" name="last_name" id="apellidos" class="form-control" value="{{ old('last_name') }}" autocomplete="family-name" required>
                </div>
                <div class="col-sm-6">
                    <label for="documento" class="form-label">Documento de identidad</label>
                    <input type="text" name="document_number" id="documento" class="form-control" value="{{ old('document_number') }}">
                </div>
                <div class="col-sm-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" name="phone" id="telefono" class="form-control" value="{{ old('phone') }}" autocomplete="tel">
                </div>
                <div class="col-12">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <div class="input-group login-input">
                        <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                        <input type="email" name="email" id="correo" class="form-control" placeholder="nombre@correo.com" value="{{ old('email') }}" autocomplete="email" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="clave" class="form-label">Contraseña</label>
                    <div class="input-group login-input">
                        <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                        <input type="password" name="password" id="clave" class="form-control" minlength="8" autocomplete="new-password" aria-describedby="ayuda-clave" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="clave2" class="form-label">Repite la contraseña</label>
                    <div class="input-group login-input">
                        <span class="input-group-text"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>
                        <input type="password" name="password_confirmation" id="clave2" class="form-control" minlength="8" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="col-12">
                    <div id="ayuda-clave" class="form-text mt-0">Usa al menos 8 caracteres.</div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary login-submit w-100"><i class="bi bi-person-check me-2" aria-hidden="true"></i>Crear mi cuenta</button>
                </div>
            </form>

            @if ($errors->any())<div class="alert alert-danger mt-3" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="login-demo-nota"><i class="bi bi-info-circle" aria-hidden="true"></i> Tu carnet de lector se generará al crear la cuenta.</div>
            <p class="login-registro mb-0">¿Ya tienes cuenta? <a href="{{ route('login') }}">Ingresar</a></p>
        </div>
    </div>
</section>
@endsection
