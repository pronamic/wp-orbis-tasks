<?php

class Orbis_Tasks_AJAX {
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions
		add_action( 'wp_ajax_' . 'orbis_get_tasks', array( $this, 'get_tasks' ) );
		add_action( 'wp_ajax_' . 'orbis_add_task', array( $this, 'add_task' ) );
		add_action( 'wp_ajax_' . 'orbis_set_task_completed', array( $this, 'set_task_completed' ) );
		add_action( 'wp_ajax_' . 'orbis_set_task_due_at', array( $this, 'set_task_due_at' ) );
	}

	private function get_task( $post = null ) {
		// Post
		$post = get_post( $post );

		// Task
		$task = new stdClass();

		$task->post_id = $post->ID;

		// Due
		$due_at = get_post_meta( $post->ID, '_orbis_task_due_at', true );

		$task->due_at_string = mysql2date( 'c', $due_at );
		$task->days_left     = ceil( ( mysql2date( 'U', $due_at ) - time() ) / ( 3600 * 24 ) );

		$task->text = $post->post_title;
		$task->url  = get_permalink( $post );
		$task->time = (int) get_post_meta( $post->ID, '_orbis_task_seconds', true );
		$task->done = (bool) get_post_meta( $post->ID, '_orbis_task_completed', true );;

		// Assignee
		$assignee_id = (int) get_post_meta( $post->ID, '_orbis_task_assignee_id', true );

		if ( ! empty( $assignee_id ) ) {
			$assignee = new stdClass;
			$assignee->user_id       = $assignee_id;
			$assignee->gravatar_hash = md5( strtolower( trim( get_the_author_meta( 'user_email', $assignee_id ) ) ) );

			$task->assignee = $assignee;
		}

		// Project
		if ( isset( $post->project_post_id ) ) {
			$project_post = get_post( $post->project_post_id );

			$project = new stdClass();
			$project->post_id = $project_post->ID;
			$project->url     = get_permalink( $project_post );
			$project->title   = $project_post->post_title;

			$task->project = $project;
		}

		return $task;
	}

	public function get_tasks() {
		global $post;

		// Tasks
		$tasks = array();

		// Query
		$query = new WP_Query( array(
			'post_type'            => 'orbis_task',
			'posts_per_page'       => -1,
			'orderby'              => 'orbis_task_due_at',
			'order'                => 'ASC',
			'orbis_task_completed' => 'no',
			//'orbis_task_assignee' => get_current_user_id(),
		) );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$tasks[] = $this->get_task( $post );
			}
		}

		wp_send_json( $tasks );
	}

	public function add_task() {
		$object = json_decode( file_get_contents( 'php://input' ), false );

		if ( $object ) {
			$post_id = wp_insert_post( array(
				'post_title'  => $object->text,
				'post_status' => 'publish',
				'post_type'   => 'orbis_task',
			) );

			$orbis_task = new Orbis_Task( $post_id );
			$orbis_task->set_time( $object->time );
			$orbis_task->set_project_id( $object->project_id );
			$orbis_task->set_assignee_id( $object->assignee_id );
			$orbis_task->set_due_at( $object->due_at );

			orbis_save_task_sync( $post_id );

			$task = $this->get_task( $post_id );

			wp_send_json( $task );
		}
	}

	public function set_task_completed() {
		$object = json_decode( file_get_contents( 'php://input' ), false );

		if ( $object ) {
			$post_id = $object->post_id;

			$task = new Orbis_Task( $object->post_id );
			$task->set_completed( $object->done );

			orbis_save_task_sync( $post_id );

			$task = $this->get_task( $post_id );

			wp_send_json( $task );
		}
	}

	public function set_task_due_at() {
		$object = json_decode( file_get_contents( 'php://input' ), false );

		if ( $object ) {
			$post_id = $object->post_id;

			$task = new Orbis_Task( $object->post_id );
			$task->set_due_at( $object->due_at );

			orbis_save_task_sync( $post_id );

			$task = $this->get_task( $post_id );

			wp_send_json( $task );
		}
	}
}
