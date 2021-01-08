# Changelog

All notable changes to `laravel-webhook-client` will be documented in this file

## 2.7.5 - 2021-01-08

- Fix php constraint

## 2.7.4 - 2020-11-28

- add support for PHP 8

## 2.7.3 - 2020-10-10

- fix docblock

## 2.7.2 - 2020-09-08

- add support for Laravel 8

## 2.7.1 - 2020-04-30

- use default webhook response as fallback (#60)

## 2.7.0 - 2020-04-30

- add support for creating your own webhook response

## 2.6.1 - 2020-04-20

- use job class instead of instance in webhook config

## 2.6.0 - 2020-04-15

- drop support for Laravel 5

## 2.5.0 - 2020-03-02

- add support for Laravel 7

## 2.4.1 - 2020-01-20

- support older Laravel versions

## 2.4.0 - 2019-12-08

- drop support for PHP 7.3 and below

## 2.3.0 - 2019-10-30

- add `WebhookConfigRepository` to make it easier to programmatically add config 

## 2.2.0 - 2019-09-04

- Add Laravel 6 support

## 2.1.1 - 2019-09-02

- use `bigInteger` by default

## 2.1.0 - 2019-07-09

- added an overridable method `storeWebhook` on the `WebhookCall` model.

## 2.0.1 - 2019-07-08

- make `signing_secret` and `signature_header_name` config keys optional

## 2.0.0 - 2019-07-08

- `DefaultSignatureValidator` is now responsible for verifying that a signature header has been set
- `InvalidSignatureEvent` now only gets the `$request`

## 1.0.2 - 2019-07-01

- remove handle abstract method from `ProcessWebhookJob` to allow DI.

## 1.0.1 - 2019-06-19

- fix config file

## 1.0.0 - 2019-06-14

- initial release
