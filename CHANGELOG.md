# Changelog

All notable changes to `laravel-webhook-client` will be documented in this file

## 2.0.0 - 2019-07-08

- `DefaultSignatureValidator` is now responsible for verifying that a signature header has been set
- `InvalidSignatureEvent` now only gets the `$request`

## 1.0.2 - 2019-07-01

- remove handle abstract method from `ProcessWebhookJob` to allow DI.

## 1.0.1 - 2019-06-19

- fix config file

## 1.0.0 - 2019-06-14

- initial release
