<?php

namespace App\Policies;

class TagPolicy extends CmsPolicy
{
    protected function module(): string
    {
        return 'tag';
    }
}
