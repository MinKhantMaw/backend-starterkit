<?php

namespace App\Enums;

enum ActivityEvent: string
{
    case Login = 'login';
    case Logout = 'logout';
    case LoginFailed = 'login_failed';
    case AccountLocked = 'account_locked';
    case AccountUnlocked = 'account_unlocked';
    case PasswordChanged = 'password_changed';
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
}
