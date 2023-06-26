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

			$url = add_query_arg( array(
				'task'     => false,
				'action'   => false,
				'_wpnonce' => false,
			) );

			wp_redirect( $url );

			exit;
		} else {
			exit( 'Nonce is invalid' );
		}
	}
}

add_action( 'init', 'orbis_tasks_maybe_finish_task' );

function get_delete_orbis_task_link( $id ) {
	$link = add_query_arg( array(
		'task'   => $id,
		'action' => 'delete',
	) );

	$link = wp_nonce_url( $link, 'delete-task_' . $id );

	return $link;
}

function get_finish_orbis_task_link( $id ) {
	$link = add_query_arg( array(
		'task'   => $id,
		'action' => 'finish',
	) );

	$link = wp_nonce_url( $link, 'finish-task_' . $id );

	return $link;
}

function get_edit_orbis_task_link( $id ) {
	$link = add_query_arg( array(
		'task'   => $id,
		'action' => 'edit',
	) );

	$link = wp_nonce_url( $link, 'edit-task_' . $id );

	return $link;
}
