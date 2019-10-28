<?php

namespace Spatie\WebhookClient\Storage;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\Models\DefaultWebhookCall;
use Illuminate\Contracts\Cache\Repository as CacheContract;

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
     * @var string
     */
    protected $prefix;

    /**
     * @param CacheContract $cache
     * @param int $minutes
     * @param string $prefix
     */
    public function __construct(CacheContract $cache, int $minutes, string $prefix)
    {
        $this->cache = $cache;
        $this->minutes = $minutes;
        $this->prefix = $prefix;
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

        $this->cache->put($this->getCacheKey($webhook->getId()), $webhook, $this->minutes * 60);

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
        $cacheKey = $this->getCacheKey($id);

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
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
        $this->cache->forget($this->getCacheKey($id));

        return true;
    }

    /**
     * @param string $id
     * @return string
     */
    protected function getCacheKey(string $id)
    {
        return $this->prefix.$id;
    }
}
