<?php

/**
 * Tasks overview
 *
 * @param unknown $atts
 * @return string
 */
function orbis_tasks_shortcode_tasks( $atts ) {
	global $wpdb;
	global $orbis_tasks_plugin;

	$person_id = orbis_get_current_person_id();

	$query = "
		SELECT
			task.id,
			added_by.first_name AS added_by_name,
			assigned_to.first_name AS assigned_to_name,
			task.task,
			task.planned_duration,
			task.planned_end_date,
			company.name AS company_name,
			project.name AS project_name,
			project.id AS project_post_id
		FROM
			$wpdb->orbis_tasks AS task
				LEFT JOIN
			$wpdb->orbis_projects AS project
					ON project.id = task.project_id
				LEFT JOIN
			$wpdb->orbis_companies AS company
					ON company.id = project.principal_id
				LEFT JOIN
			orbis_persons AS added_by
					ON added_by.id = task.added_by_id
				LEFT JOIN
			orbis_persons AS assigned_to
					ON assigned_to.id = task.assigned_to_id
		WHERE
			task.percentage_completed < 100
				AND
			assigned_to.id = %d
		ORDER BY
			planned_end_date ASC ,
			assigned_to_id ASC
		LIMIT
			20
		;
	";

	$query = $wpdb->prepare( $query, $person_id );

	global $orbis_tasks;

	$orbis_tasks = $wpdb->get_results( $query );

	$return  = '';

	ob_start();

	$orbis_tasks_plugin->plugin_include( 'templates/tasks.php' );

	$return = ob_get_contents();

	ob_end_clean();

	return $return;
}

add_shortcode( 'orbis_tasks', 'orbis_tasks_shortcode_tasks' );

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

	$return  = '';

	ob_start();

	$orbis_tasks_plugin->plugin_include( 'templates/new-task-form.php' );

	$return = ob_get_contents();

	ob_end_clean();

	return $return;
}

add_shortcode( 'orbis_new_task_form', 'orbis_tasks_shortcode_new_task_form' );

/**
 * Tasks init
 */
function orbis_tasks_init() {
	if ( filter_has_var( INPUT_POST,  'orbis_task_add' ) ) {
		$nonce = filter_input( INPUT_POST, 'orbis_tasks_new_task_nonce', FILTER_SANITIZE_STRING );
	
		if ( wp_verify_nonce( $nonce, 'orbis_tasks_add_new_task' ) ) {
			$task_description = filter_input( INPUT_POST, '_orbis_task_description', FILTER_SANITIZE_STRING );

			$result = wp_insert_post( array(
				'post_type'             => 'orbis_task',
				'post_status'           => 'publish',
				'post_title'            => $task_description,
			), true );
	
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

add_action( 'init', 'orbis_tasks_init' );
