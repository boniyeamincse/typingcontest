<?php

namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarService
{
    private const DISK        = 'public';
    private const AVATAR_DIR  = 'avatars';
    private const COVER_DIR   = 'covers';
    private const MAX_SIZE_KB = 2048; // 2 MB

    /**
     * Store avatar, delete old one, update user record.
     */
    public function store(User $user, UploadedFile $file): string
    {
        $this->deleteOld($user->avatar);

        $filename = sprintf('%s_%s.%s', $user->id, Str::random(12), $file->getClientOriginalExtension());
        $path     = $file->storeAs(self::AVATAR_DIR, $filename, self::DISK);

        $user->update(['avatar' => $path]);

        return Storage::disk(self::DISK)->url($path);
    }

    /**
     * Store cover photo, delete old one, update profile record.
     */
    public function storeCover(User $user, UploadedFile $file): string
    {
        $profile = $user->profile;
        if ($profile) {
            $this->deleteOld($profile->cover_photo);
        }

        $filename = sprintf('cover_%s_%s.%s', $user->id, Str::random(12), $file->getClientOriginalExtension());
        $path     = $file->storeAs(self::COVER_DIR, $filename, self::DISK);

        if ($profile) {
            $profile->update(['cover_photo' => $path]);
        }

        return Storage::disk(self::DISK)->url($path);
    }

    /**
     * Delete a stored image by its path.
     */
    public function deleteOld(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * Generate a full URL for a stored path.
     */
    public function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk(self::DISK)->url($path);
    }
}
