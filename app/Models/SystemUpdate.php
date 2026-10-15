<?php

namespace App\Models;

use App\Enums\UpdateStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['version', 'notes', 'package_path', 'status', 'files', 'added_files', 'backup_path', 'log', 'user_id', 'applied_at'])]
class SystemUpdate extends Model
{
    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => UpdateStatus::class,
            'files' => 'array',
            'added_files' => 'array',
            'applied_at' => 'datetime',
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
     * Only the most recent installed update can be rolled back, so files are
     * always restored in the reverse order they were changed.
     */
    public function canRollBack(): bool
    {
        return $this->status === UpdateStatus::Applied
            && $this->backup_path !== null
            && ! static::query()->where('status', UpdateStatus::Applied)->where('applied_at', '>', $this->applied_at)->exists();
    }
}
