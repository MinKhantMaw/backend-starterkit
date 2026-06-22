<?php

namespace App\Models\Concerns;

trait HasSeo
{
    public static function seoFields(): array
    {
        return [
            'meta_title',
            'meta_description',
            'og_title',
            'og_description',
            'og_image',
            'canonical_url',
        ];
    }
}
