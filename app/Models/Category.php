<?php

namespace App\Models;

use App\Models\Concerns\HasImageUrl;
use App\Models\Concerns\HasSeo;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['parent_id', 'name', 'menu_label', 'slug', 'tagline', 'description', 'image', 'sort_order', 'is_active'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasImageUrl, HasSeo;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Products filed under this product line's sections.
     *
     * @return HasManyThrough<Product, Category, $this>
     */
    public function sectionProducts(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, Category::class, 'parent_id', 'category_id');
    }

    /**
     * @param  Builder<Category>  $query
     */
    public function scopeProductLines(Builder $query): void
    {
        $query->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Short name for the main menu (two words at most), falling back to the name.
     */
    public function menuLabel(): string
    {
        return $this->menu_label ?: $this->name;
    }

    public function isProductLine(): bool
    {
        return $this->parent_id === null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
