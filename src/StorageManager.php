<?php

namespace Spatie\WebhookClient;

use Closure;
use InvalidArgumentException;

class StorageManager implements Storage\Factory
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved storage drivers.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new storage manager instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param string|null $storage
     * @return Storage\WebhookCallStorage
     */
    public function storage(?string $storage): Storage\WebhookCallStorage
    {
        $storage = $storage ?: $this->getDefaultDriver();

        return $this->storage[$storage] = $this->get($storage);
    }

    /**
     * Attempt to get the storage from the local cache.
     *
     * @param string $name
     * @return Storage\WebhookCallStorage
     */
    protected function get($name)
    {
        return $this->storage[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given storage.
     *
     * @param string $name
     * @return Storage\EloquentWebhookCallStorage
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (empty($config['driver'])) {
            throw new InvalidArgumentException("Storage [{$name}] does not have a configured driver.");
        }

        $name = $config['driver'];

        if (isset($this->customCreators[$name])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create'.ucfirst($name).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$name}] is not supported.");
        }
    }

    /**
     * Call a custom driver creator.
     *
     * @param array $config
     * @return Storage\EloquentWebhookCallStorage
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Create a eloquent store instance.
     *
     * @param array $config
     * @return Storage\EloquentWebhookCallStorage
     */
    protected function createEloquentDriver($config)
    {
        return new Storage\EloquentWebhookCallStorage($config['model']);
    }

    /**
     * Create a memory store instance.
     *
     * @param array $config
     * @return Storage\InMemoryWebhookCallStorage
     */
    protected function createMemoryDriver($config)
    {
        return new Storage\InMemoryWebhookCallStorage();
    }

    /**
     * Get the storage connection configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["webhook-client.storage.config.{$name}"] ?: [];
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['webhook-client.storage.default'];
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string $driver
     * @param \Closure $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }
}
