<?php

namespace Spatie\WebhookClient;

use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;
use Spatie\WebhookClient\WebhookResponse\WebhookResponse;

class WebhookConfig
{
    public string $name;

    public string $signingSecret;

    public string $signatureHeaderName;

    public SignatureValidator $signatureValidator;

    public WebhookProfile $webhookProfile;

    public WebhookResponse $webhookResponse;

    public string $webhookModel;

    public string $processWebhookJobClass;

    public function __construct(array $properties)
    {
        $this->name = $properties['name'];

        $this->signingSecret = $properties['signing_secret'] ?? '';

        $this->signatureHeaderName = $properties['signature_header_name'] ?? '';

        if (! is_subclass_of($properties['signature_validator'], SignatureValidator::class)) {
            throw InvalidConfig::invalidSignatureValidator($properties['signature_validator']);
        }
        $this->signatureValidator = app($properties['signature_validator']);

        if (! is_subclass_of($properties['webhook_profile'], WebhookProfile::class)) {
            throw InvalidConfig::invalidWebhookProfile($properties['webhook_profile']);
        }
        $this->webhookProfile = app($properties['webhook_profile']);

        if (! is_subclass_of($properties['webhook_response'], WebhookResponse::class)) {
            throw InvalidConfig::invalidWebhookResponse($properties['webhook_response']);
        }
        $this->webhookResponse = app($properties['webhook_response']);

        $this->webhookModel = $properties['webhook_model'];

        if (! is_subclass_of($properties['process_webhook_job'], ProcessWebhookJob::class)) {
            throw InvalidConfig::invalidProcessWebhookJob($properties['process_webhook_job']);
        }
        $this->processWebhookJobClass = $properties['process_webhook_job'];
    }
}
