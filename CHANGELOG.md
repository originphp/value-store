# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.6] - 2021-01-04

Bumped copyright years, updated version dependencies.

## [1.0.5] - 2020-11-29

### Fixed

- Fixed minimum PHP version in composer this makes no sense for a new package. 

## [1.0.4] - 2020-11-27

### Fixed

- Fixed installing suggested packages by default

## [1.0.2] - 2020-07-29

No changes, cleaned up and improved testing

## [1.0.1] - 2020-07-28

### Added

- Added `escape` option for enabling/disabling escaping of forward slashes in JSON. This was security option to prevent embedding of script tags.
- Added `json_decode` error handling

### Changed

- Changed `set` to throw `ValueStoreException` if non scalar final values are passed (arrays are processed deep)
- Changed `Increment` to throw `ValueStoreException` if value is not integer
- Changed `Decrement` to throw `ValueStoreException` if value is not integer

## [1.0.0] - 2020-07-27

This component is part of the [OriginPHP framework](https://www.originphp.com/).