<?php

use Illuminate\Support\Facades\Route;
use Spatie\WebhookClient\Exceptions\InvalidMethod;

it('registers a post route by default', function () {
    $route = Route::webhooks('incoming-webhooks');

    expect($route->methods())->toBe(['POST']);
    expect($route->getName())->toBe('webhook-client-default');
});

it('can register a route for another method', function () {
    $route = Route::webhooks('incoming-webhooks', 'default', 'get');

    expect($route->methods())->toBe(['GET', 'HEAD']);
});

it('can register a route for multiple methods', function () {
    $route = Route::webhooks('incoming-webhooks', 'default', ['post', 'put']);

    expect($route->methods())->toBe(['POST', 'PUT']);
});

it('throws when a method is not supported', function (array|string $methods) {
    Route::webhooks('incoming-webhooks', 'default', $methods);
})
    ->throws(InvalidMethod::class)
    ->with([
        'a single method' => 'options',
        'one of multiple methods' => [['post', 'options']],
    ]);
