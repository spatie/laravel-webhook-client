<?php

namespace Spatie\WebhookClient\Events;

use Spatie\WebhookClient\Models\WebhookCall;

class WebhookCallFailedEvent extends WebhookCallEvent
{
    /**
     * @var \Exception
     */
    public $exception;

    /**
     * @param WebhookCall $webhookCall
     * @param \Exception $exception
     */
    public function __construct(WebhookCall $webhookCall, \Exception $exception)
    {
        parent::__construct($webhookCall);

        $this->exception = $exception;
    }
}
