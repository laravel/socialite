# Release Notes

## [Unreleased](https://github.com/laravel/socialite/compare/v5.9.1...5.x)

## [v5.9.1](https://github.com/laravel/socialite/compare/v5.9.0...v5.9.1) - 2023-09-07

- (feat) Extract some logic to a `userInstance` method. by [@lucasmichot](https://github.com/lucasmichot) in https://github.com/laravel/socialite/pull/663

## [v5.9.0](https://github.com/laravel/socialite/compare/v5.8.1...v5.9.0) - 2023-09-05

- [5.x] Include GitHub `node_id` field by [@lucasmichot](https://github.com/lucasmichot) in https://github.com/laravel/socialite/pull/657
- [5.x] Adds `LinkedInOpenId` provider by [@nunomaduro](https://github.com/nunomaduro) in https://github.com/laravel/socialite/pull/662

## [v5.8.1](https://github.com/laravel/socialite/compare/v5.8.0...v5.8.1) - 2023-08-21

- Fix phpstan issues in Twitter and Slack drivers by [@alecpl](https://github.com/alecpl) in https://github.com/laravel/socialite/pull/653

## [v5.8.0](https://github.com/laravel/socialite/compare/v5.7.0...v5.8.0) - 2023-07-14

- Update Slack provider to use v2 API and allow Bot tokens by [@jbrooksuk](https://github.com/jbrooksuk) in https://github.com/laravel/socialite/pull/645

## [v5.7.0](https://github.com/laravel/socialite/compare/v5.6.3...v5.7.0) - 2023-07-08

- Add support for Slack driver by [@jbrooksuk](https://github.com/jbrooksuk) in https://github.com/laravel/socialite/pull/644

## [v5.6.3](https://github.com/laravel/socialite/compare/v5.6.2...v5.6.3) - 2023-06-06

- Add buildProvider method on DockBlock for IDE  support by @emrancu in https://github.com/laravel/socialite/pull/643

## [v5.6.2](https://github.com/laravel/socialite/compare/v5.6.1...v5.6.2) - 2023-05-29

- Fix unable to use updated config object when using Laravel Octane by @aprokopenko in https://github.com/laravel/socialite/pull/639

## [v5.6.1](https://github.com/laravel/socialite/compare/v5.6.0...v5.6.1) - 2023-01-20

### Fixed

- Add app property by @driesvints in https://github.com/laravel/socialite/pull/621

## [v5.6.0](https://github.com/laravel/socialite/compare/v5.5.8...v5.6.0) - 2023-01-13

### Added

- Laravel v10 Support by @driesvints in https://github.com/laravel/socialite/pull/618

## [v5.5.8](https://github.com/laravel/socialite/compare/v5.5.7...v5.5.8) - 2023-01-05

### Fixed

- Fix User return types by @antoinelame in https://github.com/laravel/socialite/pull/614

## [v5.5.7](https://github.com/laravel/socialite/compare/v5.5.6...v5.5.7) - 2022-12-28

### Fixed

- Fixed string type issue by @driesvints in https://github.com/laravel/socialite/commit/295a36648828f0419c1e9cbedd97609d2a4cb211

## [v5.5.6](https://github.com/laravel/socialite/compare/v5.5.5...v5.5.6) - 2022-11-08

### Changed

- PHP 8.2 support by @driesvints in https://github.com/laravel/socialite/pull/607

## [v5.5.5](https://github.com/laravel/socialite/compare/v5.5.4...v5.5.5) - 2022-08-20

### Changed

- Add ability to override access token request headers by @JasonTolliver in https://github.com/laravel/socialite/pull/603

## [v5.5.4](https://github.com/laravel/socialite/compare/v5.5.3...v5.5.4) - 2022-08-08

### Fixed

- Add correct encoding and the required state param by @xhezairbey in https://github.com/laravel/socialite/pull/599

## [v5.5.3](https://github.com/laravel/socialite/compare/v5.5.2...v5.5.3) - 2022-07-18

### Fixed

- Add Twitter OAuth 2 fallback call by @driesvints in https://github.com/laravel/socialite/pull/596

## [v5.5.2](https://github.com/laravel/socialite/compare/v5.5.1...v5.5.2) - 2022-03-15

### Changed

- Allow OAuth version in the Twitter config by @taylorotwell ([68afb03](https://github.com/laravel/socialite/commit/68afb03259b82d898c68196cbcacd48596a9dd72))

## [v5.5.1](https://github.com/laravel/socialite/compare/v5.5.0...v5.5.1) - 2022-02-08

### Fixed

- Override abstract provider for Twitter's OAuth 2.0 provider ([#576](https://github.com/laravel/socialite/pull/576))

## [v5.5.0](https://github.com/laravel/socialite/compare/v5.4.0...v5.5.0) - 2022-02-01

### Changed

- add/set approvedScopes in User ([#572](https://github.com/laravel/socialite/pull/572))

## [v5.4.0 (2022-01-25)](https://github.com/laravel/socialite/compare/v5.3.0...v5.4.0)

### Added

- Add Twitter OAuth2 provider ([#574](https://github.com/laravel/socialite/pull/574))

## [v5.3.0 (2022-01-12)](https://github.com/laravel/socialite/compare/v5.2.6...v5.3.0)

### Changed

- Laravel 9 Support ([#571](https://github.com/laravel/socialite/pull/571))

## [v5.2.6 (2021-12-07)](https://github.com/laravel/socialite/compare/v5.2.5...v5.2.6)

### Fixed

- Fix PHP 8.1 issues ([#567](https://github.com/laravel/socialite/pull/567))

## [v5.2.5 (2021-08-31)](https://github.com/laravel/socialite/compare/v5.2.4...v5.2.5)

### Changed

- Make `enablePKCE` public ([#550](https://github.com/laravel/socialite/pull/550))

## [v5.2.4 (2021-08-10)](https://github.com/laravel/socialite/compare/v5.2.3...v5.2.4)

### Changed

- Handle 'scope' for Twitter Oauth1 ([#548](https://github.com/laravel/socialite/pull/548))

## [v5.2.3 (2021-04-06)](https://github.com/laravel/socialite/compare/v5.2.2...v5.2.3)

### Changed

- Add reset methods for Octane ([07840c0](https://github.com/laravel/socialite/commit/07840c07e7b5cf20bb31f8bc2332776a2411695e))

## [v5.2.2 (2021-03-02)](https://github.com/laravel/socialite/compare/v5.2.1...v5.2.2)

### Fixed

- Update provider to use DeferrableProvider instead ([#529](https://github.com/laravel/socialite/pull/529))

## [v5.2.1 (2021-02-22)](https://github.com/laravel/socialite/compare/v5.2.0...v5.2.1)

### Fixed

- Make PKCE opt-in ([#523](https://github.com/laravel/socialite/pull/523))

## [v5.2.0 (2021-02-16)](https://github.com/laravel/socialite/compare/v5.1.3...v5.2.0)

### Added

- Add support for OAuth 2.0 PKCE extension ([#518](https://github.com/laravel/socialite/pull/518))

## [v5.1.3 (2021-01-05)](https://github.com/laravel/socialite/compare/v5.1.2...v5.1.3)

### Added

- Added support for self hosted Gitlab instances ([#510](https://github.com/laravel/socialite/pull/510))

### Fixed

- Fix scope separator in Gitlab provider ([#512](https://github.com/laravel/socialite/pull/512))

## [v5.1.2 (2020-12-04)](https://github.com/laravel/socialite/compare/v5.1.1...v5.1.2)

### Security

- Revert Facebook picture access token changes ([#504](https://github.com/laravel/socialite/pull/504))

## [v5.1.1 (2020-11-24)](https://github.com/laravel/socialite/compare/v5.1.0...v5.1.1)

### Changed

- Cache User instance to allow `$provider->user()` to be called multiple times ([#498](https://github.com/laravel/socialite/pull/498))

## [v5.1.0 (2020-11-03)](https://github.com/laravel/socialite/compare/v5.0.3...v5.1.0)

### Added

- PHP 8 Support ([#495](https://github.com/laravel/socialite/pull/495))

## [v5.0.3 (2020-10-21)](https://github.com/laravel/socialite/compare/v5.0.2...v5.0.3)

### Fixed

- Include access token when requesting Facebook user profile photo ([#489](https://github.com/laravel/socialite/pull/489))

## [v5.0.2 (2020-10-20)](https://github.com/laravel/socialite/compare/v5.0.1...v5.0.2)

### Fixed

- Include `grant_type=authorization` code in token request parameters ([#488](https://github.com/laravel/socialite/pull/488))

## [v5.0.1 (2020-09-12)](https://github.com/laravel/socialite/compare/v5.0.0...v5.0.1)

### Fixed

- Fix bitbucket provider ([#475](https://github.com/laravel/socialite/pull/475))

## [v5.0.0 (2020-09-08)](https://github.com/laravel/socialite/compare/v4.4.1...v5.0.0)

### Added

- Add custom exceptions for providers of One ([#440](https://github.com/laravel/socialite/pull/440))
- Support Laravel 8 ([#465](https://github.com/laravel/socialite/pull/465), [#466](https://github.com/laravel/socialite/pull/466))

### Changed

- Only use the `read_user` scope for GitLab by default ([#403](https://github.com/laravel/socialite/pull/403))

### Removed

- Drop Laravel 5.7 support ([0bd64ae](https://github.com/laravel/socialite/commit/0bd64aefccf9b4d4dfee79ebe111003e392b1628))
- Drop Laravel 5.8 support ([4757ec4c](https://github.com/laravel/socialite/commit/4757ec4cc689e457fb161dd0afed4845a26cedff))
- Drop PHP 7.1 support ([6e21f1a](https://github.com/laravel/socialite/commit/6e21f1abdde6cd7a8deb4d5c1d2fb5d89dede6e7))

## [v4.4.1 (2020-06-03)](https://github.com/laravel/socialite/compare/v4.4.0...v4.4.1)

### Fixed

- Fix containter call ([#450](https://github.com/laravel/socialite/pull/450))

## [v4.4.0 (2020-06-02)](https://github.com/laravel/socialite/compare/v4.3.2...v4.4.0)

### Added

- Support Guzzle 7 ([#449](https://github.com/laravel/socialite/pull/449))

### Removed

- Remove guzzlehttp/guzzle ~5.0 workaround ([#448](https://github.com/laravel/socialite/pull/448))

## [v4.3.2 (2020-02-04)](https://github.com/laravel/socialite/compare/v4.3.1...v4.3.2)

### Fixed

- Use Authorization header for Github provider ([#430](https://github.com/laravel/socialite/pull/430))

## [v4.3.1 (2019-11-26)](https://github.com/laravel/socialite/compare/v4.3.0...v4.3.1)

### Fixed

- Override `SocialiteServiceProvider::isDeferred()` to mark as deferred ([#412](https://github.com/laravel/socialite/pull/412))

## [v4.3.0 (2019-11-19)](https://github.com/laravel/socialite/compare/v4.2.0...v4.3.0)

### Added

- Allow to manually set desired Facebook graph version ([#408](https://github.com/laravel/socialite/pull/408))

### Changed

- Change default Facebook graph version to 3.3 ([#408](https://github.com/laravel/socialite/pull/408))

## [v4.2.0 (2019-09-03)](https://github.com/laravel/socialite/compare/v4.1.4...v4.2.0)

### Added

- Laravel 6.0 support ([#390](https://github.com/laravel/socialite/pull/390))

## [v4.1.4 (2019-07-30)](https://github.com/laravel/socialite/compare/v4.1.3...v4.1.4)

### Changed

- Updated version constraints for Laravel 6.0 ([3fe71f1](https://github.com/laravel/socialite/commit/3fe71f1c593967e5b6046977b310e287f40ee92d))

## [v4.1.3 (2019-04-02)](https://github.com/laravel/socialite/compare/v4.1.2...v4.1.3)

### Fixed

- Fix bug with no LinkedIn email addresses ([#355](https://github.com/laravel/socialite/pull/355))

## [v4.1.2 (2019-03-15)](https://github.com/laravel/socialite/compare/v4.1.1...v4.1.2)

### Fixed

- Use proper key name for original avatar in Google Provider ([5ec0024](https://github.com/laravel/socialite/commit/5ec0024284d15df527376ced59b9e7b393f6f88b))

## [v4.1.1 (2019-03-12)](https://github.com/laravel/socialite/compare/v4.1.0...v4.1.1)

### Fixed

- Update Google API urls ([#346](https://github.com/laravel/socialite/pull/346))

## [v4.1.0 (2019-02-14)](https://github.com/laravel/socialite/compare/v4.0.3...v4.1.0)

### Added

- Laravel 5.8 support ([32b5ecf](https://github.com/laravel/socialite/commit/32b5ecf537648759bbb90dec8298424477c14f19))

### Fixed

- Handle Google responses excluding 'name' ([#340](https://github.com/laravel/socialite/pull/340))

## [v4.0.3 (2019-01-21)](https://github.com/laravel/socialite/compare/v4.0.2...v4.0.3)

### Fixed

- Use proper localized name ([#329](https://github.com/laravel/socialite/pull/329))

## [v4.0.2 (2019-01-21)](https://github.com/laravel/socialite/compare/v4.0.1...v4.0.2)

### Fixed

- Fix a bug with no LinkedIn image ([81adfcc](https://github.com/laravel/socialite/commit/81adfcc4f7df3a470cdab8a500db77c0de5d01a3))

## [v4.0.1 (2018-12-20)](https://github.com/laravel/socialite/compare/v4.0.0...v4.0.1)

### Fixed

- Fixed broken `GoogleProvider` ([#316](https://github.com/laravel/socialite/pull/316))

## [v4.0.0 (2018-12-18)](https://github.com/laravel/socialite/compare/v3.2.0...v4.0.0)

### Changed

- Removed support for PHP 7.0 ([#311](https://github.com/laravel/socialite/pull/311))
- Require Laravel 5.7 as minimum version ([#311](https://github.com/laravel/socialite/pull/311))

### Fixed

- Fixed and updated the broken LinkedIn provider ([#310](https://github.com/laravel/socialite/pull/310))
