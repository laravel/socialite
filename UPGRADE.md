# Upgrade Guide

## Upgrading To 4.0 From 3.0

### PHP & Laravel Version Requirements

Like the latest releases of the Laravel framework, Laravel Socialite now requires PHP >= 7.1.3. We encourage you to upgrade to the latest versions of PHP and Laravel before upgrading to Socialite 4.0.

### LinkedInProvider Changes

The `LinkedInProvider` was updated to make use of the latest API version of LinkedIn. The provider will now only retrieve the most basic fields and removes the `fields` method from the provider. Please review [the related PR](https://github.com/laravel/socialite/pull/310) for all details and changes.
