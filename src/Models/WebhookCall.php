<?php

namespace Spatie\WebhookClient\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookCall extends Model
{
    public $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'exception' => 'array',
    ];
}

