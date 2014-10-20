<?php

class Orbis_Tasks_AJAX {
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions
		add_action( 'wp_ajax_' . 'orbis_get_tasks', array( $this, 'get_tasks' ) );
		add_action( 'wp_ajax_' . 'orbis_add_task', array( $this, 'add_task' ) );
	}

	private function get_task( $post = null ) {
		// Post
		$post = get_post( $post );

		// Task
		$task = new stdClass();

		$task->text          = $post->post_title;
		$task->url           = get_permalink( $post );
		$task->due_at        = mysql2date( 'c', get_post_meta( $post->ID, '_orbis_task_due_at', true ) );
		$task->time          = (int) get_post_meta( $post->ID, '_orbis_task_seconds', true );
		$task->done          = false;
		$task->gravatar_hash = md5( strtolower( trim( get_the_author_meta( 'user_email', $post->post_author ) ) ) );

		return $task;
	}

	public function get_tasks() {
		// Tasks
		$tasks = array();

		// Query
		$query = new WP_Query( array(
			'post_type'      => 'orbis_task',
			'posts_per_page' => -1,
		) );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$tasks[] = $this->get_task();
			}
		}

		wp_send_json( $tasks );
	}

	public function add_task() {
		$object = json_decode( file_get_contents( 'php://input' ), false );

		$post_id = wp_insert_post( array(
			'post_title'  => $object->text,
			'post_status' => 'publish',
			'post_type'   => 'orbis_task',
		) );

		$orbis_task = new Orbis_Task( $post_id );
		$orbis_task->set_time( $object->time );
		$orbis_task->set_project_id( $object->project_id );
		$orbis_task->set_due_at( $object->due_at );

		orbis_save_task_sync( $post_id );

		$task = $this->get_task( $post_id );

		wp_send_json( $task );
	}
}
