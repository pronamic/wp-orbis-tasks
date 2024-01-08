<?php

namespace Pronamic\Orbis\Tasks;

class Task {
	private $post;

	public function __construct( $post ) {
		$this->post = get_post( $post );
	}

	public function set_project_id( $project_id ) {
		update_post_meta( $this->post->ID, '_orbis_task_project_id', $project_id );
	}

	public function set_assignee_id( $assignee_id ) {
		update_post_meta( $this->post->ID, '_orbis_task_assignee_id', $assignee_id );
	}

	public function set_due_at( $date_string ) {
		update_post_meta( $this->post->ID, '_orbis_task_due_at_string', $date_string );

		$timestamp = strtotime( $date_string );

		if ( false !== $timestamp ) {
			$date     = date( 'Y-m-d H:i:s', $timestamp );
			$date_gmt = get_gmt_from_date( $date );

			update_post_meta( $this->post->ID, '_orbis_task_due_at', $date );
			update_post_meta( $this->post->ID, '_orbis_task_due_at_gmt', $date_gmt );
		}
	}

	public function set_time( $time ) {
		update_post_meta( $this->post->ID, '_orbis_task_seconds', $time );
	}

	public function set_completed( $completed ) {
		$completed = (bool) $completed;

		update_post_meta( $this->post->ID, '_orbis_task_completed', $completed );
	}
}
