<?php

namespace Spatie\WebhookClient\Exceptions;

use Exception;
use Spatie\WebhookClient\ProcessWebhookJob;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;

class InvalidConfig extends Exception
{
    public static function couldNotFindConfig(string $notFoundConfigName)
    {
        return new static("Could not find the configuration for `{$notFoundConfigName}`");
    }

    public static function invalidSignatureValidator(string $invalidSignatureValidator)
    {
        $signatureValidatorInterface = SignatureValidator::class;

        return new static("`{$invalidSignatureValidator}` is not a valid signature validator class. A valid signature validator is a class that implements `{$signatureValidatorInterface}`.");
    }

    public static function invalidWebhookProfile(string $webhookProfile)
    {
        $webhookProfileInterface = WebhookProfile::class;

        return new static("`{$webhookProfile}` is not a valid webhook profile class. A valid web hook profile is a class that implements `{$webhookProfileInterface}`.");
    }

    public static function invalidProcessWebhookJob(string $processWebhookJob)
    {
        $abstractProcessWebhookJob = ProcessWebhookJob::class;

        return new static("`{$processWebhookJob}` is not a valid process webhook job class. A valid class should implement `{$abstractProcessWebhookJob}`.");
    }
}

