# Laravel 12 Enterprise Backend Starter Kit

A reusable backend foundation for ERP, CRM, estate management, inventory management, HR management, and accounting systems.

This is not a CMS. It intentionally excludes settings, posts, pages, categories, tags, and media library domain modules.

## Stack

- Laravel 12
- PHP 8.4
- MySQL
- Redis
- Laravel Sanctum
- Spatie Permission
- Database notifications

## Modules

- `app/Modules/Auth`
- `app/Modules/User`
- `app/Modules/Role`
- `app/Modules/Permission`
- `app/Modules/Profile`
- `app/Modules/ActivityLog`
- `app/Modules/Notification`
- `app/Modules/File`

## Core API

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/change-password`
- `GET /api/v1/auth/me`
- `GET|PUT /api/v1/profile`
- `POST /api/v1/profile/change-password`
- `POST /api/v1/profile/avatar`
- `apiResource /api/v1/users`
- `PATCH /api/v1/users/{user}/assign-role`
- `PATCH /api/v1/users/{user}/status`
- `apiResource /api/v1/roles`
- `PATCH /api/v1/roles/{role}/permissions`
- `apiResource /api/v1/permissions`
- `GET /api/v1/activity-logs`
- `GET /api/v1/notifications`
- `GET /api/v1/notifications/unread-count`
- `PATCH /api/v1/notifications/{notification}/read`
- `PATCH /api/v1/notifications/read-all`
- `POST /api/v1/files/images`
- `POST /api/v1/files/documents`

OpenAPI YAML is served from `GET /api/openapi`.
