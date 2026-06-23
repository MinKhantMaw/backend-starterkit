<?php

namespace App\Enums;

enum ActivityEvent: string
{
    case Login = 'login';
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
}
