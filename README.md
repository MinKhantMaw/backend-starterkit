# Enterprise CMS Backend API

Production-oriented Laravel 12 API for an enterprise content management system.

## Stack

- PHP 8.4 (Docker runtime)
- Laravel 12 and Sanctum
- MySQL 8.4 and Redis 7
- Spatie Laravel Permission
- Pest 4
- OpenAPI 3.1

## Start With Docker

```bash
cp .env.example .env
docker compose build
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan storage:link
docker compose exec app php artisan migrate --seed
```

API: `http://localhost:8000/api/v1`

Default credentials are controlled by `SUPER_ADMIN_EMAIL` and
`SUPER_ADMIN_PASSWORD`. Change them before running the production seeder.

## Documentation

- [Architecture](docs/ARCHITECTURE.md)
- [Database schema](docs/DATABASE.md)
- [API and environment guide](docs/API.md)
- [OpenAPI specification](docs/openapi.yaml)

## Quality Checks

```bash
composer require --dev pestphp/pest:^4.0 pestphp/pest-plugin-laravel:^4.0
./vendor/bin/pest
./vendor/bin/pint --test
php artisan route:list --path=api
```

The current lockfile retains the repository's original PHPUnit installation
because the development environment could not reach Packagist. Pest runs the
committed tests unchanged after the command above is run in the PHP 8.4 image.
