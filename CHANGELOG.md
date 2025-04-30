# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning] (http://semver.org/). For change log format,
use [Keep a Changelog] (http://keepachangelog.com/).

## [1.0.0] - 2025-04-30

No changes were introduced since the previous beta 10 release.

## [1.0.0-beta10] - 2025-03-14

### Changed

- Bump `hectororm/connection` version to 1.0.0-beta9

### Fixed

- Compatibility with MariaDB >= 11.5 to get charsets
- Implicitly marking parameter as nullable

## [1.0.0-beta9] - 2025-02-04

### Changed

- Bump `hectororm/connection` version to 1.0.0-beta8

## [1.0.0-beta8] - 2024-09-25

### Changed

- Bump `hectororm/connection` version to 1.0.0-beta7

## [1.0.0-beta7] - 2024-03-19

### Added

- New property `Schema::$alias` to get schema by its alias

## [1.0.0-beta6] - 2023-07-21

### Changed

- Compatibility SQL ASCII mode
- `Column::getName()` quote alias too

## [1.0.0-beta5] - 2022-09-05

### Changed

- Compatibility with `hectororm/connection` version 1.0.0-beta6 

## [1.0.0-beta4] - 2022-02-19

### Fixed

- Strict dependency of `hectororm/connection` package

## [1.0.0-beta3] - 2021-08-27

### Added

- New method `Column::hasDefault(): bool`

### Fixed

- Signature of `SchemaContainer::count(): int`
- Signature of `Table::count(): int`
- Signature of `Table::getIterator(): ArrayIterator`
- Order of columns fixed
- Tests fixed on MySQL 8
- Empty value with MariaDB
- Comparisons with PHP 8.1

## [1.0.0-beta2] - 2021-07-07

### Added

- `SchemaContainerInterface::hasColumn()` method
- `SchemaContainerInterface::getColumn()` method

### Removed

- Dependency with reflections
- @package attributes from PhpDoc

## [1.0.0-beta1] - 2021-06-02

Initial development.
