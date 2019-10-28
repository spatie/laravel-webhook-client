<?php

namespace Spatie\WebhookClient\Tests\Storage;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Application;
use Spatie\WebhookClient\Events\WebhookCallDeletedEvent;
use Spatie\WebhookClient\Events\WebhookCallStoredEvent;
use Spatie\WebhookClient\Storage\WebhookCallStorageAdapter;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\Storage\InMemoryWebhookCallStorage;
use Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class WebhookCallStorageAdapterTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_fire_stored_event()
    {
        $storage = new InMemoryWebhookCallStorage();

        $dispatcher = $this->createMock(Dispatcher::class);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(WebhookCallStoredEvent::class));

        $adapter = new WebhookCallStorageAdapter($storage, $dispatcher);

        $webhook = $adapter->storeWebhookCall(
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
    public function it_should_fire_deleted_event()
    {
        $storage = new InMemoryWebhookCallStorage();

        $dispatcher = $this->createMock(Dispatcher::class);

        $dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(WebhookCallStoredEvent::class)],
                [$this->isInstanceOf(WebhookCallDeletedEvent::class)]
            );

        $adapter = new WebhookCallStorageAdapter($storage, $dispatcher);

        $webhook = $adapter->storeWebhookCall(
            new WebhookConfig(new Application(), $storage, $this->getValidConfig()),
            (new Request())->replace(['payload'])
        );

        $adapter->deleteWebhookCall($webhook->getId());
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
