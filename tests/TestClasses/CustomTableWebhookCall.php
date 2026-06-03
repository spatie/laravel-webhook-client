<?php

namespace Spatie\WebhookClient\Tests\TestClasses;

use Spatie\WebhookClient\Models\WebhookCall;

class CustomTableWebhookCall extends WebhookCall
{
    protected $table = 'custom_webhook_calls';
}
