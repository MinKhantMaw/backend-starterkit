<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(private readonly FileUploadService $uploads) {}

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->load('roles.permissions');
    }

    public function uploadAvatar(User $user, UploadedFile $avatar): User
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $upload = $this->uploads->store($avatar, 'avatars');

        $user->update([
            'avatar_path' => $upload['path'],
        ]);

        return $user->load('roles.permissions');
    }
}
