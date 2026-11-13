# BibliotecaApp

Aplicación de biblioteca desarrollada con Laravel y MySQL. El proyecto administra libros, categorías, ejemplares, lectores, préstamos, reservas y multas.

## Requisitos

- PHP 8.4.1 o superior con `pdo_mysql`.
- Composer.
- MySQL 8 o MariaDB.
- Node.js y npm.

## Instalación

1. Clona el repositorio y entra a la carpeta del proyecto.
2. Instala las dependencias de PHP y JavaScript:

   ```bash
   composer install
   npm install
   ```

3. Crea una base de datos vacía llamada `biblioteca` en MySQL.
4. Copia `.env.example` como `.env` y configura la conexión `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. No uses ni publiques credenciales reales en GitHub.
5. Genera la clave de Laravel. Luego elige **una** forma de preparar el esquema:

   ```bash
   php artisan key:generate
   ```

   En desarrollo, otorga temporalmente permisos para crear tablas al usuario de MySQL configurado y ejecuta las migraciones con datos de muestra:

   ```bash
   php artisan migrate --seed
   ```

   O importa `database/schema.sql` en una base vacía desde Workbench. Ese archivo solo contiene la estructura y no se combina con `migrate` en la misma base.

6. Compila los recursos y arranca la aplicación:

   ```bash
   npm run build
   php artisan serve
   ```

Abre la dirección local que muestra Artisan. Para instrucciones adicionales de MySQL, consulta [MYSQL_SETUP.md](MYSQL_SETUP.md).

## Base de datos

Las migraciones de `database/migrations` crean las tablas y relaciones. El seeder carga registros de muestra. `database/schema.sql` contiene una exportación del esquema sin datos; impórtala solo en una base vacía. En una instalación normal, usa `php artisan migrate --seed`.

## Desarrollo

Para compilar después de cambiar CSS o JavaScript, ejecuta `npm run build`. Para ejecutar las pruebas con una instalación de desarrollo compatible:

```bash
php artisan test
```

No subas `.env`, contraseñas, `database/database.sqlite`, `vendor` ni `node_modules` al repositorio.
