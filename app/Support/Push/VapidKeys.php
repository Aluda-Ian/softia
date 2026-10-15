<?php

namespace App\Support\Push;

use App\Models\Setting;
use RuntimeException;
use Throwable;

/**
 * VAPID key pair identifying this site to browser push services. Generated
 * once on first use and kept in settings (private key encrypted).
 */
class VapidKeys
{
    public static function publicKey(): ?string
    {
        try {
            $settings = Setting::allValues();

            if (blank($settings['push.vapid_public'] ?? null) || blank($settings['push.vapid_private'] ?? null)) {
                [$public, $private] = self::generate();
                Setting::put('push.vapid_public', $public);
                Setting::put('push.vapid_private', $private, encrypt: true);

                return $public;
            }

            return $settings['push.vapid_public'];
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    public static function privateKey(): ?string
    {
        self::publicKey();

        return Setting::allValues()['push.vapid_private'] ?? null;
    }

    /**
     * @return array{0: string, 1: string} [public, private], base64url encoded
     */
    public static function generate(): array
    {
        $options = ['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC];
        $key = openssl_pkey_new($options);

        // Windows PHP builds don't find openssl.cnf on their own.
        if ($key === false && is_file($bundled = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf')) {
            $key = openssl_pkey_new($options + ['config' => $bundled]);
        }

        if ($key === false) {
            throw new RuntimeException('Could not generate push notification keys: '.openssl_error_string());
        }

        $ec = openssl_pkey_get_details($key)['ec'];

        $public = "\x04".str_pad($ec['x'], 32, "\0", STR_PAD_LEFT).str_pad($ec['y'], 32, "\0", STR_PAD_LEFT);
        $private = str_pad($ec['d'], 32, "\0", STR_PAD_LEFT);

        return [self::base64Url($public), self::base64Url($private)];
    }

    private static function base64Url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
