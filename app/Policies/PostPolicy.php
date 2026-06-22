<?php

namespace App\Policies;

class PostPolicy extends CmsPolicy
{
    protected function module(): string
    {
        return 'post';
    }
}
