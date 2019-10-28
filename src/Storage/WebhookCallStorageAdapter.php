<?php

namespace Spatie\WebhookClient\Storage;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Illuminate\Contracts\Events\Dispatcher;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\Events\WebhookCallStoredEvent;
use Spatie\WebhookClient\Events\WebhookCallDeletedEvent;

class WebhookCallStorageAdapter implements WebhookCallStorage
{
    /**
     * @var WebhookCallStorage
     */
    protected $storage;

    /**
     * @var Dispatcher|null
     */
    protected $dispatcher;

    /**
     * @param WebhookCallStorage $storage
     * @param Dispatcher|null $dispatcher
     */
    public function __construct(WebhookCallStorage $storage, ?Dispatcher $dispatcher)
    {
        $this->storage = $storage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Store given webhook call.
     *
     * @param WebhookConfig $config
     * @param Request $request
     * @return WebhookCall
     */
    public function storeWebhookCall(WebhookConfig $config, Request $request): WebhookCall
    {
        $webhook = $this->storage->storeWebhookCall($config, $request);

        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new WebhookCallStoredEvent($webhook));
        }

        return $webhook;
    }

    /**
     * Retrieve a webhook by given id.
     *
     * @param string $id
     * @return WebhookCall
     * @throws \OutOfBoundsException
     */
    public function retrieveWebhookCall(string $id): WebhookCall
    {
        return $this->storage->retrieveWebhookCall($id);
    }

    /**
     * Delete given webhook from storage.
     *
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function deleteWebhookCall(string $id): bool
    {
        $webhook = $this->storage->retrieveWebhookCall($id);

        if ($this->storage->deleteWebhookCall($id)) {
            if ($this->dispatcher) {
                $this->dispatcher->dispatch(new WebhookCallDeletedEvent($webhook));
            }

            return true;
        }

        return false;
    }

    /**
     * @return WebhookCallStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
