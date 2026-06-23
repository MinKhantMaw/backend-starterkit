# Enterprise Starter Kit Architecture

This backend is a reusable Laravel 12 foundation, not a CMS.

## Structure

- `app/Modules/Auth`
- `app/Modules/User`
- `app/Modules/Role`
- `app/Modules/Permission`
- `app/Modules/Profile`
- `app/Modules/ActivityLog`
- `app/Modules/Notification`
- `app/Modules/File`
- `app/Services`
- `app/Repositories`
- `app/Traits`
- `app/Helpers`
- `app/Enums`

## Patterns

- Feature-based module controllers.
- Service layer for business workflows.
- Repository layer for persistence boundaries.
- Form Requests for validation and request authorization.
- API Resources for response shaping.
- Policies and middleware for authorization and account state.
- Sanctum bearer tokens for API authentication.
- Spatie Permission for roles and permissions.
- Database notifications through Laravel's `Notifiable` support.
- Reusable upload handling through `FileUploadService`.
- Global exception rendering in `bootstrap/app.php`.
- Queue and cache support through Laravel infrastructure migrations.
