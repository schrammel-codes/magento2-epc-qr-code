# Changelog
## [2.2.1] - 2026-11-20
### Fixed
* Do not render the EPC QR code for orders not placed in EUR [PR #17](https://github.com/schrammel-codes/magento2-epc-qr-code/pull/17) - contributed by [lbajsarowicz](https://github.com/lbajsarowicz)
* Allow empty optional configuration values, so the beneficiary can be configured per website or store view [PR #19](https://github.com/schrammel-codes/magento2-epc-qr-code/pull/19)
  * Saving an empty IBAN in the default scope no longer fails
  * Skip BIC and customer hint if saved empty
  * Use default QR code colors if saved empty
  * Validate QR code colors as HEX (`#RRGGBB`) when saving, a missing `#` is added
  * Log an error if the beneficiary name is not configured

## [2.2.0] - 2026-07-10
### Fixed
* Fix for PHP 8.4, make nullable types explicit [PR #14](https://github.com/schrammel-codes/magento2-epc-qr-code/pull/14) - contributed by [hostep](https://github.com/hostep)

### Improved
* Code quality improvements: CI coverage with PHPUnit and Codecov [PR #15](https://github.com/schrammel-codes/magento2-epc-qr-code/pull/15)

## [2.1.0] - 2025-12-03
* Enable upgrades from 1.x to 2.1.0 onwards by introducing a hash check bypass on orders placed prior to v2.1.0 upgrade [PR #12](https://github.com/schrammel-codes/magento2-epc-qr-code/pull/12)

## [2.0.0] - 2025-11-28
* [BREAKING CHANGE]  Harden security on public QR code URL [PR #10](https://github.com/schrammel-codes/magento2-epc-qr-code/pull/10) - contributed by [rommelfreddy](https://github.com/rommelfreddy)

## [1.1.2] - 2025-07-01
### Fixed
* Introduce clean error handling if IBAN is not set

## [1.1.1] - 2025-04-25
### Fixed
* Update configuration screenshot in documentation to fix typo (Fixes [issue #5](https://github.com/schrammel-codes/magento2-epc-qr-code/issues/5))

## [1.1.0] - 2025-04-14
### Added
* Configuration possibility to select between public URL or `base64` image rendering [PR #3](https://github.com/schrammel-codes/magento2-epc-qr-code/pull/3)
