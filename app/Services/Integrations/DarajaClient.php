<?php

namespace App\Services\Integrations;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Safaricom M-Pesa Daraja API.
 */
class DarajaClient
{
    public function baseUrl(): string
    {
        return config('services.daraja.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Requests an OAuth token: proves the consumer key and secret are valid.
     */
    public function testConnection(): ConnectionResult
    {
        $key = config('services.daraja.consumer_key');
        $secret = config('services.daraja.consumer_secret');

        if (blank($key) || blank($secret)) {
            return ConnectionResult::failure('Enter the Daraja consumer key and consumer secret first.');
        }

        try {
            $response = Http::timeout(20)
                ->withBasicAuth($key, $secret)
                ->acceptJson()
                ->get($this->baseUrl().'/oauth/v1/generate', ['grant_type' => 'client_credentials']);
        } catch (ConnectionException $e) {
            return ConnectionResult::failure('Could not reach Safaricom: '.$e->getMessage());
        }

        if ($response->successful() && filled($response->json('access_token'))) {
            return ConnectionResult::success('Connected to Daraja ('.config('services.daraja.environment').'). Credentials are valid.');
        }

        return ConnectionResult::failure('Daraja rejected the credentials (HTTP '.$response->status().'). Check the key, secret and environment.');
    }
}
