<?php

namespace App\Policies;

class CategoryPolicy extends CmsPolicy
{
    protected function module(): string
    {
        return 'category';
    }
}
