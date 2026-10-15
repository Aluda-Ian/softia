<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A staff team. Its permissions decide which admin sections members can use.
 */
#[Fillable(['name', 'description', 'permissions'])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * Admin sections a team can be given access to.
     */
    public const PERMISSIONS = [
        'catalogue' => 'Products & product lines',
        'inventory' => 'Inventory',
        'orders' => 'Orders & quotations',
        'messages' => 'Contact messages',
        'content' => 'Pages & news',
        'seo' => 'SEO Manager',
        'notifications' => 'Push notifications',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function allows(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}
