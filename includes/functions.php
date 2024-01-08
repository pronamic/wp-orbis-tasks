<?php

function orbis_tasks_maybe_finish_task() {
	if ( filter_has_var( INPUT_GET, 'task' ) && filter_has_var( INPUT_GET, 'action' ) ) {
		$post_id = filter_input( INPUT_GET, 'task', FILTER_SANITIZE_NUMBER_INT );
		$action  = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$nonce   = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		$nonce_action = 'finish-task_' . $post_id;

		if ( wp_verify_nonce( $nonce, $nonce_action ) ) {
			global $wpdb;

			update_post_meta( $post_id, '_orbis_task_completed', true );

			orbis_save_task_sync( $post_id, get_post( $post_id ) );

			$url = add_query_arg(
				[
					'task'     => false,
					'action'   => false,
					'_wpnonce' => false,
				] 
			);

			wp_redirect( $url );

			exit;
		} else {
			exit( 'Nonce is invalid' );
		}
	}
}

add_action( 'init', 'orbis_tasks_maybe_finish_task' );

function get_delete_orbis_task_link( $id ) {
	$link = add_query_arg(
		[
			'task'   => $id,
			'action' => 'delete',
		] 
	);

	$link = wp_nonce_url( $link, 'delete-task_' . $id );

	return $link;
}

function get_finish_orbis_task_link( $id ) {
	$link = add_query_arg(
		[
			'task'   => $id,
			'action' => 'finish',
		] 
	);

	$link = wp_nonce_url( $link, 'finish-task_' . $id );

	return $link;
}

function get_edit_orbis_task_link( $id ) {
	$link = add_query_arg(
		[
			'task'   => $id,
			'action' => 'edit',
		] 
	);

	$link = wp_nonce_url( $link, 'edit-task_' . $id );

	return $link;
}


function orbis_task_time() {
	$post_id = get_the_ID();

	$seconds = get_post_meta( $post_id, '_orbis_task_seconds', true );

	echo orbis_time( $seconds );
}

function orbis_task_due_at() {
	$post_id = get_the_ID();

	$date = get_post_meta( $post_id, '_orbis_task_due_at', true );

	echo date_i18n( 'D j M Y', strtotime( $date ) );
}

function orbis_task_project() {
	global $post;

	if ( isset( $post->project_post_id ) ) {
		printf(
			'<a href="%s">%s</a>',
			esc_attr( get_permalink( $post->project_post_id ) ),
			esc_html( get_the_title( $post->project_post_id ) )
		);
	}
}

function orbis_task_assignee() {
	global $post;

	if ( isset( $post->task_assignee_display_name ) ) {
		echo $post->task_assignee_display_name;
	}
}

function orbis_finish_task_link() {
	printf(
		'<a href="%s" class="orbis-confirm"><span class="glyphicon glyphicon-ok"></span> <span style="display: none">%s</span></a>',
		esc_attr( get_finish_orbis_task_link( get_the_ID() ) ),
		esc_html( __( 'Finish', 'orbis-tasks' ) )
	);
}


/**
 * Add domain task meta boxes
 */
function orbis_tasks_add_meta_boxes() {
	add_meta_box(
		'orbis_task_details',
		__( 'Details', 'orbis-tasks' ),
		'orbis_task_details_meta_box',
		'orbis_task',
		'normal',
		'high'
	);
}

add_action( 'add_meta_boxes', 'orbis_tasks_add_meta_boxes' );

/**
 * Subscription details meta box
 *
 * @param array $post
 */
function orbis_task_details_meta_box( $post ) {
	include __DIR__ . '/../admin/meta-box-task-details.php';
}

/**
 * Save task details
 */
