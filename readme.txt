=== Orbis Tasks ===
Contributors: pronamic, remcotolsma
Donate link: http://www.orbiswp.com/
Tags: orbis, tasks, task, todo, licence
Requires at least: 3.5
Tested up to: 4.0
Stable tag: 2.0.0
License: Copyright (c) Pronamic
License URI: http://www.pronamic.eu/copyright/



== Description ==



== Installation ==



== Frequently Asked Questions ==



== Screenshots ==



== Changelog ==

= 2.0.0 =
*	Rewritten plugin with namespaced architecture (`Pronamic\Orbis\Tasks`).
*	Added task template post type for recurring tasks.
*	Added task scheduler for automatic task creation from templates.
*	Added end date merge tag support.
*	Added compact period fields in admin.
*	Improved handling of empty meta values.
*	Increased minimum PHP requirement to 8.0.
*	Increased minimum WordPress requirement to 6.2.
*	Removed AngularJS frontend code.
*	Removed AJAX controller.
*	Removed tasks widget.
*	Removed legacy templates (`tasks.php`, `new-task-form.php`).
*	Added new templates (`task-details.php`, `task-template-details.php`).
*	Added Composer autoloading via `automattic/jetpack-autoloader` (v3.0.2) — Changelog edits. [Release](https://github.com/Automattic/jetpack-autoloader/releases/tag/v3.0.2)
*	Added `woocommerce/action-scheduler` (v3.7.1) — WP 6.4 compatibility and semver security fix. [Release](https://github.com/woocommerce/action-scheduler/releases/tag/3.7.1)
*	Added `pronamic/wp-datetime` (v2.1.7) — Updated `.gitattributes`. [Release](https://github.com/pronamic/wp-datetime/releases/tag/v2.1.7)

= 1.1.1 =
*	Bug fixes on updating an task.

= 1.1.0 =
*	Added support for AngularJS.

= 1.0.2 =
*	Tweak - Switched from custom menu icons to the new WordPress [Dashicons](http://melchoyce.github.io/dashicons/).

= 1.0.1 =
*	Tweak - Updated Bootstrap support from v2.1.1 to v3.0.3.

= 1.0.0 =
*	Initial release


== Developers ==

*	php ~/wp/svn/i18n-tools/makepot.php wp-plugin ~/wp/git/orbis-tasks ~/wp/git/orbis-tasks/languages/orbis_tasks.pot

