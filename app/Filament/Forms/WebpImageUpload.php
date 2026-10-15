<?php

namespace App\Filament\Forms;

use App\Support\WebpImage;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Image upload that stores files on the public disk as optimised WebP,
 * falling back to the original file when the server lacks GD WebP support.
 */
class WebpImageUpload
{
    public static function make(string $name, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->imageEditor()
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->maxSize(10240)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->helperText('JPG, PNG or WebP up to 10 MB. Converted to WebP automatically.')
            ->saveUploadedFileUsing(
                fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => WebpImage::store($file, $directory)
                    ?? $component->saveUploadedFile($file)
            );
    }
}
