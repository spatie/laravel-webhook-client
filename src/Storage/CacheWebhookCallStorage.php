<?php

namespace Spatie\WebhookClient\Storage;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\WebhookClient\Models\DefaultWebhookCall;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class CacheWebhookCallStorage implements WebhookCallStorage
{
    /**
     * @var CacheContract
     */
    protected $cache;

    /**
     * @var int
     */
    protected $minutes;

    /**
     * @param CacheContract $cache
     * @param int $minutes
     */
    public function __construct(CacheContract $cache, $minutes)
    {
        $this->cache = $cache;
        $this->minutes = $minutes;
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

        $this->cache->put($webhook->getId(), $webhook, $this->minutes * 60);

        return $webhook;
    }

    /**
     * Retrieve a webhook by given id.
     *
     * @param string $id
     * @return WebhookCall
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    public function retrieveWebhookCall(string $id): WebhookCall
    {
        if ($this->cache->has($id)) {
            return $this->cache->get($id);
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
        $this->cache->forget($id);

        return true;
    }
}
