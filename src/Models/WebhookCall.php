<?php

namespace Spatie\WebhookClient\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Symfony\Component\HttpFoundation\HeaderBag;

class WebhookCall extends Model
{
    public $guarded = [];

    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
        'exception' => 'array',
    ];

    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        return self::create([
            'name' => $config->name,
            'headers' => $request->headers->all(),
            'payload' => $request->input(),
        ]);
    }

    public function headerBag(): HeaderBag
    {
        return new HeaderBag($this->headers ?? []);
    }

    public function saveException(Exception $exception): self
    {
        $this->exception = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ];

        $this->save();

        return $this;
    }

    public function clearException(): self
    {
        $this->exception = null;

        $this->save();

        return $this;
    }
}
