<?php

namespace App\Services\Integrations;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Africa's Talking SMS.
 */
class AfricasTalkingClient
{
    public function baseUrl(): string
    {
        return config('services.africastalking.environment') === 'production'
            ? 'https://api.africastalking.com'
            : 'https://api.sandbox.africastalking.com';
    }

    /**
     * Reads the account balance: validates credentials without sending an SMS.
     */
    public function testConnection(): ConnectionResult
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');

        if (blank($username) || blank($apiKey)) {
            return ConnectionResult::failure("Enter the Africa's Talking username and API key first.");
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders(['apiKey' => $apiKey])
                ->acceptJson()
                ->get($this->baseUrl().'/version1/user', ['username' => $username]);
        } catch (ConnectionException $e) {
            return ConnectionResult::failure("Could not reach Africa's Talking: ".$e->getMessage());
        }

        if ($response->successful() && filled($response->json('UserData.balance'))) {
            return ConnectionResult::success('Connected. Account balance: '.$response->json('UserData.balance'));
        }

        return ConnectionResult::failure("Africa's Talking rejected the credentials (HTTP {$response->status()}).");
    }
}
