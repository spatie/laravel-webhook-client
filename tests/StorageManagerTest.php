<?php

namespace Spatie\WebhookClient\Tests;

use Illuminate\Cache\CacheManager;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Spatie\WebhookClient\Models\EloquentWebhookCall;
use Spatie\WebhookClient\Storage\CacheWebhookCallStorage;
use Spatie\WebhookClient\Storage\EloquentWebhookCallStorage;
use Spatie\WebhookClient\Storage\InMemoryWebhookCallStorage;
use Spatie\WebhookClient\StorageManager;

class StorageManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_on_unsupported_driver()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage [local] does not have a configured driver.');
        $manager = new StorageManager(tap(new Application, function ($app) {
            $app['config'] = ['webhook-client.storage.config.local' => null];
        }));
        $manager->storage('local');
    }

    /**
     * @test
     */
    public function it_creates_in_memory_storage_driver()
    {
        $manager = new StorageManager(tap(new Application(), function (Application $app) {
            $app['config'] = ['webhook-client.storage.config.memory' => ['driver' => 'memory']];
        }));

        $this->assertInstanceOf(InMemoryWebhookCallStorage::class, $manager->storage('memory'));
    }

    /**
     * @test
     */
    public function it_creates_in_eloquent_storage_driver()
    {
        $manager = new StorageManager(tap(new Application(), function (Application $app) {
            $app['config'] = ['webhook-client.storage.config.eloquent' => [
                'driver' => 'eloquent',
                'model' => EloquentWebhookCall::class,
            ]];
        }));

        $this->assertInstanceOf(EloquentWebhookCallStorage::class, $manager->storage('eloquent'));
    }

    /**
     * @test
     */
    public function it_creates_in_cache_storage_driver()
    {
        $manager = new StorageManager(tap(new Application(), function (Application $app) {
            $app['cache'] = new CacheManager($app);

            $app['config'] = [
                'cache.stores.array' => [
                    'driver' => 'array',
                ],
                'webhook-client.storage.config.cache' => [
                    'driver' => 'cache',
                    'store' => 'array',
                    'lifetime' => 10,
                    'prefix' => 'webhook_call:',
                ],
            ];
        }));

        $this->assertInstanceOf(CacheWebhookCallStorage::class, $manager->storage('cache'));
    }
}
