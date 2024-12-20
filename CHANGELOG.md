# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.8.0] - 2024-12-14

### Added

- Add Gravity Forms file upload field compatibility

### Changed

- Rename package to `achttienvijftien/media`

## [0.7.1] - 2024-09-25

### Fixed

- Better handling of image srcsets

## [0.7.0] - 2024-04-05

### Changed

- Implemented a better way of overriding attachment URL's without needing to force override generic upload URL

## [0.6.1] - 2023-06-19

### Fixed

- Fixed bug with wrong expected response type of `WP_Http::request()` (array instead of object)

## [0.6.0] - 2023-06-17

### Fixed

- Added WordPress 6.2 support by changing requests to WP builtin function where possible and where needed based
  implementation on WordPress version

## [0.5.1] - 2023-01-01

### Fixed

- Determine mime-type when not yet present in metadata by local file if it still exists

## [0.5.0] - 2022-12-30

### Added

- When attachments are deleted from media library they'll be deleted from media as well
- Added support of uploading attachment types other than images

## [0.4.1] - 2022-05-23

### Fixed

- Thumbnails in media library are now using the correct size urls

## [0.4.0] - 2022-05-23

### Added

- Implemented editable image functionality

## [0.3.1] - 2022-04-02

### Added

- Added media url to preconnect resource hints as well

## [0.3.0] - 2022-03-11

### Added

- Added media url to dns-prefetch

[unreleased]: https://github.com/achttienvijftien/media/compare/0.8.0...master

[0.8.0]: https://github.com/achttienvijftien/media/compare/0.7.1...0.8.0

[0.7.1]: https://github.com/achttienvijftien/media/compare/0.7.0...0.7.1

[0.7.0]: https://github.com/achttienvijftien/media/compare/0.6.1...0.7.0

[0.6.1]: https://github.com/achttienvijftien/media/compare/0.6.0...0.6.1

[0.6.0]: https://github.com/achttienvijftien/media/compare/0.5.1...0.6.0

[0.5.1]: https://github.com/achttienvijftien/media/compare/0.5.0...0.5.1

[0.5.0]: https://github.com/achttienvijftien/media/compare/0.4.1...0.5.0

[0.4.1]: https://github.com/achttienvijftien/media/compare/0.4.0...0.4.1

[0.4.0]: https://github.com/achttienvijftien/media/compare/0.3.1...0.4.0

[0.3.1]: https://github.com/achttienvijftien/media/compare/0.3.0...0.3.1

[0.3.0]: https://github.com/achttienvijftien/media/compare/0.2.3...0.3.0
