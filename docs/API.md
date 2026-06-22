# Enterprise CMS API Guide

## Requirements

- PHP 8.4 with `bcmath`, `gd`, `intl`, `mbstring`, `pdo_mysql`, `redis`, and `zip`
- MySQL 8+
- Redis 7+
- Composer 2

For S3 media storage, install the Laravel adapter:

```bash
composer require league/flysystem-aws-s3-v3
```

For the required Pest runner:

```bash
composer require --dev pestphp/pest:^4.0 pestphp/pest-plugin-laravel:^4.0
```

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan queue:work
php artisan serve
```

Use Redis in production:

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

Use local public media with `FILESYSTEM_DISK=public`, or configure the AWS
variables and set `FILESYSTEM_DISK=s3`.

## Authentication

Base admin URL: `/api/v1/admin`

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/login` | Create Sanctum token |
| POST | `/logout` | Revoke current token |
| POST | `/forgot-password` | Email reset link |
| POST | `/reset-password` | Reset password |
| GET | `/profile` | Current user |
| PUT | `/profile` | Update profile |
| POST | `/change-password` | Change and revoke tokens |

Protected endpoints require:

```http
Authorization: Bearer {token}
Accept: application/json
```

## Modules

All CRUD resources use `GET`, `POST`, `GET /{id}`, `PUT|PATCH /{id}`, and
`DELETE /{id}` where applicable.

| Prefix | Module |
|---|---|
| `/users` | Users, status, roles, direct permissions |
| `/roles`, `/permissions` | Dynamic access control |
| `/pages` | Pages, publish state, SEO |
| `/posts` | Posts, images, categories, tags, SEO |
| `/categories` | Hierarchical categories |
| `/tags` | Tags |
| `/media` | Local/S3 uploads and metadata |
| `/menus` | Nested ordered navigation |
| `/settings` | Typed site configuration |
| `/contact-messages` | Inbox and read state |
| `/activity-logs` | Actor-based audit history |
| `/notifications` | Database notification inbox |
| `/dashboard/overview` | Counts and recent activity |

Public endpoints:

- `GET /api/v1/settings`
- `POST /api/v1/contact-messages`

## Response Contract

```json
{
  "success": true,
  "message": "Posts retrieved.",
  "data": {
    "items": [],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 0
    }
  }
}
```

Validation, authentication, authorization, and not-found errors use the same
envelope with an appropriate `401`, `403`, `404`, or `422` status.

## Operations

Production deployment should run:

```bash
php artisan migrate --force
php artisan optimize
php artisan queue:restart
```

Run a dedicated queue worker for email notifications and the scheduler for
maintenance tasks. The provided Compose stack includes both.
