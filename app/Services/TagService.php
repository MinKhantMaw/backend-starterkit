<?php

namespace App\Services;

use App\Models\Tag;

class TagService extends CrudService
{
    protected function modelClass(): string
    {
        return Tag::class;
    }
}
