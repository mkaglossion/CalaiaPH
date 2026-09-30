# CalaiaPH

CalaiaPH is a Laravel web application for managing bookings, staycations, day tours, activities, packages, and related content.

## Requirements

- PHP 8.3 or later, with the extensions required by Laravel and the project
- Composer 2
- Node.js 22 and npm
- MySQL/MariaDB, or SQLite for local development and tests

This project uses Laravel 13 and PHPUnit 12. The Cloudinary PHP SDK is used directly for media uploads.

## Local setup

1. Install PHP and Composer dependencies:

   ```powershell
   composer install
   ```

2. Create your local environment file and application key:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

3. Configure the database connection in `.env`. Set `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` for MySQL/MariaDB, or configure SQLite.

4. Configure Cloudinary in `.env` if you need media uploads. Set `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, and `CLOUDINARY_API_SECRET`, or provide `CLOUDINARY_URL`. `CLOUDINARY_UPLOAD_FOLDER` is optional.

5. Install frontend dependencies and build the assets:

   ```powershell
   npm ci
   npm run build
   ```

6. Run database migrations after confirming the configured database is the intended local database:

   ```powershell
   php artisan migrate
   ```

## Development

Start the Laravel server:

```powershell
php artisan serve
```

Or run the project's combined development command, which starts the web server, queue listener, and Vite:

```powershell
composer run dev
```

Useful commands:

```powershell
php artisan route:list
php artisan queue:failed
php artisan optimize:clear
npm run dev
npm run build
```

## Tests

Run the feature tests with:

```powershell
php artisan test
```

The PHPUnit configuration uses an in-memory SQLite database and isolated testing environment.

## Database safety

- `php artisan migrate:fresh --seed` drops all tables before reseeding. Use it only when you intend to erase the selected database.
- `tools/db-safe.ps1 -DumpPath "<dump.sql>" -Force` backs up `calaiaph`, imports the dump into a new database copy, verifies it, and prints the phpMyAdmin URL.
- `tools/db-safe.ps1 -RestoreDatabase <name>` checks an existing restore copy.
- `-Force` skips the confirmation prompt during import.

## Laravel documentation

- [Laravel 13 documentation](https://laravel.com/docs/13.x)
- [Laravel 13 upgrade guide](https://laravel.com/docs/13.x/upgrade)
