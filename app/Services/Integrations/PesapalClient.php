<?php

namespace App\Services\Integrations;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Pesapal API v3.
 */
class PesapalClient
{
    public function baseUrl(): string
    {
        return config('services.pesapal.environment') === 'production'
            ? 'https://pay.pesapal.com/v3'
            : 'https://cybqa.pesapal.com/pesapalv3';
    }

    /**
     * Requests an auth token: proves the consumer key and secret are valid.
     */
    public function testConnection(): ConnectionResult
    {
        $key = config('services.pesapal.consumer_key');
        $secret = config('services.pesapal.consumer_secret');

        if (blank($key) || blank($secret)) {
            return ConnectionResult::failure('Enter the Pesapal consumer key and consumer secret first.');
        }

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->post($this->baseUrl().'/api/Auth/RequestToken', [
                    'consumer_key' => $key,
                    'consumer_secret' => $secret,
                ]);
        } catch (ConnectionException $e) {
            return ConnectionResult::failure('Could not reach Pesapal: '.$e->getMessage());
        }

        if ($response->successful() && filled($response->json('token'))) {
            return ConnectionResult::success('Connected to Pesapal ('.config('services.pesapal.environment').'). Credentials are valid.');
        }

        $error = $response->json('error.message') ?? $response->json('message') ?? 'HTTP '.$response->status();

        return ConnectionResult::failure("Pesapal rejected the credentials: {$error}");
    }
}
