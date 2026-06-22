<?php

namespace App\Policies;

class MenuPolicy extends CmsPolicy
{
    protected function module(): string
    {
        return 'menu';
    }
}
