<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Key/value system setting. Secrets are encrypted with the app key, so they
 * stay unreadable in database backups and phpMyAdmin.
 */
#[Fillable(['key', 'value', 'is_encrypted'])]
class Setting extends Model
{
    public const CACHE_KEY = 'system-settings';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * All stored settings as key => decrypted value (cached).
     *
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()
            ->get()
            ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->plainValue()])
            ->all());
    }

    public static function put(string $key, ?string $value, bool $encrypt = false): void
    {
        static::updateOrCreate(['key' => $key], [
            'value' => ($encrypt && filled($value)) ? Crypt::encryptString($value) : $value,
            'is_encrypted' => $encrypt && filled($value),
        ]);
    }

    public function plainValue(): ?string
    {
        if (! $this->is_encrypted || $this->value === null) {
            return $this->value;
        }

        try {
            return Crypt::decryptString($this->value);
        } catch (Throwable) {
            // Unreadable after an APP_KEY change; treat as not set.
            return null;
        }
    }
}
