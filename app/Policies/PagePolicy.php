<?php

namespace App\Policies;

class PagePolicy extends CmsPolicy
{
    protected function module(): string
    {
        return 'page';
    }
}