function orbis_save_task_details( $post_id, $post ) {
	// Doing autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Verify nonce
	$nonce = filter_input( INPUT_POST, 'orbis_task_details_meta_box_nonce', FILTER_SANITIZE_STRING );
	if ( ! wp_verify_nonce( $nonce, 'orbis_save_task_details' ) ) {
		return;
	}

	// Check permissions
	if ( ! ( 'orbis_task' === $post->post_type && current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	// OK
	$definition = [
		'_orbis_task_project_id'     => FILTER_SANITIZE_STRING,
		'_orbis_task_assignee_id'    => FILTER_SANITIZE_NUMBER_INT,
		'_orbis_task_due_at_string'  => FILTER_SANITIZE_STRING,
		'_orbis_task_seconds_string' => FILTER_SANITIZE_STRING,
		'_orbis_task_completed'      => FILTER_VALIDATE_BOOLEAN,
	];

	$data = filter_input_array( INPUT_POST, $definition );

	update_orbis_task_meta( $post_id, $data );
}

add_action( 'save_post', 'orbis_save_task_details', 10, 2 );

/**
 * Update Orbis task meta data
 *
 * @param array $data
 */
function update_orbis_task_meta( $post_id, array $data = null ) {
	if ( is_array( $data ) ) {
		// Due At
		if ( isset( $data['_orbis_task_due_at_string'] ) ) {
			$date_string = $data['_orbis_task_due_at_string'];

			$timestamp = strtotime( $date_string );

			if ( false !== $timestamp ) {
				$date     = date( 'Y-m-d H:i:s', $timestamp );
				$date_gmt = get_gmt_from_date( $date );

				$data['_orbis_task_due_at']     = $date;
				$data['_orbis_task_due_at_gmt'] = $date_gmt;
			}
		}

		// Seconds
		if ( isset( $data['_orbis_task_seconds_string'] ) ) {
			$data['_orbis_task_seconds'] = orbis_parse_time( $data['_orbis_task_seconds_string'] );
		}

		// Meta
		foreach ( $data as $key => $value ) {
			if ( '' === $value || null === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Sync
		orbis_save_task_sync( $post_id );
	}
}

/**
 * Sync task with Orbis tables
*/
function orbis_save_task_sync( $post_id ) {
	// OK
	global $wpdb;

	// Orbis project ID
	$orbis_id = get_post_meta( $post_id, '_orbis_task_id', true );
	$orbis_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_tasks WHERE post_id = %d;", $post_id ) );

	$project_id  = get_post_meta( $post_id, '_orbis_task_project_id', true );
	$assignee_id = get_post_meta( $post_id, '_orbis_task_assignee_id', true );
	$due_at      = get_post_meta( $post_id, '_orbis_task_due_at', true );
	$completed   = get_post_meta( $post_id, '_orbis_task_completed', true );

	$data = [];
	$form = [];

	$data['task'] = get_the_title( $post_id );
	$form['task'] = '%s';

	$data['completed'] = (bool) $completed;
	$form['completed'] = '%d';

	if ( ! empty( $project_id ) ) {
		$data['project_id'] = $project_id;
		$form['project_id'] = '%d';
	}

	if ( ! empty( $assignee_id ) ) {
		$data['assignee_id'] = $assignee_id;
		$form['assignee_id'] = '%d';
	}

	if ( ! empty( $due_at ) ) {
		$data['due_at'] = $due_at;
		$form['due_at'] = '%s';
	}

	if ( empty( $orbis_id ) ) {
		$data['post_id'] = $post_id;
		$form['post_id'] = '%d';

		$result = $wpdb->insert( $wpdb->orbis_tasks, $data, $form );

		if ( false !== $result ) {
			$orbis_id = $wpdb->insert_id;
		}
	} else {
		$result = $wpdb->update(
			$wpdb->orbis_tasks,
			$data,
			[ 'id' => $orbis_id ],
			$form,
			[ '%d' ]
		);
	}

	update_post_meta( $post_id, '_orbis_task_id', $orbis_id );
}

/**
 * Task edit columns
 */
function orbis_task_edit_columns( $columns ) {
	return [
		'cb'                   => '<input type="checkbox" />',
		'title'                => __( 'Task', 'orbis-tasks' ),
		'orbis_task_project'   => __( 'Project', 'orbis-tasks' ),
		'orbis_task_assignee'  => __( 'Assignee', 'orbis-tasks' ),
		'orbis_task_due_at'    => __( 'Due At', 'orbis-tasks' ),
		'orbis_task_time'      => __( 'Time', 'orbis-tasks' ),
		'orbis_task_completed' => __( 'Completed', 'orbis-tasks' ),
		'author'               => __( 'Author', 'orbis-tasks' ),
		'comments'             => __( 'Comments', 'orbis-tasks' ),
		'date'                 => __( 'Date', 'orbis-tasks' ),
	];
}

add_filter( 'manage_edit-orbis_task_columns', 'orbis_task_edit_columns' );

function orbis_task_sortable_columns( $columns ) {
	$columns['orbis_task_due_at'] = 'orbis_task_due_at';

	return $columns;
}

add_filter( 'manage_edit-orbis_task_sortable_columns', 'orbis_task_sortable_columns' );

/**
 * Project column
 *
 * @param string $column
 */
function orbis_task_column( $column, $post_id ) {
	switch ( $column ) {
		case 'orbis_task_project':
			$id = get_post_meta( $post_id, '_orbis_task_project_id', true );

			global $post;

			if ( isset( $post->project_post_id ) ) {
				$url   = get_permalink( $post->project_post_id );
				$title = get_the_title( $post->project_post_id );

				printf(
					'<a href="%s" target="_blank">%s</a>',
					esc_attr( $url ),
					esc_attr( $title )
				);
			} else {
				echo '&mdash;';
			}

			break;
		case 'orbis_task_assignee':
			orbis_task_assignee();

			break;
		case 'orbis_task_due_at':
			$due_at = get_post_meta( $post_id, '_orbis_task_due_at', true );

			if ( empty( $due_at ) ) {
				echo '&mdash;';
			} else {
				$seconds = strtotime( $due_at );

				$delta = $seconds - time();
				$days  = round( $delta / ( 3600 * 24 ) );

				echo esc_html( $due_at ), '<br />';
				printf( __( '%d days', 'orbis-tasks' ), $days );
			}

			break;
		case 'orbis_task_time':
			$seconds = get_post_meta( $post_id, '_orbis_task_seconds', true );

			if ( empty( $seconds ) ) {
				echo '&mdash;';
			} else {
				echo orbis_time( $seconds );
			}

			break;
		case 'orbis_task_completed':
			$completed = get_post_meta( $post_id, '_orbis_task_completed', true );

			echo $completed ? __( 'Yes', 'orbis-tasks' ) : __( 'No', 'orbis-tasks' );

			break;
	}
}

add_action( 'manage_posts_custom_column', 'orbis_task_column', 10, 2 );


/**
 * Posts clauses
 *
 * http://codex.wordpress.org/WordPress_Query_Vars
 * http://codex.wordpress.org/Custom_Queries
 *
 * @param array $pieces
 * @param WP_Query $query
 * @return string
 */
function orbis_tasks_posts_clauses( $pieces, $query ) {
	global $wpdb;

	$post_type = $query->get( 'post_type' );

	if ( 'orbis_task' === $post_type ) {
		// Fields
		$fields = ',
			project.id AS project_id,
			project.post_id AS project_post_id,
			task.assignee_id AS task_assignee_id,
			assignee.display_name AS task_assignee_display_name
		';

		// Join
		$join = "
			LEFT JOIN
				$wpdb->orbis_tasks AS task
					ON $wpdb->posts.ID = task.post_id
			LEFT JOIN
				$wpdb->orbis_projects AS project
					ON task.project_id = project.id
			LEFT JOIN
				$wpdb->users AS assignee
					ON task.assignee_id = assignee.id
		";

		// Where
		$where = '';

		// Project
		$project = $query->get( 'orbis_task_project' );

		if ( ! empty( $project ) ) {
			$where .= $wpdb->prepare( ' AND project.post_id = %d', $project );
		}

		// Assignee
		$assignee = $query->get( 'orbis_task_assignee' );

		if ( ! empty( $assignee ) ) {
			$where .= $wpdb->prepare( ' AND task.assignee_id = %d', $assignee );
		}

		// Completed
		$completed = $query->get( 'orbis_task_completed' );

		if ( ! empty( $completed ) ) {
			$completed = filter_var( $completed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

			if ( null !== $completed ) {
				$where .= sprintf( ' AND %s task.completed ', $completed ? '' : 'NOT' );
			}
		}

		// Order by
		$orderby = $pieces['orderby'];
		$order   = $query->get( 'order' );

		switch ( $query->get( 'orderby' ) ) {
			case 'orbis_task_due_at':
				$orderby = 'task.due_at ' . $order;

				break;
		}

		// Pieces
		$pieces['join']   .= $join;
		$pieces['fields'] .= $fields;
		$pieces['where']  .= $where;

		$pieces['orderby'] = $orderby;
	}

	return $pieces;
}

add_filter( 'posts_clauses', 'orbis_tasks_posts_clauses', 10, 2 );

/**
 * Defaults
 *
 * @param unknown $query
 */
function orbis_tasks_pre_get_posts( $query ) {
	$post_type = $query->get( 'post_type' );

	if ( 'orbis_task' === $post_type ) {
		// Order
		$orderby = $query->get( 'orderby' );
		$order   = $query->get( 'order' );

		if ( empty( $orderby ) ) {
			//  Default = Due At
			$query->set( 'orderby', 'orbis_task_due_at' );

			if ( empty( $order ) ) {
				if ( is_admin() ) {
					// Default = Descending
					$query->set( 'order', 'DESC' );
				} else {
					// Default = Ascending
					$query->set( 'order', 'ASC' );
				}
			}
		}

		// Completed
		if ( $query->is_post_type_archive( 'orbis_task' ) && ! is_admin() ) {
			$completed = $query->get( 'orbis_task_completed' );

			if ( empty( $completed ) ) {
				//  Default = Not completed
				$query->set( 'orbis_task_completed', 'no' );
			}
		}
	}
}

add_action( 'pre_get_posts', 'orbis_tasks_pre_get_posts' );

function orbis_tasks_query_vars( $query_vars ) {
	$query_vars[] = 'orbis_task_completed';
	$query_vars[] = 'orbis_task_assignee';
	$query_vars[] = 'orbis_task_project';

	return $query_vars;
}

add_filter( 'query_vars', 'orbis_tasks_query_vars' );
