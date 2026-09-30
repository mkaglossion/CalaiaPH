<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# RecruitmentHRIS

A modern **Recruitment & HR Information System** built with **Laravel 13** + **Vue 3** + **Inertia.js**.  
Features a hybrid architecture with Blade templates for public pages and a fully interactive Vue/Inertia dashboard for authenticated users.

## Tech Stack

- **Backend**: Laravel 13.34.0 (PHP 8.3)
- **Frontend**: Vue 3.5.13 + Inertia.js 2.0.0
- **Build Tool**: Vite 6.2.0
- **Styling**: Tailwind CSS 4.1.1
- **Database**: MariaDB
- **Testing**: PHPUnit 11.5.3
- **Security Scanning**: squeak/rat v1.2.1 (Python-based static analyzer)
- **Package Manager**: Composer + NPM

## Features

✅ **Authentication System**
- Email verification workflow
- Pending registration approval process
- Password reset functionality

✅ **Admin Panel**
- Admin profile & settings management
- System configuration controls
- Maintenance mode toggle
- Activity audit logs
- System alerts & notifications
- Enhanced dashboard with widgets

✅ **User Management**
- Profile management
- Password change
- Account deletion
- Settings & preferences

✅ **Security**
- Input validation & sanitization
- Cookie security hardening
- RAT (Python-native) deep security scanning
- Zero security findings after cleanup

## Requirements

- **PHP 8.3+**
- **Composer** (latest)
- **Node.js 18+** + **NPM**
- **Database**: MariaDB 10.5+ (recommended)
- **Git**
- **Python 3.9+** (optional, for RAT security scanning)

## Installation

### 1) Clone the repository

```bash
git clone https://github.com/mkaglossion22/RecruitmentHRIS.git
cd RecruitmentHRIS
```

### 2) Install backend dependencies

```bash
composer install
```

### 3) Create environment file

```bash
cp .env.example .env
```

### 4) Generate application key

```bash
php artisan key:generate
```

### 5) Configure database (MariaDB)

Update your `.env` file with MariaDB credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=recruitment_hris
DB_USERNAME=root
DB_PASSWORD=
```

### 6) Run migrations

```bash
php artisan migrate
```

### 7) Install frontend dependencies

```bash
npm install
```

### 8) Development servers

Start Vite (frontend build watcher):

```bash
npm run dev
```

In another terminal, start Laravel:

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

## Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

**Current Test Status**: ✅ 33 tests passing

## Security Scanning

Run the RAT (squeak/rat) security scanner:

```bash
# Quick scan
php artisan rat

# Deep analysis
php artisan rat --deep
```

**Current Status**: ✅ 0 security findings

## Project Structure

```
RecruitmentHRIS/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # API & application controllers
│   │   ├── Middleware/       # Route middleware (auth, appearance, etc)
│   │   └── Requests/         # Form request validation
│   ├── Models/               # Eloquent models
│   └── Notifications/        # Email notifications
├── resources/
│   ├── js/
│   │   ├── pages/           # Vue page components (Inertia)
│   │   ├── components/      # Reusable Vue components
│   │   ├── layouts/         # App layouts
│   │   └── types/           # TypeScript type definitions
│   ├── views/               # Blade templates (public pages)
│   └── css/                 # Tailwind stylesheets
├── routes/
│   ├── web.php              # Web routes
│   ├── auth.php             # Auth routes
│   └── settings.php         # Settings routes
├── database/
│   ├── migrations/          # Schema migrations
│   ├── factories/           # Model factories
│   └── seeders/             # Database seeders
└── tests/
    ├── Feature/             # Feature tests
    └── Unit/                # Unit tests
```

## Available Routes

### Public Routes
- `GET /` → Login page
- `GET /login` → Login form
- `GET /register` → Registration form
- `POST /register` → Create pending registration

### Authenticated Routes
- `GET /dashboard` → Main dashboard (requires email verification)
- `GET /profile` → User profile settings
- `PUT /profile` → Update profile
- `POST /profile` → Delete account

### Admin Routes
- `GET /admin/settings/profile` → Admin profile & preferences
- `GET /admin/settings/system` → System configuration
- `GET /admin/settings/alerts` → System alerts management
- `GET /admin/settings/activity-log` → Audit log viewer

## Useful Commands

### Database
```bash
php artisan migrate              # Run all pending migrations
php artisan migrate:rollback     # Rollback last batch
php artisan migrate:refresh      # Reset and re-run migrations
php artisan migrate:fresh --seed # Fresh database with seeds
php artisan db:seed              # Run seeders
```

### Development
```bash
php artisan serve                # Start local development server
php artisan route:list           # Display all registered routes
php artisan tinker               # Interactive shell (REPL)
php artisan optimize:clear       # Clear all caches
```

### Code Quality
```bash
php artisan test                 # Run all tests
php artisan test --filter=PasswordUpdate  # Run specific test
php artisan rat --deep           # Security analysis (requires Python)
```

### Cache Management
```bash
php artisan config:clear         # Clear config cache
php artisan config:cache         # Cache config for production
php artisan route:clear          # Clear route cache
php artisan view:clear           # Clear view cache
```

## Architecture

This project uses a **hybrid architecture**:

- **Blade Templates** for public pages (Login, Registration, Email Verification)
- **Inertia.js + Vue 3** for authenticated dashboard & admin panel
- **TypeScript** for type-safe frontend development
- **Tailwind CSS** for utility-first styling

### Data Flow
1. User requests page → Laravel routes to controller
2. Controller prepares data + renders via Inertia
3. Inertia passes data to Vue component
4. Vue component renders in browser with live reactivity

## Environment Setup

### Windows (Laragon)
```bash
# PHP & MariaDB already included
# Just ensure Python 3 for RAT scanning
python3 --version

# If python3 command missing, add to PATH or create wrapper:
# python3.cmd in project root maps to python executable
```

### macOS/Linux
```bash
# Install PHP, Node, MariaDB via Homebrew
brew install php@8.3 node mariadb

# Start MariaDB
brew services start mariadb
```

## Documentation

- **Laravel Docs**: https://laravel.com/docs
- **Inertia.js**: https://inertiajs.com
- **Vue 3**: https://vuejs.org
- **Tailwind CSS**: https://tailwindcss.com
- **squeak/rat**: https://github.com/squeak-security/rat

Laravel provides the application foundation with expressive routing, dependency injection, database migrations, Eloquent ORM, queues, caching, and authentication tools. See the [Laravel documentation](https://laravel.com/docs) for framework guidance and the [Laravel Learn](https://laravel.com/learn) tutorials for practical lessons.

For AI-assisted development, this project includes [Laravel Boost](https://laravel.com/docs/ai):

```bash
composer require laravel/boost --dev
php artisan boost:install
```

## License

This project is open-sourced under the **MIT License**. See the [LICENSE](LICENSE) file for details.
