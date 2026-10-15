<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Converts uploaded images to resized WebP files using PHP's GD extension.
 * Returns null when conversion isn't possible so callers can store the original.
 */
class WebpImage
{
    private const CONVERTIBLE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public static function isSupported(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    public static function store(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $maxDimension = 1600,
        int $quality = 80,
    ): ?string {
        if (! self::isSupported() || ! in_array($file->getMimeType(), self::CONVERTIBLE_MIME_TYPES, true)) {
            return null;
        }

        try {
            $source = imagecreatefromstring($file->get());

            if ($source === false) {
                return null;
            }

            $image = self::resize($source, $maxDimension);

            ob_start();
            imagewebp($image, null, $quality);
            $contents = ob_get_clean();

            imagedestroy($image);

            $path = trim($directory, '/').'/'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.Str::lower(Str::random(6)).'.webp';

            Storage::disk($disk)->put(ltrim($path, '/'), $contents, 'public');

            return ltrim($path, '/');
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    private static function resize(GdImage $source, int $maxDimension): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));

        if (! imageistruecolor($source)) {
            imagepalettetotruecolor($source);
        }

        if ($scale >= 1) {
            imagealphablending($source, false);
            imagesavealpha($source, true);

            return $source;
        }

        $newWidth = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        return $resized;
    }
}
