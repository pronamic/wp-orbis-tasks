<?php

// Default filters
add_filter( 'orbis_task_text', 'wptexturize' );
add_filter( 'orbis_task_text', 'convert_chars' );
add_filter( 'orbis_task_text', 'make_clickable', 9 );
add_filter( 'orbis_task_text', 'force_balance_tags', 25 );
add_filter( 'orbis_task_text', 'convert_smilies', 20 );

/**
 * Insert new task into the database
 *
 * @param array $data
 */
function orbis_insert_task( $data = array() ) {
	global $wpdb;

	$person_id = orbis_get_current_person_id();

	$defaults = array(
		'added_by_id'          => $person_id,
		'added_on_date'        => time(),
		'modified_by_id'       => $person_id,
		'modified_on_date'     => time(),
		'assigned_to_id'       => $person_id,
		'task'                 => 'Random text ' . time(),
		'start_date'           => date( 'Y-m-d H:i:s' ),
		'end_date'             => date( 'Y-m-d H:i:s', strtotime( '+7 days' ) ),
		'planned_end_date'     => date( 'Y-m-d H:i:s', strtotime( '+7 days' ) ),
		'planned_number_hours' => 1,
		'planned_duration'     => 3600
	);

	$data = wp_parse_args( $data, $defaults );

	return $wpdb->insert( $wpdb->orbis_tasks, $data );
}

/**
 * Maybe add new task listen to POST values
 */
function orbis_tasks_maybe_new_task() {
	if ( isset( $_POST['orbis_tasks_new_task_nonce'] ) ) {
		$nonce = filter_input( INPUT_POST, 'orbis_tasks_new_task_nonce', FILTER_SANITIZE_STRING );

		if ( wp_verify_nonce( $nonce, 'orbis_tasks_add_new_task' ) ) {
			$description = filter_input( INPUT_POST, 'orbis_task_description', FILTER_UNSAFE_RAW );
			$description = wp_kses_post( $description );

			$assigned_to_id = filter_input( INPUT_POST, 'person_id', FILTER_VALIDATE_INT );

			$project_id = filter_input( INPUT_POST, 'project_id', FILTER_VALIDATE_INT );

			$data = array(
				'task'           => $description,
				'assigned_to_id' => $assigned_to_id,
				'project_id'     => $project_id
			);

			$result = orbis_insert_task( $data );
		}
	}
}

add_action( 'init', 'orbis_tasks_maybe_new_task' );

function orbis_tasks_maybe_delete_task() {
	if ( isset( $_GET['task'] ) && isset( $_GET['action'] ) ) {
		$id     = filter_input( INPUT_GET, 'task', FILTER_SANITIZE_NUMBER_INT );
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$nonce  = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		$nonce_action =  'delete-task_' . $id;

		if ( wp_verify_nonce( $nonce, $nonce_action ) ) {
			global $wpdb;

			$result = $wpdb->delete( 'orbis_tasks', array( 'id' => $id ) );

			if ( $result !== false ) {
				$url = add_query_arg( array(
					'action'   => false,
					'_wpnonce' => false,
					'deleted'  => true
				) );

				wp_redirect( $url );

				exit;
			}
		} else {
			exit( 'Nonce is invalid' );
		}
	}
}

add_action( 'init', 'orbis_tasks_maybe_delete_task' );

function get_delete_orbis_task_link( $id ) {
	$link = add_query_arg( array(
		'task'   => $id,
		'action' => 'delete'
	) );

	$link = wp_nonce_url( $link, 'delete-task_' . $id );

	return $link;
}

function get_finish_orbis_task_link( $id ) {
	$link = add_query_arg( array(
		'task'   => $id,
		'action' => 'finish'
	) );

	$link = wp_nonce_url( $link, 'finish-task_' . $id );

	return $link;
}

function get_edit_orbis_task_link( $id ) {
	$link = add_query_arg( array(
		'task'   => $id,
		'action' => 'edit'
	) );

	$link = wp_nonce_url( $link, 'edit-task_' . $id );

	return $link;
}
