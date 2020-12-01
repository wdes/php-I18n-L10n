# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v2.1.0] - 2020-12-01

- Add support for PHP 8.0

## [v2.0.0] - 2020-03-30
### Added
- Add support for phpunit 8 and 9
- Add a .gitattributes file
- Improved code coverage

### Changed
- Upgraded squizlabs/php_codesniffer from 3.3.x to 3.5.x
- Upgraded phpstan/phpstan from 0.11.8+ to 0.12+
- BREAKING CHANGE rename namespace to Wdes\phpI18nL10n

### Fixed
- Test phpunit suite
- .editorconfig for *.neon files

### Removed
- twig/extensions dependency
- TravisCI setup
- scripts directory from dist version
- dev files from dist version
- example from dist version
- Utils class
- CI scripts
- Some useless files

## [v1.0.0] - 2019-06-08
### Added
- First stable version

[Unreleased]: https://github.com/wdes/php-I18n-L10n/compare/v1.0.0...HEAD
[v1.0.0]: https://github.com/wdes/php-I18n-L10n/releases/tag/v1.0.0
