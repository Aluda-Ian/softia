<?php

namespace App\Models;

use App\Models\Concerns\HasImageUrl;
use Database\Factories\SeoMetaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['seoable_type', 'seoable_id', 'route_name', 'meta_title', 'meta_description', 'og_image', 'canonical_url', 'robots'])]
class SeoMeta extends Model
{
    /** @use HasFactory<SeoMetaFactory> */
    use HasFactory, HasImageUrl;

    /**
     * @return MorphTo<Model, $this>
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogImageUrl(): ?string
    {
        return static::imageUrlFor($this->og_image);
    }
}
