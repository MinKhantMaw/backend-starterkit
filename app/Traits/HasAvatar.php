<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasAvatar
{
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }
}
