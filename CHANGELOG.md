# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning] (http://semver.org/). For change log format,
use [Keep a Changelog] (http://keepachangelog.com/).

## [1.0.0-beta3] - In progress

### Fixed

- Signature of `SchemaContainer::count(): int`
- Signature of `Table::count(): int`
- Signature of `Table::getIterator(): ArrayIterator`
- Order of columns fixed
- Tests fixed on MySQL 8

## [1.0.0-beta2] - 2021-07-07

### Added

- `SchemaContainerInterface::hasColumn()` method
- `SchemaContainerInterface::getColumn()` method

### Removed

- Dependency with reflections
- @package attributes from PhpDoc

## [1.0.0-beta1] - 2021-06-02

Initial development.
