<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="380" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-%5E8.2-777BB4?style=flat&logo=php&logoColor=white" alt="PHP ^8.2">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel 12.x">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=flat&logo=alpine.js&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=flat&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Vite-8.x-646CFF?style=flat&logo=vite&logoColor=white" alt="Vite 8">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

# CalaiaPH

A booking and content-management platform for CalaiaPH, built with **Laravel 12.x**. The website supports staycations, day visits, tours, activities, packages, and dining, with an admin panel for managing bookings, reservations, inventory, pricing, and site content.

## Tech Stack

- **Backend**: Laravel 12.x (PHP ^8.2)
- **Frontend**: Laravel Blade + Alpine.js
- **Build Tool**: Vite 8
- **Styling**: Tailwind CSS 4
- **Database**: MySQL/MariaDB (SQLite for automated tests)
- **Testing**: PHPUnit 11
- **Payments**: PayMongo
- **Media Storage**: Cloudinary PHP SDK
- **Authentication**: Laravel Socialite
- **Broadcasting**: Laravel Echo + Pusher
- **Package Managers**: Composer + npm

## Features

✅ **Guest Bookings**
- Staycation and day-visit booking flows
- Availability calendars, inventory, and dynamic pricing
- Activities, packages, add-ons, and dining reservations
- Booking confirmations, payment processing, and camp waivers

✅ **Admin Panel**
- Booking dashboard, reservation monitoring, and exports
- Inventory and pricing management
- Activities, packages, user, and site content management
- Activity logging and operational alerts

✅ **Integrations**
- PayMongo checkout and payment webhook handling
- Cloudinary image uploads and delivery
- Google and Facebook sign-in
- Weather information and optional real-time broadcasting

✅ **Security**
- Request validation and API security middleware
- Rate-limited booking, upload, and API endpoints
- Configurable session and cookie security settings
- Optional RAT static security scanning

## Requirements

- **PHP ^8.2** with required Laravel extensions
- **Composer 2**
- **Node.js 22+** + **npm**
- **Database**: MySQL or MariaDB for local development
- **Git**
- **Python 3.9+** (optional, for RAT scanning)

## Installation

### 1) Clone the repository

```bash
git clone https://github.com/mkaglossion/CalaiaPH.git
cd CalaiaPH
```

### 2) Install dependencies

```bash
composer install
npm ci
```

### 3) Create the environment file

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

macOS/Linux:

```bash
cp .env.example .env
```

### 4) Generate the application key

```bash
php artisan key:generate
```

### 5) Configure the database

Update `.env` with your local MySQL/MariaDB credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=calaiaph
DB_USERNAME=root
DB_PASSWORD=
```

Configure integration credentials as needed. See `.env.example` for PayMongo, Cloudinary, mail, weather, Pusher, and Google/Facebook OAuth settings. Keep real credentials private and use test credentials for local development.

### 6) Run migrations

Confirm `.env` points to the intended local database before running migrations:

```bash
php artisan migrate
```

### 7) Build frontend assets

```bash
npm run build
```

### 8) Start the development servers

Start Laravel, the queue listener, and Vite together:

```bash
composer run dev
```

Or start them separately in different terminals:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

## Running Tests

Run the configured feature tests with:

```bash
php artisan test
```

Tests use an in-memory SQLite database configured in `phpunit.xml`; a local MySQL database is not needed to run them.

## Production Deployment (PuTTY/SSH)

Deploy the matching `composer.json` and `composer.lock` files before installing dependencies. Laravel 12 requires PHP ^8.2. Confirm the production PHP runtime before installing dependencies, and do not run `composer update` on the production server.

```bash
cd /home/dwmngr/web/CalaiaPH
php -v
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan optimize:clear
php artisan optimize
composer check-platform-reqs --no-dev
```

If the application is deployed in a different directory, replace the `cd` path. Build frontend assets locally with `npm run build` and deploy the resulting `public/build` directory when Node.js is not available on the production server. Never copy `.env` from local development; configure production credentials directly on the server.

## Security Scanning

Run the optional RAT (`squeak/rat`) security scanner:

```bash
# Quick scan
php artisan rat

# Deep analysis
php artisan rat --deep
```

Review scan output against the current code and configuration; results can change as the project changes.

## Project Structure

```text
CalaiaPH/
├── app/
│   ├── Helpers/             # Application helper classes
│   ├── Http/
│   │   ├── Controllers/     # Website, API, and admin controllers
│   │   ├── Middleware/      # Request and access middleware
│   │   └── Requests/        # Form request validation
│   ├── Models/              # Eloquent models
│   ├── Providers/           # Service providers
│   └── Services/            # Booking, payment, and integration services
├── config/                  # Application and integration configuration
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database schema migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── css/                 # Stylesheets
│   ├── js/                  # Frontend scripts
│   ├── mail/                # Mail resources
│   └── views/               # Blade templates
├── routes/
│   ├── api.php              # API routes
│   ├── channels.php         # Broadcast channels
│   ├── console.php          # Artisan schedules and commands
│   └── web.php              # Website and admin routes
└── tests/
    ├── Feature/             # Feature tests
    └── Unit/                # Unit tests
