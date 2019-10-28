<?php

namespace Spatie\WebhookClient\Tests\Storage;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator;
use Spatie\WebhookClient\Storage\InMemoryWebhookCallStorage;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class InMemoryWebhookCallStorateTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_store_webhook()
    {
        $storage = new InMemoryWebhookCallStorage();

        $webhook = $storage->storeWebhookCall(
            new WebhookConfig(new Application(), $storage, $this->getValidConfig()),
            (new Request())->replace(['payload'])
        );

        $this->assertNotEmpty($webhook->getId());
        $this->assertEquals('default', $webhook->getName());
        $this->assertEquals(['payload'], $webhook->getPayload());

        $this->assertEquals($webhook, $storage->retrieveWebhookCall($webhook->getId()));
    }

    /**
     * @test
     */
    public function it_should_delete_webhook()
    {
        $this->expectException(\OutOfBoundsException::class);

        $storage = new InMemoryWebhookCallStorage();

        $webhook = $storage->storeWebhookCall(
            new WebhookConfig(new Application(), $storage, $this->getValidConfig()),
            (new Request())->replace(['payload'])
        );

        $this->assertEquals($webhook, $storage->retrieveWebhookCall($webhook->getId()));

        $storage->deleteWebhookCall($webhook->getId());
        $storage->retrieveWebhookCall($webhook->getId());
    }

    protected function getValidConfig(): array
    {
        return [
            'name' => 'default',
            'signing_secret' => 'my-secret',
            'signature_header_name' => 'Signature',
            'signature_validator' => DefaultSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_storage' => 'default',
            'process_webhook_job' => ProcessWebhookJobTestClass::class,
        ];
    }
}
