<?php

/**
 * Shortcode new task form
 *
 * @param array $atts
 * @return string
 */
function orbis_tasks_shortcode_new_task_form( $atts ) {
	wp_enqueue_script( 'orbis-autocomplete' );
	wp_enqueue_style( 'orbis-select2' );

	global $wpdb;
	global $orbis_tasks_plugin;

	$return = '';

	ob_start();

	$orbis_tasks_plugin->plugin_include( 'templates/new-task-form.php' );

	$return = ob_get_contents();

	ob_end_clean();

	return $return;
}

// add_shortcode( 'orbis_new_task_form', 'orbis_tasks_shortcode_new_task_form' );

/**
 * Tasks init
 */
function orbis_tasks_init() {
	if ( filter_has_var( INPUT_POST, 'orbis_task_add' ) ) {
		$nonce = filter_input( INPUT_POST, 'orbis_tasks_new_task_nonce', FILTER_SANITIZE_STRING );

		if ( wp_verify_nonce( $nonce, 'orbis_tasks_add_new_task' ) ) {
			$task_description = filter_input( INPUT_POST, '_orbis_task_description', FILTER_SANITIZE_STRING );

			$result = wp_insert_post(
				[
					'post_type'   => 'orbis_task',
					'post_status' => 'publish',
					'post_title'  => $task_description,
				],
				true 
			);

			if ( is_wp_error( $result ) ) {
				var_dump( $result );
			} else {
				$post_id = $result;

				$url = get_permalink( $post_id );

				wp_redirect( $url );

				exit;
			}
		}
	}
}

// add_action( 'init', 'orbis_tasks_init' );
