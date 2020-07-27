# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [unreleased]

### Changed

- Changed `set` to throw `ValueStoreException` if non scalar final values are passed (arrays are processed deep)
- Changed `Increment` to throw `ValueStoreException` if value is not integer
- Changed `Decrement` to throw `ValueStoreException` if value is not integer

## [1.0.0] - 20202-07-27

This component is part of the [OriginPHP framework](https://www.originphp.com/).