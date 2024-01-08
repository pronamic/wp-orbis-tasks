<?php
/**
 * Orbis Tasks
 *
 * @package   Pronamic\Orbis\Tasks
 * @author    Pronamic
 * @copyright 2024 Pronamic
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Orbis Tasks
 * Plugin URI:        https://wp.pronamic.directory/plugins/orbis-tasks/
 * Description:       The Orbis Tasks plugin extends your Orbis environment with the option to add tasks and connect them to Orbis projects.
 * Version:           1.1.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Pronamic
 * Author URI:        https://www.pronamic.eu/
 * Text Domain:       orbis-tasks
 * Domain Path:       /languages/
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://wp.pronamic.directory/plugins/orbis-tasks/
 * GitHub URI:        https://github.com/pronamic/wp-orbis-tasks
 */

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload_packages.php';

/**
 * Bootstrap.
 */
function orbis_tasks_bootstrap() {
	// Classes
	require_once 'classes/orbis-task.php';
	require_once 'classes/orbis-tasks-ajax.php';
	require_once 'classes/orbis-tasks-plugin.php';

	// Initialize
	global $orbis_tasks_plugin;

	$orbis_tasks_plugin = new Orbis_Tasks_Plugin( __FILE__ );
}

add_action( 'orbis_bootstrap', 'orbis_tasks_bootstrap' );
