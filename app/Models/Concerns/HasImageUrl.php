<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasImageUrl
{
    /**
     * Bundled images live in public/images; admin uploads live on the public disk.
     */
    public static function imageUrlFor(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    public function imageUrl(): ?string
    {
        return static::imageUrlFor($this->image);
    }
}
