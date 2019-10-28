<?php

namespace Spatie\WebhookClient\Listeners;

use Spatie\WebhookClient\Events\WebhookCallFailedEvent;
use Spatie\WebhookClient\Models\EloquentWebhookCall;

class LogEloquentExceptionListener
{
    /**
     * Save processing errors for eloquent models.
     *
     * @param WebhookCallFailedEvent $event
     */
    public function handle(WebhookCallFailedEvent $event)
    {
        if ($event->webhookCall instanceof EloquentWebhookCall) {
            $event->webhookCall->saveException($event->exception);
        }
    }
}
