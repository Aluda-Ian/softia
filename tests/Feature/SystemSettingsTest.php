<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Integrations\DarajaClient;
use App\Support\Settings\SettingsRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_secrets_are_stored_encrypted_and_applied_to_config(): void
    {
        SettingsRegistry::save([
            'daraja.consumer_key' => 'live-key-123',
            'daraja.environment' => 'production',
            'contact.phone' => '+254 700 000 000',
        ]);

        $stored = Setting::firstWhere('key', 'daraja.consumer_key');
        $this->assertTrue($stored->is_encrypted);
        $this->assertNotSame('live-key-123', $stored->value);

        SettingsRegistry::applyToConfig();
        $this->assertSame('live-key-123', config('services.daraja.consumer_key'));
        $this->assertSame('production', config('services.daraja.environment'));
        $this->assertSame('+254 700 000 000', config('shop.contact.phone'));
    }

    public function test_blank_secret_keeps_the_saved_value(): void
    {
        SettingsRegistry::save(['pesapal.consumer_secret' => 'original-secret']);
        SettingsRegistry::save(['pesapal.consumer_secret' => '', 'pesapal.environment' => 'sandbox']);

        SettingsRegistry::applyToConfig();
        $this->assertSame('original-secret', config('services.pesapal.consumer_secret'));
    }

    public function test_settings_page_saves_without_exposing_secrets(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        SettingsRegistry::save(['daraja.consumer_secret' => 'top-secret']);

        Livewire::test(Settings::class)
            ->assertSet('data.daraja.consumer_secret', null)
            ->set('data.contact.showroom', 'Mombasa Road, Nairobi')
            ->call('save')
            ->assertNotified();

        $this->assertSame('Mombasa Road, Nairobi', Setting::firstWhere('key', 'contact.showroom')->value);
        $this->get('/admin/settings')->assertDontSee('top-secret');
    }

    public function test_daraja_connection_test_reports_success(): void
    {
        Http::fake(['sandbox.safaricom.co.ke/*' => Http::response(['access_token' => 'abc', 'expires_in' => '3599'])]);
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(Settings::class)
            ->set('data.daraja.consumer_key', 'key')
            ->set('data.daraja.consumer_secret', 'secret')
            ->set('data.daraja.environment', 'sandbox')
            ->call('applyUnsavedToConfig');

        $this->assertSame('key', config('services.daraja.consumer_key'));
        $this->assertTrue(app(DarajaClient::class)->testConnection()->ok);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'sandbox.safaricom.co.ke/oauth/v1/generate'));
    }

    public function test_daraja_connection_test_reports_rejected_credentials(): void
    {
        Http::fake(['sandbox.safaricom.co.ke/*' => Http::response(['errorMessage' => 'Invalid credentials'], 400)]);
        config(['services.daraja.consumer_key' => 'bad', 'services.daraja.consumer_secret' => 'bad', 'services.daraja.environment' => 'sandbox']);

        $result = app(DarajaClient::class)->testConnection();

        $this->assertFalse($result->ok);
        $this->assertStringContainsString('HTTP 400', $result->message);
    }

    public function test_disabling_retail_payments_turns_checkout_into_quotes(): void
    {
        SettingsRegistry::save(['payments.pay_on_delivery' => false, 'payments.mpesa_manual' => false]);

        $product = Product::factory()->create();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);

        $this->post('/checkout', [
            'customer_type' => 'b2c',
            'customer_name' => 'Jane',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'delivery_address' => 'Nairobi',
        ])->assertRedirect();

        $this->assertSame('quote', Order::sole()->type->value);
    }
}
