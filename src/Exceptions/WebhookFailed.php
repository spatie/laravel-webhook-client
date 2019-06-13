<?php

namespace Spatie\WebhookClient\Exceptions;

use Exception;
use Spatie\StripeWebhooks\StripeWebhookCall;

class WebhookFailed extends Exception
{
    public static function missingSignature(string $headerName)
    {
        return new static("The request did not contain a header named `${headerName}`.");
    }

    public static function invalidSignature(string $signature, string $signatureHeaderName)
    {
        return new static("The signature `{$signature}` found in the header named `{$signatureHeaderName}` is invalid. Make sure that the `webhook_signing_secret` config key is set to the correct value. If you are caching your config try running `php artisan cache:clear` to resolve the problem.");
    }

    public static function signingSecretNotSet()
    {
        return new static('The webhook signing secret is not set. Make sure that the `signing_secret` config key is set to the correct value.');
    }

    public static function jobClassDoesNotExist(string $jobClass, StripeWebhookCall $webhookCall)
    {
        return new static("Could not process webhook id `{$webhookCall->id}` of type `{$webhookCall->type} because the configured jobclass `$jobClass` does not exist.");
    }
}
