<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Support\Push\VapidKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_link_the_manifest_and_load_the_app_script(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false)
            ->assertSee('/js/softia-app.js', false)
            ->assertSee('data-install-banner', false)
            ->assertSee('data-notify-toggle', false);
    }

    public function test_manifest_is_valid_and_installable(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

        $this->assertSame('standalone', $manifest['display']);
        $this->assertContains('512x512', array_column($manifest['icons'], 'sizes'));

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_offline_page_is_available(): void
    {
        $this->get('/offline')->assertOk()->assertSee('You\'re offline', false);
    }

    public function test_vapid_public_key_is_generated_once_and_reused(): void
    {
        $first = VapidKeys::publicKey();

        $this->assertNotNull($first);
        $this->assertSame(65, strlen(base64_decode(strtr($first, '-_', '+/'))));
        $this->assertSame($first, VapidKeys::publicKey());
    }

    public function test_browsers_can_subscribe_and_unsubscribe(): void
    {
        $payload = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'keys' => ['p256dh' => 'BPublicKeyExample', 'auth' => 'authSecret'],
        ];

        $this->postJson('/push/subscribe', $payload)->assertOk()->assertJson(['subscribed' => true]);
        $this->postJson('/push/subscribe', $payload)->assertOk();
        $this->assertSame(1, PushSubscription::count());

        $this->postJson('/push/unsubscribe', ['endpoint' => $payload['endpoint']])->assertOk();
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_subscriptions_require_a_secure_endpoint(): void
    {
        $this->postJson('/push/subscribe', [
            'endpoint' => 'http://insecure.example/push',
            'keys' => ['p256dh' => 'x', 'auth' => 'y'],
        ])->assertUnprocessable();
    }
}
