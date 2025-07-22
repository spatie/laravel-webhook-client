<?php

use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;
use Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo;

it('can handle a valid configuration', function () {
    $configArray = getValidConfig();

    $webhookConfig = new WebhookConfig($configArray);

    expect($webhookConfig->name)->toBe($configArray['name']);
    expect($webhookConfig->signingSecret)->toBe($configArray['signing_secret']);
    expect($webhookConfig->signatureHeaderName)->toBe($configArray['signature_header_name']);
    expect($webhookConfig->signatureValidator)->toBeInstanceOf($configArray['signature_validator']);
    expect($webhookConfig->webhookProfile)->toBeInstanceOf($configArray['webhook_profile']);
    expect($webhookConfig->webhookModel)->toBe($configArray['webhook_model']);
    expect($webhookConfig->processWebhookJobClass)->toBe($configArray['process_webhook_job']);
});

it('validates the signature validator', function () {
    $config = getValidConfig();
    $config['signature_validator'] = 'invalid-signature-validator';

    expect(fn () => new WebhookConfig($config))->toThrow(InvalidConfig::class);
});

it('validates the webhook profile', function () {
    $config = getValidConfig();
    $config['webhook_profile'] = 'invalid-webhook-profile';

    expect(fn () => new WebhookConfig($config))->toThrow(InvalidConfig::class);
});

it('validates the webhook response', function () {
    $config = getValidConfig();
    $config['webhook_response'] = 'invalid-webhook-response';

    expect(fn () => new WebhookConfig($config))->toThrow(InvalidConfig::class);
});

it('uses the default webhook response if none provided', function () {
    $config = getValidConfig();
    $config['webhook_response'] = null;

    expect((new WebhookConfig($config))->webhookResponse)->toBeInstanceOf(DefaultRespondsTo::class);
});

it('validates the process webhook job', function () {
    $config = getValidConfig();
    $config['process_webhook_job'] = 'invalid-process-webhook-job';

    expect(fn () => new WebhookConfig($config))->toThrow(InvalidConfig::class);
});

function getValidConfig(): array
{
    return [
        'name' => 'default',
        'signing_secret' => 'my-secret',
        'signature_header_name' => 'Signature',
        'signature_validator' => DefaultSignatureValidator::class,
        'webhook_profile' => ProcessEverythingWebhookProfile::class,
        'webhook_response' => DefaultRespondsTo::class,
        'webhook_model' => WebhookCall::class,
        'process_webhook_job' => ProcessWebhookJobTestClass::class,
    ];
}