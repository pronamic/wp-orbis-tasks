<?php

function orbis_tasks_angular_init() {
	global $orbis_tasks_plugin;

	wp_register_script(
		'orbis-tasks-angular',
		$orbis_tasks_plugin->plugin_url( 'src/orbis-tasks/orbis-tasks.js' ),
		array( 'orbis-angular-app' ),
		'1.0.0',
		true
	);
}

add_action( 'init', 'orbis_tasks_angular_init' );
