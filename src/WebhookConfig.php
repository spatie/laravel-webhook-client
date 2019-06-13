<?php

namespace Spatie\WebhookClient;

class WebhookConfig
{
    /** @var string */
    public $name;

    /** @var string */
    public $signingSecret;

    /** @var string */
    public $signatureHeaderName;

    /** @var \Spatie\WebhookClient\SignatureValidator\SignatureValidator  */
    public $signatureValidator;

    /** @var string */
    public $modelClass;

    public function __construct(array $properties)
    {
        $this->name = $properties['name'];

        $this->signingSecret = $properties['signing_secret'];

        $this->signatureHeaderName = $properties['signature_header_name'];

        $this->signatureValidator = app($properties['signature_validator']);

        $this->modelClass = $properties['model_class'];
    }
}

