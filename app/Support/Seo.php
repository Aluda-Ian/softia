<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Resolves page meta tags: admin overrides from SeoMeta first, then
 * sensible defaults derived from the page's model.
 */
class Seo
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $image,
        public readonly string $canonical,
        public readonly string $robots = 'index, follow',
    ) {}

    public static function forRoute(string $routeName, ?string $title = null, ?string $description = null): self
    {
        $meta = SeoMeta::query()->where('route_name', $routeName)->first();

        return self::build($meta, $title, $description, null);
    }

    public static function forModel(Model $model): self
    {
        /** @var SeoMeta|null $meta */
        $meta = method_exists($model, 'seo') ? $model->seo : null;

        [$title, $description, $image] = match (true) {
            $model instanceof Product => [
                "{$model->name} | Softia",
                $model->short_description,
                $model->imageUrl(),
            ],
            $model instanceof Page => [
                "{$model->title} | Softia",
                $model->excerpt,
                null,
            ],
            $model instanceof Post => [
                "{$model->title} | Softia News",
                $model->excerpt,
                $model->coverUrl(),
            ],
            $model instanceof Category => [
                "{$model->name} | Softia",
                $model->description,
                $model->imageUrl(),
            ],
            default => [null, null, null],
        };

        return self::build($meta, $title, $description, $image);
    }

    private static function build(?SeoMeta $meta, ?string $title, ?string $description, ?string $image): self
    {
        $defaults = config('shop.default_seo');

        return new self(
            title: $meta?->meta_title ?: ($title ?: $defaults['title']),
            description: Str::limit($meta?->meta_description ?: ($description ?: $defaults['description']), 160),
            image: $meta?->ogImageUrl() ?: ($image ?: asset($defaults['image'])),
            canonical: $meta?->canonical_url ?: url()->current(),
            robots: $meta?->robots ?: 'index, follow',
        );
    }
}
