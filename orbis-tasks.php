<?php
/*
Plugin Name: Orbis Tasks
Plugin URI: http://www.orbiswp.com/
Description: The Orbis Tasks plugin extends your Orbis environment with the option to add tasks and connect them to Orbis projects.

Version: 1.1.1
Requires at least: 3.5

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: orbis_tasks
Domain Path: /languages/

License: Copyright (c) Pronamic

GitHub URI: https://github.com/pronamic/wp-orbis-tasks
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
