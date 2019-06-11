<?php

namespace Spatie\WebhookClient\SignatureValidator;

use Illuminate\Http\Request;

interface SignatureValidator
{
    public function isValid(Request $request);
}
