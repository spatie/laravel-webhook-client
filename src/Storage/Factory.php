<?php

namespace Spatie\WebhookClient\Storage;

interface Factory
{
    /**
     * Get storage implementation.
     *
     * @param string|null $storage
     * @return WebhookCallStorage
     */
    public function storage(?string $storage): WebhookCallStorage;
}
