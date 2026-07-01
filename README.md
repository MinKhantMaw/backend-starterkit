# CMS Backend

A Laravel 12 backend starter kit designed for admin-focused applications with authentication, role-based access control, audit logging, notifications, file uploads, and security settings.

## Features

- Authentication with Laravel Sanctum
- Two-factor authentication support for admin users
- User and role management
- Permission-based authorization via Spatie Permission
- Profile management and password updates
- Dashboard overview metrics
- Activity log tracking
- Database notifications
- Image and document upload endpoints
- OpenAPI/Swagger documentation

## Tech Stack

- PHP 8.4
- Laravel 12
- Laravel Sanctum
- Spatie Permission
- MySQL
- Redis
- Docker support

## Project Structure

- `app/Modules/Auth` - authentication and token flows
- `app/Modules/User` - user management
- `app/Modules/Role` - role management
- `app/Modules/Permission` - permission listing and authorization support
- `app/Modules/Profile` - profile and password management
- `app/Modules/Dashboard` - dashboard summary endpoints
- `app/Modules/ActivityLog` - audit trail endpoints
- `app/Modules/Notification` - notification APIs
- `app/Modules/File` - upload handlers
- `app/Modules/SecuritySetting` - 2FA and security configuration
- `routes/api.php` - API route definitions
- `docs/openapi.yaml` - OpenAPI specification

## Requirements

- PHP 8.4+
- Composer
- Database server (MySQL is the default setup)
- Redis (optional for some queue/notification flows)

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy the environment file and configure your database settings:
   ```bash
   cp .env.example .env
   ```
4. Generate the application key:
   ```bash
   php artisan key:generate
   ```
5. Run database migrations:
   ```bash
   php artisan migrate
   ```
6. Start the development server:
   ```bash
   php artisan serve
   ```

## API Documentation

The project includes Swagger UI and an OpenAPI spec.

- Swagger UI: `/api/swagger`
- OpenAPI YAML: `/api/openapi`

## Main API Endpoints

### Authentication
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/2fa/challenge`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/refresh`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/change-password`

### Profile and Dashboard
- `GET /api/v1/profile`
- `PUT /api/v1/profile`
- `POST /api/v1/profile/change-password`
- `POST /api/v1/profile/avatar`
- `GET /api/v1/dashboard/overview`

### Users, Roles, and Permissions
- `GET|POST /api/v1/users`
- `GET|PUT|DELETE /api/v1/users/{user}`
- `PATCH /api/v1/users/{user}/status`
- `PATCH /api/v1/users/{user}/assign-role`
- `GET|POST /api/v1/roles`
- `GET|PUT|DELETE /api/v1/roles/{role}`
- `PATCH /api/v1/roles/{role}/permissions`
- `GET /api/v1/permissions`

### Notifications, Files, and Logs
- `GET /api/v1/notifications`
- `GET /api/v1/notifications/unread-count`
- `PATCH /api/v1/notifications/{notification}/read`
- `PATCH /api/v1/notifications/read-all`
- `POST /api/v1/files/images`
- `POST /api/v1/files/documents`
- `GET /api/v1/activity-logs`

### Security Settings
- `GET /api/v1/security-settings`
- `PUT /api/v1/security-settings`
- `GET /api/v1/security-settings/2fa/setup`
- `POST /api/v1/security-settings/2fa/confirm`
- `POST /api/v1/security-settings/2fa/disable`

## Testing

Run the test suite with:

```bash
php artisan test
```
