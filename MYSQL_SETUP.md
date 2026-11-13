# Biblioteca — preparación para MySQL

Este proyecto está configurado para utilizar MySQL mediante la conexión nativa de Laravel (`pdo_mysql`). La creación de la base de datos y la administración de sus credenciales quedan a cargo del administrador del proyecto.

## Requisitos

- PHP 8.4.1 o superior, requerido por las dependencias instaladas.
- Extensión PHP `pdo_mysql` habilitada.
- Composer.
- Node.js y npm para compilar los recursos frontend.
- Una instancia MySQL accesible.

En Windows, comprueba la versión de PHP que usa la terminal con `php -v`. `pdo_mysql` también debe aparecer en `php -m`.

## Configuración local

1. Crear manualmente una base de datos MySQL llamada `biblioteca` (o usar el nombre que se prefiera).
2. Instalar dependencias: `composer install`.
3. Copiar `.env.example` como `.env`.
4. Para el trabajo diario, crear un usuario exclusivo para la aplicación con permisos `SELECT`, `INSERT`, `UPDATE` y `DELETE` únicamente sobre el esquema de la biblioteca. No conectar Laravel como `root`. Configurar `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` con ese usuario.
5. Generar la clave: `php artisan key:generate`.
6. Instalar y compilar frontend: `npm install` y `npm run build`.
7. Configurar `BIBLIOTECA_ADMIN_EMAIL` y `BIBLIOTECA_ADMIN_PASSWORD` en `.env` para crear el usuario administrador inicial.
8. Ejecutar `php artisan migrate --seed` para crear el esquema y cargar el catálogo de muestra. Las migraciones requieren permisos de DDL; ejecútelas con una cuenta de despliegue con permisos sobre el esquema y conserva las credenciales de esa cuenta fuera del `.env` operativo cuando sea posible.
9. Ejecutar `php artisan storage:link` para habilitar las portadas cargadas.
10. Iniciar: `php artisan serve`.

## Importante sobre el estado actual

La conexión MySQL está soportada por `config/database.php` y `.env.example` usa MySQL por defecto. Las migraciones crean libros, categorías, ejemplares, cuentas, lectores, préstamos, reservas y multas. El seeder carga títulos y movimientos de muestra y crea el administrador solo si se configuran su correo y contraseña. Las solicitudes se encolan por fecha; al regresar o agregarse un ejemplar compatible, se asigna a la solicitud más antigua y se reserva durante tres días.

Comprueba `php -v` en la misma terminal antes de ejecutar Artisan. Las rutas administrativas y de cuenta están protegidas por autenticación y rol.

No se incluye un `.env` real ni credenciales. Genere su propio archivo local a partir de `.env.example`. Nunca distribuyas `.env`, `database/database.sqlite` ni archivos de respaldo con datos o contraseñas; entrega el código y el esquema mediante migraciones.

## Liberación automática de reservas

La tarea programada libera las reservas listas que vencieron y vuelve a asignar el ejemplar a la siguiente solicitud. En desarrollo local, deja activo `php artisan schedule:work`. En un servidor, configura el programador del sistema para ejecutar `php artisan schedule:run` cada minuto desde la carpeta del proyecto. En Windows se puede crear una tarea del Programador de tareas con esa frecuencia.
