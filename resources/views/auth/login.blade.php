@extends('layouts.publico')

@section('titulo', 'Ingresar')

@section('contenido')
<section class="login-pagina" aria-labelledby="titulo-ingreso">
    <aside class="login-bienvenida login-bienvenida-ingreso">
        <div class="login-simbolo"><i class="bi bi-book-half" aria-hidden="true"></i></div>
        <span class="login-eyebrow">Tu biblioteca, contigo</span>
        <h1>Vuelve a tus historias.</h1>
        <p>Ingresa para tener a mano la actividad de tu biblioteca.</p>
        <ul class="login-beneficios">
            <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Revisa tus préstamos y fechas de devolución</li>
            <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Consulta tus reservas</li>
            <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Descubre nuevas lecturas</li>
        </ul>
        <span class="login-ilustracion" aria-hidden="true"><i class="bi bi-book-half"></i></span>
    </aside>

    <div class="login-formulario">
        <div class="login-card">
            <div class="login-card-icono"><i class="bi bi-person-lock" aria-hidden="true"></i></div>
            <span class="cuenta-eyebrow">Acceso de usuario</span>
            <h2 id="titulo-ingreso">Ingresar</h2>
            <p class="login-descripcion">Ingresa con tu cuenta de lector o de administración.</p>

            <form action="{{ route('login.procesar') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <div class="input-group login-input">
                        <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                        <input type="email" name="email" id="correo" class="form-control" placeholder="nombre@correo.com" value="{{ old('email') }}" autocomplete="email" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="clave" class="form-label">Contraseña</label>
                    <div class="input-group login-input">
                        <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                        <input type="password" name="password" id="clave" class="form-control" autocomplete="current-password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary login-submit w-100"><i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>Ingresar a mi cuenta</button>
            </form>

            @if ($errors->any())<div class="alert alert-danger mt-3" role="alert">{{ $errors->first() }}</div>@endif
            <div class="login-demo-nota"><i class="bi bi-info-circle" aria-hidden="true"></i> Usa el correo que registraste y tu contraseña.</div>
            <p class="login-registro mb-0">¿Aún no tienes cuenta? <a href="{{ route('registro') }}">Crear una cuenta</a></p>
        </div>
    </div>
</section>
@endsection
