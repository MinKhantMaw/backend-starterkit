<?php

namespace App\Policies;

class MediaPolicy extends CmsPolicy
{
    protected function module(): string
    {
        return 'media';
    }
}
