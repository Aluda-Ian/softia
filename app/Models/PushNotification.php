<?php

namespace App\Models;

use App\Models\Concerns\HasImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'body', 'url', 'image', 'sent_count', 'failed_count', 'removed_count', 'error', 'user_id', 'sent_at'])]
class PushNotification extends Model
{
    use HasImageUrl;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Payload read by the service worker (public/sw.js).
     *
     * @return array{title: string, body: string, url: string, image: ?string}
     */
    public function payload(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url ?: url('/'),
            'image' => static::imageUrlFor($this->image),
        ];
    }
}
