<?php

namespace Spatie\WebhookClient\Tests\Storage;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\EloquentWebhookCall;
use Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator;
use Spatie\WebhookClient\Storage\EloquentWebhookCallStorage;
use Spatie\WebhookClient\Tests\TestCase;
use Spatie\WebhookClient\Tests\TestClasses\NonWebhookCallModel;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class EloquentWebhookCallStorateTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_throw_exception_on_non_model_class()
    {
        $this->expectException(\RuntimeException::class);

        new EloquentWebhookCallStorage(\stdClass::class);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_on_invalid_model_class()
    {
        $this->expectException(\RuntimeException::class);

        new EloquentWebhookCallStorage(NonWebhookCallModel::class);
    }

    /**
     * @test
     */
    public function it_should_store_webhook()
    {
        $storage = new EloquentWebhookCallStorage(EloquentWebhookCall::class);

        $webhook = $storage->storeWebhookCall(
            new WebhookConfig(new Application(), $storage, $this->getValidConfig()),
            (new Request())->replace(['payload'])
        );

        $this->assertNotEmpty($webhook->getId());
        $this->assertEquals('default', $webhook->getName());
        $this->assertEquals(['payload'], $webhook->getPayload());

        $retrievedWebhook = $storage->retrieveWebhookCall($webhook->getId());

        $this->assertEquals($webhook->getId(), $retrievedWebhook->getId());
        $this->assertEquals($webhook->getName(), $retrievedWebhook->getName());
        $this->assertEquals($webhook->getPayload(), $retrievedWebhook->getPayload());
    }

    /**
     * @test
     */
    public function it_should_delete_webhook()
    {
        $this->expectException(\OutOfBoundsException::class);

        $storage = new EloquentWebhookCallStorage(EloquentWebhookCall::class);

        $webhook = $storage->storeWebhookCall(
            new WebhookConfig(new Application(), $storage, $this->getValidConfig()),
            (new Request())->replace(['payload'])
        );

        $retrievedWebhook = $storage->retrieveWebhookCall($webhook->getId());

        $this->assertEquals($webhook->getId(), $retrievedWebhook->getId());
        $this->assertEquals($webhook->getName(), $retrievedWebhook->getName());
        $this->assertEquals($webhook->getPayload(), $retrievedWebhook->getPayload());

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
