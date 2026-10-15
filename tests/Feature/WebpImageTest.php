<?php

namespace Tests\Feature;

use App\Support\WebpImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebpImageTest extends TestCase
{
    public function test_uploads_are_converted_to_resized_webp(): void
    {
        if (! WebpImage::isSupported()) {
            $this->markTestSkipped('PHP GD with WebP support is not enabled.');
        }

        Storage::fake('public');

        $path = WebpImage::store(UploadedFile::fake()->image('Tissue Roll.png', 3200, 1600), 'products', maxDimension: 1600);

        $this->assertNotNull($path);
        $this->assertStringStartsWith('products/tissue-roll-', $path);
        $this->assertStringEndsWith('.webp', $path);

        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([1600, 800], [$width, $height]);
    }

    public function test_non_images_are_left_for_the_default_upload_handler(): void
    {
        Storage::fake('public');

        $this->assertNull(WebpImage::store(UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'), 'products'));
    }
}
