# Enterprise Starter Kit API Guide

Base URL: `/api/v1`

## Authentication

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/auth/login` | Issue a Sanctum token |
| `POST` | `/auth/logout` | Revoke the current token |
| `POST` | `/auth/forgot-password` | Send a password reset link |
| `POST` | `/auth/reset-password` | Reset password |
| `POST` | `/auth/change-password` | Change password |
| `GET` | `/auth/me` | Current authenticated user |

## Profile

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/profile` | Show current profile |
| `PUT` | `/profile` | Update profile |
| `POST` | `/profile/change-password` | Change password |
| `POST` | `/profile/avatar` | Upload avatar |

## Administration

| Endpoint | Purpose |
| --- | --- |
| `/users` | User CRUD, role assignment, activate/deactivate |
| `/roles` | Role CRUD |
| `/roles/{role}/permissions` | Assign permissions to a role |
| `/permissions` | Permission CRUD |
| `/activity-logs` | Login/create/update/delete audit trail |
| `/notifications` | List current user's database notifications |
| `/notifications/unread-count` | Current user's unread notification count |
| `/files/images` | Upload a validated image |
| `/files/documents` | Upload a validated document |

## Permission Pattern

- `users.view`, `users.create`, `users.edit`, `users.delete`
- `roles.view`, `roles.create`, `roles.edit`, `roles.delete`
- `permissions.view`, `permissions.create`, `permissions.edit`, `permissions.delete`
- `activityLogs.view`
- `files.upload`

## Validation Contract

Backend Form Requests are the source of truth. User create/update requests require `name`, `email`, `phone`, `password` confirmation where applicable, `role_id`, and `status`. Role permission assignment accepts permission IDs. Validation failures return:

```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```
