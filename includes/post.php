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
			'menu_icon'       => $orbis_tasks_plugin->plugin_url( 'images/task.png' ),
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
		'_orbis_task_project_id'  => FILTER_SANITIZE_STRING,
		'_orbis_task_assignee_id' => FILTER_SANITIZE_NUMBER_INT,
		'_orbis_task_due_at'      => FILTER_SANITIZE_STRING,
		'_orbis_task_completed'   => FILTER_VALIDATE_BOOLEAN,
	);

	$data = filter_input_array( INPUT_POST, $definition );

	foreach ( $data as $key => $value ) {
		if ( empty( $value ) ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}

add_action( 'save_post', 'orbis_save_task_details', 10, 2 );

/**
 * Sync task with Orbis tables
*/
function orbis_save_task_sync( $post_id, $post ) {
	// Doing autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
		return;
	}

	// Check post type
	if ( ! ( $post->post_type == 'orbis_task' ) ) {
		return;
	}

	// Revision
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Publish
	if ( $post->post_status != 'publish' ) {
		return;
	}

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
	
	$data['task'] = $post->post_title;
	$form['task'] = '%s';

	$data['project_id'] = $project_id;
	$form['project_id'] = '%d';

	$data['assignee_id'] = $assignee_id;
	$form['assignee_id'] = '%d';

	$data['due_at'] = $due_at;
	$form['due_at'] = '%s';

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

add_action( 'save_post', 'orbis_save_task_sync', 20, 2 );

/**
 * Task edit columns
 */
function orbis_task_edit_columns( $columns ) {
	return array(
        'cb'                  => '<input type="checkbox" />',
        'title'               => __( 'Task', 'orbis_tasks' ),
        'orbis_task_project'  => __( 'Project', 'orbis_tasks' ),
		'orbis_task_assignee' => __( 'Assignee', 'orbis_tasks' ),
		'orbis_task_due_at'   => __( 'Due At', 'orbis_tasks' ),
		'author'              => __( 'Author', 'orbis_tasks' ),
		'comments'            => __( 'Comments', 'orbis_tasks' ),
        'date'                => __( 'Date', 'orbis_tasks' ),
	);
}

add_filter( 'manage_edit-orbis_task_columns' , 'orbis_task_edit_columns' );

/**
 * Project column
 *
 * @param string $column
 */
function orbis_task_column( $column, $post_id ) {
	switch ( $column ) {
		case 'orbis_task_project':
			$id = get_post_meta( $post_id, '_orbis_task_project_id', true );

			if ( ! empty( $id ) ) {
				$url = sprintf( 'http://orbis.pronamic.nl/projecten/details/%s/', $id );

				printf( '<a href="%s" target="_blank">%s</a>', $url, $id );
			} else {
				echo '&mdash;';
			}

			break;
		case 'orbis_task_assignee':
			echo get_post_meta( $post_id, '_orbis_task_assignee_id', true );

			break;
		case 'orbis_task_due_at':
			echo get_post_meta( $post_id, '_orbis_task_due_at', true );

			break;
	}
}

add_action( 'manage_posts_custom_column' , 'orbis_task_column', 10, 2 );

