<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case DASHBOARD_VIEW = 'dashboard.view';

    case USER_VIEW = 'user.view';
    case USER_CREATE = 'user.create';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';

    case ROLE_VIEW = 'role.view';
    case ROLE_CREATE = 'role.create';
    case ROLE_UPDATE = 'role.update';
    case ROLE_DELETE = 'role.delete';

    case PERMISSION_VIEW = 'permission.view';

    case PROFILE_VIEW = 'profile.view';
    case PROFILE_UPDATE = 'profile.update';

    case AUDIT_LOG_VIEW = 'audit_log.view';
    case ACTIVITY_LOG_VIEW = 'activity_log.view';
    case NOTIFICATION_VIEW = 'notification.view';

    case FILE_UPLOAD = 'file.upload';

    public function module(): string
    {
        return str($this->value)->before('.')->toString();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
