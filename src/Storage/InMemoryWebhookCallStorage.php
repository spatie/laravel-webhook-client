<?php

namespace Spatie\WebhookClient\Storage;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\Models\DefaultWebhookCall;

class InMemoryWebhookCallStorage implements WebhookCallStorage
{
    /**
     * Local webhook storage.
     *
     * @var WebhookCall[]
     */
    protected $storage = [];

    /**
     * @param WebhookCall[] $storage
     */
    public function __construct(array $storage = [])
    {
        $this->storage = $storage;
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
        $webhook = new DefaultWebhookCall((string) Str::uuid(), (string) $config->name, (array) $request->input());

        $this->storage[$webhook->getId()] = $webhook;

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
        if (isset($this->storage[$id])) {
            return $this->storage[$id];
        }

        throw new \OutOfBoundsException(sprintf('Given webhook call does not exist in storage [%s]', $id));
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
        unset($this->storage[$id]);

        return true;
    }
}
