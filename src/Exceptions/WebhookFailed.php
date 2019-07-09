<?php

namespace Spatie\WebhookClient\Exceptions;

use Exception;

class WebhookFailed extends Exception
{
    public static function invalidSignature(): WebhookFailed
    {
        return new static('The signature is invalid.');
    }

    public static function signingSecretNotSet(): WebhookFailed
    {
        return new static('The webhook signing secret is not set. Make sure that the `signing_secret` config key is set to the correct value.');
    }
}
