<?php

namespace Spatie\WebhookClient;

use Illuminate\Contracts\Foundation\Application;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\Storage\WebhookCallStorage;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;

class WebhookConfig
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $signingSecret;

    /**
     * @var string
     */
    public $signatureHeaderName;

    /**
     * @var SignatureValidator
     */
    public $signatureValidator;

    /**
     * @var WebhookProfile
     */
    public $webhookProfile;

    /**
     * @var WebhookCallStorage
     */
    public $webhookStorage;

    /**
     * @var ProcessWebhookJob
     */
    public $processWebhookJob;

    /**
     * WebhookConfig constructor.
     * @param Application $app
     * @param WebhookCallStorage $storage
     * @param array $properties
     * @throws InvalidConfig
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Application $app, Storage\WebhookCallStorage $storage, array $properties)
    {
        $this->name = $properties['name'];

        $this->signingSecret = $properties['signing_secret'] ?? '';

        $this->signatureHeaderName = $properties['signature_header_name'] ?? '';

        if (! is_subclass_of($properties['signature_validator'], SignatureValidator::class)) {
            throw InvalidConfig::invalidSignatureValidator($properties['signature_validator']);
        }
        $this->signatureValidator = $app->make($properties['signature_validator']);

        if (! is_subclass_of($properties['webhook_profile'], WebhookProfile::class)) {
            throw InvalidConfig::invalidWebhookProfile($properties['webhook_profile']);
        }
        $this->webhookProfile = $app->make($properties['webhook_profile']);

        $this->webhookStorage = $storage;

        if (! is_subclass_of($properties['process_webhook_job'], ProcessWebhookJob::class)) {
            throw InvalidConfig::invalidProcessWebhookJob($properties['process_webhook_job']);
        }
        $this->processWebhookJob = $properties['process_webhook_job'];
    }
}
