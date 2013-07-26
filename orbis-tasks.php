<?php
/*
Plugin Name: Orbis Tasks
Plugin URI: http://orbiswp.com/
Description:

Version: 0.1.0
Requires at least: 3.5

Author: Pronamic
Author URI: http://pronamic.eu/

Text Domain: orbis_tasks
Domain Path: /languages/

License: GPL

GitHub URI: https://github.com/pronamic/wp-orbis-tasks
*/

function orbis_tasks_bootstrap() {
	// Classes
	require_once 'classes/orbis-tasks-plugin.php';

	// Initialize
	global $orbis_tasks_plugin;

	$orbis_tasks_plugin = new Orbis_Tasks_Plugin( __FILE__ );
}

add_action( 'orbis_bootstrap', 'orbis_tasks_bootstrap' );
