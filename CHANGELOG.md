# Changelog

All notable changes to `laravel-webhook-client` will be documented in this file

## 3.1.2 - 2022-04-07

- Change `WebhookConfigRepository` instance to `scoped` (Octane support)

**Full Changelog**: https://github.com/spatie/laravel-webhook-client/compare/3.1.1...3.1.2

## 3.1.1 - 2022-04-07

## What's Changed

- Fix typo by @christophrumpel in https://github.com/spatie/laravel-webhook-client/pull/129
- Update UPGRADING.md by @binaryk in https://github.com/spatie/laravel-webhook-client/pull/131
- Typo fix by @chimit in https://github.com/spatie/laravel-webhook-client/pull/139
- WebhookCall Model @property $payload exception and headers by @wbemanuel in https://github.com/spatie/laravel-webhook-client/pull/140

## New Contributors

- @christophrumpel made their first contribution in https://github.com/spatie/laravel-webhook-client/pull/129
- @binaryk made their first contribution in https://github.com/spatie/laravel-webhook-client/pull/131
- @chimit made their first contribution in https://github.com/spatie/laravel-webhook-client/pull/139
- @wbemanuel made their first contribution in https://github.com/spatie/laravel-webhook-client/pull/140

**Full Changelog**: https://github.com/spatie/laravel-webhook-client/compare/3.1.0...3.1.1

## 3.1.0 - 2022-01-11

- allow Laravel 9

## 3.0.3 - 2021-12-07

## What's Changed

- Return the Route object for method chaining by @erikwittek in https://github.com/spatie/laravel-webhook-client/pull/114

## New Contributors

- @erikwittek made their first contribution in https://github.com/spatie/laravel-webhook-client/pull/114

**Full Changelog**: https://github.com/spatie/laravel-webhook-client/compare/3.0.2...3.0.3

## 3.0.2 - 2021-12-07

- PHP 8.1 compatibility

## 3.0.2 - 2021-12-07

- PHP 8.1 support

## 3.0.1 - 2021-07-25

- Add `headers` method

## 3.0.0 - 2021-07-25

- Store headers and URL
- Require PHP 8
- Require Laravel 8
- Internals cleanup

## 2.7.5 - 2021-01-08

- Fix PHP constraint

## 2.7.4 - 2020-11-28

- Add support for PHP 8

## 2.7.3 - 2020-10-10

- Fix docblock

## 2.7.2 - 2020-09-08

- Add support for Laravel 8

## 2.7.1 - 2020-04-30

- Use default webhook response as fallback (#60)

## 2.7.0 - 2020-04-30

- Add support for creating your own webhook response

## 2.6.1 - 2020-04-20

- Use job class instead of instance in webhook config

## 2.6.0 - 2020-04-15

- Drop support for Laravel 5

## 2.5.0 - 2020-03-02

- Add support for Laravel 7

## 2.4.1 - 2020-01-20

- Support older Laravel versions

## 2.4.0 - 2019-12-08

- Drop support for PHP 7.3 and below

## 2.3.0 - 2019-10-30

- Add `WebhookConfigRepository` to make it easier to programmatically add config

## 2.2.0 - 2019-09-04

- Add Laravel 6 support

## 2.1.1 - 2019-09-02

- Use `bigInteger` by default

## 2.1.0 - 2019-07-09

- Added an overridable method `storeWebhook` on the `WebhookCall` model.

## 2.0.1 - 2019-07-08

- Make `signing_secret` and `signature_header_name` config keys optional

## 2.0.0 - 2019-07-08

- `DefaultSignatureValidator` is now responsible for verifying that a signature header has been set
- `InvalidSignatureEvent` now only gets the `$request`

## 1.0.2 - 2019-07-01

- Remove handle abstract method from `ProcessWebhookJob` to allow DI.

## 1.0.1 - 2019-06-19

- Fix config file

## 1.0.0 - 2019-06-14

- Initial release
