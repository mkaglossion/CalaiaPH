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

The PHPUnit configuration uses an in-memory SQLite database and isolated testing environment.

## Database safety

- `php artisan migrate:fresh --seed` drops all tables before reseeding. Use it only when you intend to erase the selected database.
- `tools/db-safe.ps1 -DumpPath "<dump.sql>" -Force` backs up `calaiaph`, imports the dump into a new database copy, verifies it, and prints the phpMyAdmin URL.
- `tools/db-safe.ps1 -RestoreDatabase <name>` checks an existing restore copy.
- `-Force` skips the confirmation prompt during import.

## Laravel documentation

- [Laravel 13 documentation](https://laravel.com/docs/13.x)
- [Laravel 13 upgrade guide](https://laravel.com/docs/13.x/upgrade)
