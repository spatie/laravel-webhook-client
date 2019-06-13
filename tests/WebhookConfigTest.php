<?php

namespace Spatie\WebhookClient\Tests;

use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class WebhookConfigTest extends TestCase
{
    /** @test */
    public function it_can_handle_a_valid_configuration()
    {
        $configArray = $this->getValidConfig();

        $webhookConfig = new WebhookConfig($configArray);

        $this->assertEquals($configArray['name'], $webhookConfig->name);
        $this->assertEquals($configArray['signing_secret'], $webhookConfig->signingSecret);
        $this->assertEquals($configArray['signature_header_name'], $webhookConfig->signatureHeaderName);
        $this->assertInstanceOf($configArray['signature_validator'], $webhookConfig->signatureValidator);
        $this->assertInstanceOf($configArray['webhook_profile'], $webhookConfig->webhookProfile);
        $this->assertEquals($configArray['webhook_model'], $webhookConfig->webhookModel);
        $this->assertInstanceOf($configArray['process_webhook_job'], $webhookConfig->processWebhookJob);
    }

    protected function getValidConfig(): array
    {
        return [
            'name' => 'default',
            'signing_secret' => 'my-secret',
            'signature_header_name' => 'Signature',
            'signature_validator' => DefaultSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_model' => WebhookCall::class,
            'process_webhook_job' => ProcessWebhookJobTestClass::class,
        ];
    }
}