```

## Available Routes

### Public Routes

- `GET /` → CalaiaPH home page
- `GET /our-story` → Our story
- `GET /sustainability` → Sustainability information
- `GET /our-services` → Guest services
- `GET /staycation` → Staycation information
- `GET /daytour` → Day-tour information
- `GET /packages` → Packages
- `GET /activities` → Activities
- `GET /dining` → Dining

### Booking Routes

- `GET /booking` → Start a booking
- `GET /booking/calendar` → Booking calendar
- `GET /booking/staycation/activities` → Select staycation activities
- `GET /booking/dayvisit` → Day-visit booking
- `GET /booking/payment` → Booking payment step

### Authenticated Routes

- `GET /profile` → View customer profile
- `GET /profile/edit` → Edit customer profile
- `GET /booking/history` → View booking history
- `GET /booking/cart` → View booking cart

### Admin Routes

- `GET /admin/login` → Admin sign-in
- `GET /admin/dashboard` → Admin dashboard
- `GET /admin/bookings` → Manage bookings
- `GET /admin/bookings/inventory` → Manage booking inventory
- `GET /admin/reservations/monitor` → Monitor reservations

## Useful Commands

### Database

```bash
php artisan migrate                # Run pending migrations
php artisan migrate:rollback       # Roll back the last migration batch
php artisan db:seed                # Run database seeders
```

> **Warning:** `php artisan migrate:fresh --seed` drops every table in the configured database before reseeding. Run it only when you intend to erase that database.

For safer local database dump verification on Windows, use `tools/db-safe.ps1`:

```powershell
.\tools\db-safe.ps1 -DumpPath "<dump.sql>"
.\tools\db-safe.ps1 -RestoreDatabase <name>
```

### Development

```bash
php artisan serve                  # Start the Laravel development server
php artisan route:list             # List registered routes
php artisan tinker                 # Open the Laravel REPL
php artisan queue:failed           # List failed queue jobs
php artisan optimize:clear         # Clear framework caches
npm run dev                        # Start the Vite development server
npm run build                      # Build production frontend assets
```

### Code Quality and Maintenance

```bash
php artisan test                   # Run automated tests
php artisan rat --deep             # Run deep RAT security analysis
php artisan schedule:list          # List scheduled tasks
php artisan config:clear            # Clear cached configuration
php artisan route:clear             # Clear cached routes
php artisan view:clear              # Clear compiled Blade views
```

## Architecture

CalaiaPH uses Laravel-rendered Blade pages with Alpine.js for interactive behavior and Tailwind CSS for styling. Vite builds the frontend assets. Laravel routes requests to controllers and services, which use Eloquent models to work with the database. Queued work and scheduled Artisan commands support background tasks and booking workflows.

Payment processing, media storage, social sign-in, weather data, and optional broadcasting are configured through environment variables and integrated with the Laravel application.

## Environment Setup

### Windows (Laragon)

Install PHP ^8.2, Composer, Node.js 22+, npm, and MySQL/MariaDB. Copy `.env.example` to `.env` using PowerShell, set your local database and integration values, then follow the installation steps above.

### macOS/Linux

Install PHP ^8.2 with the Laravel-required extensions, Composer 2, Node.js 22+, npm, and MySQL/MariaDB using your package manager. Copy `.env.example` to `.env`, configure your local settings, and follow the installation steps above.

## Documentation

- **Laravel 12**: https://laravel.com/docs/12.x
- **Laravel Boost**: https://laravel.com/docs/ai
- **Alpine.js**: https://alpinejs.dev/
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Vite**: https://vite.dev/guide/
- **PayMongo**: https://developers.paymongo.com/
- **Cloudinary PHP SDK**: https://cloudinary.com/documentation/php_integration

Laravel provides the application foundation with expressive routing, dependency injection, database migrations, Eloquent ORM, queues, caching, and testing tools. See the [Laravel documentation](https://laravel.com/docs/12.x) for framework guidance and [Laravel Learn](https://laravel.com/learn) for practical lessons.

For AI-assisted development, this project includes [Laravel Boost](https://laravel.com/docs/ai) as a development dependency. To install and configure Boost locally:

```bash
composer require laravel/boost --dev
php artisan boost:install
```

## License

The Composer project metadata declares this application as MIT-licensed. A separate `LICENSE` file is not currently included in the repository.
