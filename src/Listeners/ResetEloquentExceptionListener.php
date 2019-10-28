<?php

namespace Spatie\WebhookClient\Listeners;

use Spatie\WebhookClient\Models\EloquentWebhookCall;
use Spatie\WebhookClient\Events\WebhookCallProcessingEvent;

class ResetEloquentExceptionListener
{
    /**
     * Reset processing errors for eloquent models.
     *
     * @param WebhookCallProcessingEvent $event
     */
    public function handle(WebhookCallProcessingEvent $event)
    {
        if ($event->webhookCall instanceof EloquentWebhookCall) {
            $event->webhookCall->clearException();
        }
    }
}
