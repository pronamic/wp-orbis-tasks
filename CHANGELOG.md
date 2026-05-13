# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-05-13

### Added

- Task template post type for recurring tasks.
- Task scheduler for automatic task creation from templates.
- End date merge tag support.
- Compact period fields in admin.
- New templates (`task-details.php`, `task-template-details.php`).
- Composer autoloading via `automattic/jetpack-autoloader` (v3.0.2) — Changelog edits. [Release](https://github.com/Automattic/jetpack-autoloader/releases/tag/v3.0.2)
- `woocommerce/action-scheduler` (v3.7.1) — WP 6.4 compatibility and semver security fix. [Release](https://github.com/woocommerce/action-scheduler/releases/tag/3.7.1)
- `pronamic/wp-datetime` (v2.1.7) — Updated `.gitattributes`. [Release](https://github.com/pronamic/wp-datetime/releases/tag/v2.1.7)

### Changed

- Rewritten plugin with namespaced architecture (`Pronamic\Orbis\Tasks`).
- Improved handling of empty meta values.
- Increased minimum PHP requirement to 8.0.
- Increased minimum WordPress requirement to 6.2.

### Removed

- AngularJS frontend code.
- AJAX controller.
- Tasks widget.
- Legacy templates (`tasks.php`, `new-task-form.php`).

## [1.1.1] - 2014-11-07

### Fixed

- Bug fixes on updating a task.

## [1.1.0] - 2014-11-03

### Added

- Support for AngularJS.

## [1.0.2] - 2013-12-19

### Changed

- Switched from custom menu icons to WordPress Dashicons.

## [1.0.1] - 2013-12-02

### Changed

- Updated Bootstrap support from v2.1.1 to v3.0.3.

## [1.0.0] - 2013-08-26

### Added

- Initial release.

[Unreleased]: https://github.com/pronamic/wp-orbis-tasks/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/pronamic/wp-orbis-tasks/compare/1.1.1...v2.0.0
[1.1.1]: https://github.com/pronamic/wp-orbis-tasks/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/pronamic/wp-orbis-tasks/releases/tag/1.1.0
[1.0.2]: https://github.com/pronamic/wp-orbis-tasks/releases/tag/1.0.2
[1.0.1]: https://github.com/pronamic/wp-orbis-tasks/releases/tag/1.0.1
[1.0.0]: https://github.com/pronamic/wp-orbis-tasks/releases/tag/1.0.0
