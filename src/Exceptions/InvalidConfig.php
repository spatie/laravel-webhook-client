<?php

namespace Spatie\WebhookClient\Exceptions;

use Exception;

class InvalidConfig extends Exception
{
    public static function couldNotFindConfig(string $notFoundConfigName)
    {
        return new static("Could not find the configuration for `{$notFoundConfigName}`");
    }
}

