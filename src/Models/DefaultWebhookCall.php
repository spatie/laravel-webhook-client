<?php

namespace Spatie\WebhookClient\Models;

class DefaultWebhookCall implements WebhookCall
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @param string $id
     * @param string $name
     * @param array $payload
     */
    public function __construct(string $id, string $name, array $payload)
    {
        $this->id = $id;
        $this->name = $name;
        $this->payload = $payload;
    }

    /**
     * Return webhook's ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns called webhook's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns webhook's payload.
     *
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
