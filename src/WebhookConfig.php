<?php

namespace Spatie\WebhookClient;

use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;

class WebhookConfig
{
    /** @var string */
    public $name;

    /** @var string */
    public $signingSecret;

    /** @var string */
    public $signatureHeaderName;

    /** @var \Spatie\WebhookClient\SignatureValidator\SignatureValidator */
    public $signatureValidator;

    /** @var \Spatie\WebhookClient\WebhookProfile\WebhookProfile */
    public $webhookProfile;

    /** @var string */
    public $webhookModel;

    /** @var \Spatie\WebhookClient\ProcessWebhookJob */
    public $processWebhookJob;

    public function __construct(array $properties)
    {
        $this->name = $properties['name'];

        $this->signingSecret = $properties['signing_secret'];

        $this->signatureHeaderName = $properties['signature_header_name'];

        if (! is_subclass_of($properties['signature_validator'], SignatureValidator::class)) {
            throw InvalidConfig::invalidSignatureValidator($properties['signature_validator']);
        }
        $this->signatureValidator = app($properties['signature_validator']);

        if (! is_subclass_of($properties['webhook_profile'], WebhookProfile::class)) {
            throw InvalidConfig::invalidWebhookProfile($properties['webhook_profile']);
        }
        $this->webhookProfile = app($properties['webhook_profile']);

        $this->webhookModel = $properties['webhook_model'];

        if (! is_subclass_of($properties['process_webhook_job'], ProcessWebhookJob::class)) {
            throw InvalidConfig::invalidProcessWebhookJob($properties['process_webhook_job']);
        }
        $this->processWebhookJob = app($properties['process_webhook_job']);
    }
}
