# Arunika — Learning Operating System

> Platform LMS OBE-native, AI-assisted, dan engaging untuk perguruan tinggi di Indonesia.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade + Livewire (Volt) + Alpine.js
- **Styling**: Tailwind CSS v4 (CSS-first config)
- **Admin**: Filament (Super Admin & Admin Kampus)
- **Database**: MySQL 8.4 LTS, Redis
- **Search**: Meilisearch (via Laravel Scout)
- **Real-time**: Laravel Reverb
- **AI**: Gemini API (pluggable via AiProvider interface)

## Prerequisites

- PHP 8.2+ with extensions: pdo_mysql, redis, gd, zip, mbstring
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.4+ or Docker
- Redis 7+
- Meilisearch (optional, for full-text search)

## Quick Start

```bash
# Clone the repo
git clone <repo-url> arunika
cd arunika

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build assets
npm run dev

# Start the server
php artisan serve
```

### With Docker (Sail)

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

## Project Structure

```
app/
├── Actions/          # Single-purpose action classes
├── Contracts/        # Interfaces (AiProvider, VectorStore)
├── DTOs/             # Data Transfer Objects
├── Enums/            # PHP Enums
├── Filament/         # Filament admin resources
├── Http/             # Controllers, Middleware, Requests
├── Jobs/             # Queued jobs
├── Livewire/         # Livewire components
├── Models/           # Eloquent models
├── Policies/         # Authorization policies
├── Services/         # Business logic services
└── View/Components/  # Blade components
```

## Documentation

- [Architecture](docs/ARCHITECTURE.md)
- [Design Decisions](docs/decisions.md)
- [ERD](docs/erd.md)
- [API Reference](docs/API.md)

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Code style
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse
```

## License

Proprietary — All rights reserved.
