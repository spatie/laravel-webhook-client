<?php

namespace Spatie\WebhookClient\Listeners;

use Spatie\WebhookClient\Models\EloquentWebhookCall;
use Spatie\WebhookClient\Events\WebhookCallFailedEvent;

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
