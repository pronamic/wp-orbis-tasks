<?php

/**
 * Subscriptions to invoice shortcode
 *
 * @param unknown $atts
 * @return string
 */
function orbis_tasks_shortcode_tasks( $atts ) {
	global $wpdb;
	global $orbis_tasks_plugin;

	$user_id   = get_current_user_id();
	$person_id = null;

	$persons = array(
		 1 =>  null, // pronamic
		 2 =>  6, // remco
		 3 =>  5, // kj
		 4 =>  1, // jelke
		 5 =>  4, // jl
		 6 =>  2, // martijn cordes
		 7 =>  3, // leo
		 8 => 24, // martijn duker
		 9 => 25, // stefan
		10 => 26, // leon
	);

	if ( isset( $persons[$user_id] ) ) {
		$person_id = $persons[$user_id];
	}

	$query = "
		SELECT
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
