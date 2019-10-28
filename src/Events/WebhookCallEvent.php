<?php

namespace Spatie\WebhookClient\Events;

use Spatie\WebhookClient\Models\WebhookCall;

class WebhookCallEvent
{
    /**
     * @var WebhookCall
     */
    public $webhookCall;

    /**
     * @param WebhookCall $webhookCall
     */
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }
}
