<?php

function orbis_tasks_create_initial_post_types() {
	global $orbis_tasks_plugin;

	register_post_type(
		'orbis_task',
		array(
			'label'           => __( 'Tasks', 'orbis_tasks' ),
			'labels'          => array(
				'name'               => __( 'Tasks', 'orbis_tasks' ),
				'singular_name'      => __( 'Task', 'orbis_tasks' ),
				'add_new'            => _x( 'Add New', 'orbis_task', 'orbis_tasks' ),
				'add_new_item'       => __( 'Add New Task', 'orbis_tasks' ),
				'edit_item'          => __( 'Edit Task', 'orbis_tasks' ),
				'new_item'           => __( 'New Task', 'orbis_tasks' ),
				'all_items'          => __( 'All Tasks', 'orbis_tasks' ),
				'view_item'          => __( 'View Task', 'orbis_tasks' ),
				'search_items'       => __( 'Search Tasks', 'orbis_tasks' ),
				'not_found'          => __( 'No tasks found.', 'orbis_tasks' ),
				'not_found_in_trash' => __( 'No tasks found in Trash.', 'orbis_tasks' ),
				'parent_item_colon'  => __( 'Parent Task:', 'orbis_tasks' ),
				'menu_name'          => __( 'Tasks', 'orbis_tasks' ),
			),
			'public'          => true,
			'menu_position'   => 30,
			'menu_icon'       => 'dashicons-list-view',
			// 'capability_type' => 'orbis_task',
			'supports'        => array( 'title', 'editor', 'author', 'comments' ),
			'has_archive'     => true,
			'rewrite'         => array(
				'slug' => _x( 'tasks', 'slug', 'orbis_tasks' ),
			),
		)
	);
}

add_action( 'init', 'orbis_tasks_create_initial_post_types', 0 ); // highest priority

/**
 * Add domain task meta boxes
 */
function orbis_tasks_add_meta_boxes() {
	add_meta_box(
		'orbis_task_details',
		__( 'Details', 'orbis_tasks' ),
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
	global $orbis_tasks_plugin;

	$orbis_tasks_plugin->plugin_include( 'admin/meta-box-task-details.php' );
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
	if ( ! ( $post->post_type == 'orbis_task' && current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	// OK
	$definition = array(
		'_orbis_task_project_id'     => FILTER_SANITIZE_STRING,
		'_orbis_task_assignee_id'    => FILTER_SANITIZE_NUMBER_INT,
		'_orbis_task_due_at_string'  => FILTER_SANITIZE_STRING,
		'_orbis_task_seconds_string' => FILTER_SANITIZE_STRING,
		'_orbis_task_completed'      => FILTER_VALIDATE_BOOLEAN,
	);

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

			if ( $timestamp !== false ) {
				$date     = date( 'Y-m-d H:i:s', $timestamp );
				$date_gmt = get_gmt_from_date( $date );

				$data['_orbis_task_due_at']        = $date;
				$data['_orbis_task_due_at_gmt']    = $date_gmt;
			}
		}

		// Seconds
		if ( isset( $data['_orbis_task_seconds_string'] ) ) {
			$data['_orbis_task_seconds'] = orbis_parse_time( $data['_orbis_task_seconds_string'] );
		}

		// Meta
		foreach ( $data as $key => $value ) {
			if ( $value === '' || $value === null ) {
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
	$orbis_id       = get_post_meta( $post_id, '_orbis_task_id', true );
	$orbis_id       = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_tasks WHERE post_id = %d;", $post_id ) );

	$project_id  = get_post_meta( $post_id, '_orbis_task_project_id', true );
	$assignee_id = get_post_meta( $post_id, '_orbis_task_assignee_id', true );
	$due_at      = get_post_meta( $post_id, '_orbis_task_due_at', true );
	$completed   = get_post_meta( $post_id, '_orbis_task_completed', true );

	$data = array();
	$form = array();
	
	$data['task'] = get_the_title( $post_id );
	$form['task'] = '%s';

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

	if ( ! empty( $completed ) ) {
		$data['completed'] = $completed;
		$form['completed'] = '%d';
	}

	if ( empty( $orbis_id ) ) {
		$data['post_id'] = $post_id;
		$form['post_id'] = '%d';
	
		$result = $wpdb->insert( $wpdb->orbis_tasks, $data, $form );
	
		if ( $result !== false ) {
			$orbis_id = $wpdb->insert_id;
		}
	} else {
		$result = $wpdb->update(
			$wpdb->orbis_tasks,
			$data,
			array( 'id' => $orbis_id ),
			$form,
			array( '%d' )
		);
	}
	
	update_post_meta( $post_id, '_orbis_task_id', $orbis_id );
}

/**
 * Task edit columns
 */
function orbis_task_edit_columns( $columns ) {
	return array(
        'cb'                   => '<input type="checkbox" />',
        'title'                => __( 'Task', 'orbis_tasks' ),
        'orbis_task_project'   => __( 'Project', 'orbis_tasks' ),
		'orbis_task_assignee'  => __( 'Assignee', 'orbis_tasks' ),
		'orbis_task_due_at'    => __( 'Due At', 'orbis_tasks' ),
		'orbis_task_time'      => __( 'Time', 'orbis_tasks' ),
		'orbis_task_completed' => __( 'Completed', 'orbis_tasks' ),
		'author'               => __( 'Author', 'orbis_tasks' ),
		'comments'             => __( 'Comments', 'orbis_tasks' ),
        'date'                 => __( 'Date', 'orbis_tasks' ),
	);
}

add_filter( 'manage_edit-orbis_task_columns' , 'orbis_task_edit_columns' );

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
			$due_at  = get_post_meta( $post_id, '_orbis_task_due_at', true );
			
			if ( empty( $due_at ) ) {
				echo '&mdash;';
			} else {
				$seconds = strtotime( $due_at );
	
				$delta   = $seconds - time();
				$days    = round( $delta / ( 3600 * 24 ) );
	
				echo $due_at, '<br />';
				printf( __( '%d days', 'orbis_tasks' ), $days );
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

			echo $completed ? __( 'Yes', 'orbis_tasks' ) : __( 'No', 'orbis_tasks' );

			break;
	}
}

add_action( 'manage_posts_custom_column' , 'orbis_task_column', 10, 2 );


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

	if ( 'orbis_task' == $post_type ) {
		// Fields
		$fields = ",
			project.id AS project_id,
			project.post_id AS project_post_id,
			task.assignee_id AS task_assignee_id,
			assignee.display_name AS task_assignee_display_name
		";

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

			if ( $completed !== null ) {
				$where .= sprintf( ' AND %s task.completed ', $completed ? '' : 'NOT' );
			}
		}

		// Order by
		$orderby = $pieces['orderby'];
		$order   = $query->get( 'order' );

		switch( $query->get( 'orderby' ) ) {
			case 'orbis_task_due_at':
				$orderby = 'task.due_at ' . $order;
				
				break;
		}

		// Pieces
		$pieces['join']    .= $join;
		$pieces['fields']  .= $fields;
		$pieces['where']   .= $where;

		$pieces['orderby'] = $orderby;
	}

	return $pieces;
}

add_filter( 'posts_clauses', 'orbis_tasks_posts_clauses', 10, 2 );

/**
 * Defaults
 * @param unknown $query
 */
function orbis_tasks_pre_get_posts( $query ) {
	$post_type = $query->get( 'post_type' );

	if ( 'orbis_task' == $post_type ) {
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
