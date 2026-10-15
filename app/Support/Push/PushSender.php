<?php

namespace App\Support\Push;

use App\Models\PushNotification;
use App\Models\PushSubscription;
use Minishlink\WebPush\ContentEncoding;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

/**
 * Delivers a push notification to every subscribed browser and records the
 * result. Subscriptions the push service reports as expired are removed.
 */
class PushSender
{
    public function send(PushNotification $notification): PushNotification
    {
        $subscriptions = PushSubscription::query()->get();
        $sent = 0;
        $failed = 0;
        $removed = 0;
        $errors = [];

        if ($subscriptions->isEmpty()) {
            return $this->record($notification, 0, 0, 0, 'No subscribed devices yet.');
        }

        try {
            $webPush = $this->webPush();
            $payload = json_encode($notification->payload());

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => ContentEncoding::aes128gcm,
                ]), $payload);
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    $sent++;

                    continue;
                }

                $failed++;

                if ($report->isSubscriptionExpired()) {
                    $removed += PushSubscription::query()->where('endpoint', $report->getEndpoint())->delete();
                } else {
                    $errors[] = $report->getReason();
                }
            }
        } catch (Throwable $e) {
            report($e);

            return $this->record($notification, $sent, max($failed, $subscriptions->count() - $sent), $removed, $e->getMessage());
        }

        return $this->record($notification, $sent, $failed, $removed, $errors === [] ? null : implode("\n", array_unique(array_slice($errors, 0, 5))));
    }

    protected function webPush(): WebPush
    {
        return new WebPush([
            'VAPID' => [
                'subject' => 'mailto:'.config('shop.contact.email'),
                'publicKey' => VapidKeys::publicKey(),
                'privateKey' => VapidKeys::privateKey(),
            ],
        ], ['TTL' => 86400, 'urgency' => 'normal', 'batchSize' => 200]);
    }

    private function record(PushNotification $notification, int $sent, int $failed, int $removed, ?string $error): PushNotification
    {
        $notification->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'removed_count' => $removed,
            'error' => $error,
            'sent_at' => now(),
        ]);

        return $notification;
    }
}
