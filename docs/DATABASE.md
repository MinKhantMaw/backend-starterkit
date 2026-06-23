# Enterprise Starter Kit Database

Project-specific domain tables are intentionally excluded.

| Table | Purpose |
| --- | --- |
| `users` | Users, active status, avatar path |
| `roles` | Spatie roles |
| `permissions` | Spatie permissions |
| `model_has_roles` | User-role assignments |
| `model_has_permissions` | Direct user permissions |
| `role_has_permissions` | Role-permission assignments |
| `activity_logs` | Login/create/update/delete audit records |
| `notifications` | Database notifications for users |

Laravel infrastructure tables are retained for Sanctum, password resets, cache, sessions, and queue support.
