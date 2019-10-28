<?php

namespace Spatie\WebhookClient\Tests;

use Illuminate\Foundation\Application;
use InvalidArgumentException;
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
}
