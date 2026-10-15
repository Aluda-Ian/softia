<?php

namespace Tests\Feature;

use App\Filament\Resources\PushNotifications\Pages\CreatePushNotification;
use App\Models\PushNotification;
use App\Models\PushSubscription;
use App\Models\Team;
use App\Models\User;
use App\Support\Push\PushSender;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;
use Mockery;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function subscribe(string $endpoint): PushSubscription
    {
        return PushSubscription::create(['endpoint' => $endpoint, 'public_key' => 'BKey', 'auth_token' => 'auth']);
    }

    /**
     * @param  list<MessageSentReport>  $reports
     */
    private function fakeSender(array $reports): PushSender
    {
        $webPush = Mockery::mock(WebPush::class);
        $webPush->shouldReceive('queueNotification');
        $webPush->shouldReceive('flush')->andReturn((fn () => yield from $reports)());

        return new class($webPush) extends PushSender
        {
            public function __construct(private WebPush $fake) {}

            protected function webPush(): WebPush
            {
                return $this->fake;
            }
        };
    }

    public function test_delivers_and_removes_expired_subscriptions(): void
    {
        $this->subscribe('https://push.example/ok');
        $this->subscribe('https://push.example/gone');

        $notification = PushNotification::create(['title' => 'Offer', 'body' => '15% off jumbo rolls']);

        $this->fakeSender([
            new MessageSentReport(new Request('POST', 'https://push.example/ok'), new Response(201)),
            new MessageSentReport(new Request('POST', 'https://push.example/gone'), new Response(410), false, 'Gone'),
        ])->send($notification);

        $notification->refresh();
        $this->assertSame(1, $notification->sent_count);
        $this->assertSame(1, $notification->failed_count);
        $this->assertSame(1, $notification->removed_count);
        $this->assertNotNull($notification->sent_at);
        $this->assertSame(['https://push.example/ok'], PushSubscription::pluck('endpoint')->all());
    }

    public function test_sending_with_no_subscribers_is_recorded(): void
    {
        $notification = app(PushSender::class)->send(PushNotification::create(['title' => 'Hi', 'body' => 'Hello']));

        $this->assertSame(0, $notification->sent_count);
        $this->assertSame('No subscribed devices yet.', $notification->error);
    }

    public function test_admin_composes_and_sends_from_the_panel(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $this->subscribe('https://push.example/device');

        $this->app->instance(PushSender::class, $this->fakeSender([
            new MessageSentReport(new Request('POST', 'https://push.example/device'), new Response(201)),
        ]));

        $this->get('/admin/push-notifications')->assertOk()->assertSee('1 device subscribed');

        Livewire::test(CreatePushNotification::class)
            ->fillForm(['title' => 'New: Softia Facial Tissue', 'body' => 'Now in stores and online.', 'url' => url('/news')])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Delivered to 1 device');

        $this->assertSame(1, PushNotification::sole()->sent_count);
    }

    public function test_only_teams_with_the_permission_can_send(): void
    {
        $sales = Team::factory()->withPermissions(['orders'])->create();
        $this->actingAs(User::factory()->admin()->create(['team_id' => $sales->id]));

        $this->get('/admin/push-notifications')->assertForbidden();
    }
}
