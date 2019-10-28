<?php

namespace Spatie\WebhookClient\Models;

interface WebhookCall
{
    /**
     * Return webhook's ID.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Returns called webhook's name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns webhook's payload.
     *
     * @return array
     */
    public function getPayload(): array;
}
