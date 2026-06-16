# CMS Admin Backend API

Laravel 12 backend API for an admin dashboard CMS. It uses PHP 8.2+, MySQL, Laravel Sanctum Bearer tokens, and Spatie Laravel Permission.

## Installation

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan serve
```

Install commands used to create this project:

```bash
composer create-project laravel/laravel backend "^11.0"
composer update
composer require laravel/sanctum spatie/laravel-permission
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Laravel 11 was initially requested, but current Laravel 11 framework packages were blocked by Composer security advisories, so the backend targets Laravel 12.62.0.

## Environment

```env
APP_NAME="CMS Admin API"
APP_URL=http://localhost:8000
FRONTEND_URLS=http://localhost:5173,http://127.0.0.1:5173

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms_admin_api
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

Create the MySQL database before running migrations:

```sql
CREATE DATABASE cms_admin_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Default Super Admin:

```text
email: admin@example.com
password: password
```

## Response Format

Success:

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## Routes

Base prefix: `/api/v1/admin`

Authentication:

- `POST /login`
- `POST /logout`
- `GET /profile`
- `POST /change-password`

Dashboard:

- `GET /dashboard/overview`

Users:

- `GET /users?search=&status=active&role=Admin&per_page=15`
- `POST /users`
- `GET /users/{id}`
- `PUT /users/{id}`
- `DELETE /users/{id}`
- `PATCH /users/{id}/status`
- `PATCH /users/{id}/assign-role`

Roles and permissions:

- `GET /roles`
- `POST /roles`
- `GET /roles/{id}`
- `PUT /roles/{id}`
- `DELETE /roles/{id}`
- `PATCH /roles/{id}/permissions`
- `GET /permissions`

Contents:

- `GET /contents?search=&status=draft&date_from=2026-01-01&date_to=2026-12-31&per_page=15`
- `POST /contents`
- `GET /contents/{id}`
- `PUT /contents/{id}`
- `DELETE /contents/{id}`
- `PATCH /contents/{id}/publish`
- `PATCH /contents/{id}/unpublish`

All protected routes require `Authorization: Bearer {access_token}` and `Accept: application/json`.

## Example Requests

Login:

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

Create user:

```json
{
  "name": "Editor User",
  "email": "editor@example.com",
  "password": "password",
  "password_confirmation": "password",
  "is_active": true,
  "role": "Editor"
}
```

Assign role:

```json
{
  "role": "Admin"
}
```

Create role:

```json
{
  "name": "Publisher",
  "permissions": ["content.view", "content.create", "content.update", "content.publish"]
}
```

Create content:

```json
{
  "title": "First Article",
  "excerpt": "Short summary",
  "body": "Full content body",
  "status": "draft"
}
```

For `featured_image`, send `multipart/form-data`. If uploading a file during update from a browser client, use `POST /contents/{id}` with `_method=PUT`.

## Example Responses

Login response:

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "token_type": "Bearer",
    "access_token": "1|token",
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "admin@example.com",
      "is_active": true,
      "roles": ["Super Admin"],
      "permissions": ["user.view", "user.create"]
    }
  }
}
```

Dashboard response:

```json
{
  "success": true,
  "message": "Dashboard overview retrieved.",
  "data": {
    "total_users": 1,
    "total_active_users": 1,
    "total_roles": 4,
    "total_permissions": 14,
    "total_contents": 0,
    "total_published_contents": 0,
    "total_draft_contents": 0,
    "recent_users": [],
    "recent_contents": []
  }
}
```

## Postman Collection Structure

- CMS Admin API
- Auth: Login, Profile, Change Password, Logout
- Dashboard: Overview
- Users: List, Create, Show, Update, Delete, Status, Assign Role
- Roles: List, Create, Show, Update, Delete, Assign Permissions
- Permissions: List
- Contents: List, Create, Show, Update, Delete, Publish, Unpublish

Suggested Postman variables:

- `base_url`: `http://localhost:8000/api/v1/admin`
- `token`: set from `data.access_token` after login

## Vue 3 Integration Notes

- Set `VITE_API_BASE_URL=http://localhost:8000/api/v1/admin`.
- Store `data.access_token` after login and send it as `Authorization: Bearer ${token}`.
- Use `data.user.roles` and `data.user.permissions` from login/profile for route guards and component permission checks.
- Send `Accept: application/json` on every request so validation and auth failures return JSON.
- For image uploads, use `FormData` and let the browser set the multipart `Content-Type`.
- Display paginated list responses from `data.items` and pagination from `data.meta`.
