<?php

namespace App\Support\Settings;

use App\Models\Setting;
use Throwable;

/**
 * Every admin-editable system setting: its storage key, the config path it
 * overrides at runtime, and whether it is secret (stored encrypted).
 * Values saved in the admin win over .env; empty values fall back to .env.
 */
class SettingsRegistry
{
    /**
     * @return array<string, array{config: string, secret?: bool, type?: string}>
     */
    public static function definitions(): array
    {
        return [
            // Business details
            'contact.phone' => ['config' => 'shop.contact.phone'],
            'contact.email' => ['config' => 'shop.contact.email'],
            'contact.whatsapp' => ['config' => 'shop.contact.whatsapp'],
            'contact.showroom' => ['config' => 'shop.contact.showroom'],
            'contact.logistics_hub' => ['config' => 'shop.contact.logistics_hub'],

            // Payment methods at checkout
            'payments.pay_on_delivery' => ['config' => 'shop.payments.pay_on_delivery', 'type' => 'bool'],
            'payments.mpesa_manual' => ['config' => 'shop.payments.mpesa_manual', 'type' => 'bool'],
            'mpesa.paybill' => ['config' => 'shop.mpesa.paybill'],
            'mpesa.till' => ['config' => 'shop.mpesa.till'],
            'mpesa.account' => ['config' => 'shop.mpesa.account'],

            // M-Pesa Daraja API
            'daraja.enabled' => ['config' => 'services.daraja.enabled', 'type' => 'bool'],
            'daraja.environment' => ['config' => 'services.daraja.environment'],
            'daraja.consumer_key' => ['config' => 'services.daraja.consumer_key', 'secret' => true],
            'daraja.consumer_secret' => ['config' => 'services.daraja.consumer_secret', 'secret' => true],
            'daraja.shortcode' => ['config' => 'services.daraja.shortcode'],
            'daraja.transaction_type' => ['config' => 'services.daraja.transaction_type'],
            'daraja.till_number' => ['config' => 'services.daraja.till_number'],
            'daraja.passkey' => ['config' => 'services.daraja.passkey', 'secret' => true],

            // Pesapal
            'pesapal.enabled' => ['config' => 'services.pesapal.enabled', 'type' => 'bool'],
            'pesapal.environment' => ['config' => 'services.pesapal.environment'],
            'pesapal.consumer_key' => ['config' => 'services.pesapal.consumer_key', 'secret' => true],
            'pesapal.consumer_secret' => ['config' => 'services.pesapal.consumer_secret', 'secret' => true],
            'pesapal.ipn_id' => ['config' => 'services.pesapal.ipn_id'],

            // Email (SMTP)
            'mail.enabled' => ['config' => 'shop.mail_via_smtp', 'type' => 'bool'],
            'mail.host' => ['config' => 'mail.mailers.smtp.host'],
            'mail.port' => ['config' => 'mail.mailers.smtp.port', 'type' => 'int'],
            'mail.username' => ['config' => 'mail.mailers.smtp.username'],
            'mail.password' => ['config' => 'mail.mailers.smtp.password', 'secret' => true],
            'mail.from_address' => ['config' => 'mail.from.address'],
            'mail.from_name' => ['config' => 'mail.from.name'],

            // SMS (Africa's Talking)
            'sms.environment' => ['config' => 'services.africastalking.environment'],
            'sms.username' => ['config' => 'services.africastalking.username'],
            'sms.api_key' => ['config' => 'services.africastalking.api_key', 'secret' => true],
            'sms.sender_id' => ['config' => 'services.africastalking.sender_id'],

            // Analytics
            'analytics.ga4_id' => ['config' => 'shop.analytics.ga4_id'],
            'analytics.meta_pixel_id' => ['config' => 'shop.analytics.meta_pixel_id'],
        ];
    }

    public static function isSecret(string $key): bool
    {
        return self::definitions()[$key]['secret'] ?? false;
    }

    /**
     * Current value: saved setting, otherwise the config/.env default.
     */
    public static function current(string $key): mixed
    {
        return config(self::definitions()[$key]['config']);
    }

    /**
     * Push saved settings into config. Safe to call before migrations run.
     */
    public static function applyToConfig(): void
    {
        try {
            $saved = Setting::allValues();
        } catch (Throwable) {
            return;
        }

        foreach (self::definitions() as $key => $definition) {
            if (! array_key_exists($key, $saved) || $saved[$key] === null || $saved[$key] === '') {
                continue;
            }

            config([$definition['config'] => self::cast($saved[$key], $definition['type'] ?? 'string')]);
        }

        if (config('shop.mail_via_smtp')) {
            config(['mail.default' => 'smtp']);
        }
    }

    /**
     * Save form values. Blank secrets keep the stored value, so admins never
     * have to re-enter keys to change other settings.
     *
     * @param  array<string, mixed>  $values
     */
    public static function save(array $values): void
    {
        foreach (self::definitions() as $key => $definition) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];

            if (self::isSecret($key) && blank($value)) {
                continue;
            }

            $stored = match ($definition['type'] ?? 'string') {
                'bool' => $value ? '1' : '0',
                default => blank($value) ? null : trim((string) $value),
            };

            Setting::put($key, $stored, self::isSecret($key));
        }

        self::applyToConfig();
    }

    private static function cast(string $value, string $type): mixed
    {
        return match ($type) {
            'bool' => $value === '1',
            'int' => (int) $value,
            default => $value,
        };
    }
}
