<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

use DateTimeImmutable;
use WP_Comment;
use WP_Error;
use WP_Post;
use WP_Query;

/**
 * Plugin class
 */
class Plugin {
	/**
	 * Instance.
	 * 
	 * @var self
	 */
	private static $instance;

	/**
	 * Instance.
	 * 
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		\add_action( 'p2p_init', [ $this, 'p2p_init' ] );

		\add_filter( 'query_vars', [ $this, 'query_vars' ] );
		\add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
		\add_filter( 'posts_clauses', [ $this, 'task_posts_clauses' ], 10, 2 );

		// Task.
		$post_type = 'orbis_task';

		\add_action( 'save_post_' . $post_type, [ $this, 'save_post_orbis_task' ] );

		\add_filter( 'manage_' . $post_type . '_posts_columns', [ $this, 'task_posts_columns' ] );
		\add_action( 'manage_' . $post_type . '_posts_custom_column', [ $this, 'task_posts_custom_column' ], 10, 2 );

		$screen = 'edit-' . $post_type;

		\add_filter( 'manage_' . $screen . '_sortable_columns', [ $this, 'task_sortable_columns' ] );

		// Task template.
		$post_type = 'orbis_task_template';

		\add_action( 'save_post_' . $post_type, [ $this, 'save_post_orbis_task_template' ] );

		\add_filter( 'manage_' . $post_type . '_posts_columns', [ $this, 'task_template_posts_columns' ] );
		\add_action( 'manage_' . $post_type . '_posts_custom_column', [ $this, 'task_template_posts_custom_column' ], 10, 2 );

		// Scheduler.
		$scheduler = new TaskScheduler( $this );
		$scheduler->setup();

		// Templates.
		\add_action( 'orbis_before_side_content', [ $this, 'template_side_content' ] );

		\add_filter( 'comment_id_fields', [ $this, 'comment_id_fields' ], 10, 2 );
		\add_action( 'comment_post', [ $this, 'comment_post' ], 50, 2 );
		\add_filter( 'comment_text', [ $this, 'comment_text' ], 20, 2 );
	}

	/**
	 * Initialize.
	 * 
	 * @return void
	 */
	public function init() {
		global $wpdb;

		$wpdb->orbis_tasks = $wpdb->prefix . 'orbis_tasks';

		$version = '1.1.0';

		if ( \get_option( 'orbis_tasks_db_version' ) !== $version ) {
			$this->install();

			\update_option( 'orbis_tasks_db_version', $version );
		}

		/**
		 * Post types.
		 * 
		 * @link https://github.com/WordPress/WordPress/blob/6.4/wp-includes/class-wp-post-type.php#L950-L1005
		 */
		\register_post_type(
			'orbis_task',
			[
				'label'                => \__( 'Tasks', 'orbis-tasks' ),
				'labels'               => [
					'name'               => \__( 'Tasks', 'orbis-tasks' ),
					'singular_name'      => \__( 'Task', 'orbis-tasks' ),
					'add_new'            => \_x( 'Add New', 'orbis_task', 'orbis-tasks' ),
					'add_new_item'       => \__( 'Add New Task', 'orbis-tasks' ),
					'edit_item'          => \__( 'Edit Task', 'orbis-tasks' ),
					'new_item'           => \__( 'New Task', 'orbis-tasks' ),
					'all_items'          => \__( 'All Tasks', 'orbis-tasks' ),
					'view_item'          => \__( 'View Task', 'orbis-tasks' ),
					'search_items'       => \__( 'Search Tasks', 'orbis-tasks' ),
					'not_found'          => \__( 'No tasks found.', 'orbis-tasks' ),
					'not_found_in_trash' => \__( 'No tasks found in Trash.', 'orbis-tasks' ),
					'parent_item_colon'  => \__( 'Parent Task:', 'orbis-tasks' ),
					'menu_name'          => \__( 'Tasks', 'orbis-tasks' ),
				],
				'public'               => true,
				'menu_position'        => 30,
				'menu_icon'            => 'dashicons-list-view',
				'supports'             => [
					'title',
					'editor',
					'comments',
					'revisions',
					'author',
				],
				'register_meta_box_cb' => function () {
					\add_meta_box(
						'orbis_task_details',
						\__( 'Task details', 'orbis-tasks' ),
						[ $this, 'meta_box_task_details' ],
						'orbis_task',
						'normal',
						'high'
					);
				},
				'has_archive'          => true,
				'rewrite'              => [
					'slug' => \_x( 'tasks', 'slug', 'orbis-tasks' ),
				],
				'show_in_rest'         => true,
			]
		);

		\register_post_type(
			'orbis_task_template',
			[
				'label'                => \__( 'Task Templates', 'orbis-tasks' ),
				'labels'               => [
					'name'                     => \__( 'Task Templates', 'orbis-tasks' ),
					'singular_name'            => \__( 'Task Template', 'orbis-tasks' ),
					'add_new'                  => \__( 'Add New Task Template', 'orbis-tasks' ),
					'add_new_item'             => \__( 'Add New Task Template', 'orbis-tasks' ),
					'edit_item'                => \__( 'Edit Task Template', 'orbis-tasks' ),
					'new_item'                 => \__( 'New Task Template', 'orbis-tasks' ),
					'view_item'                => \__( 'View Task Template', 'orbis-tasks' ),
					'view_items'               => \__( 'View Task Templates', 'orbis-tasks' ),
					'search_items'             => \__( 'Search Task Templates', 'orbis-tasks' ),
					'not_found'                => \__( 'No task templates found.', 'orbis-tasks' ),
					'not_found_in_trash'       => \__( 'No task templates found in Trash.', 'orbis-tasks' ),
					'parent_item_colon'        => \__( 'Parent Task Template:', 'orbis-tasks' ),
					'all_items'                => \__( 'All Task Templates', 'orbis-tasks' ),
					'archives'                 => \__( 'Task Template Archives', 'orbis-tasks' ),
					'attributes'               => \__( 'Task Template Attributes', 'orbis-tasks' ),
					'insert_into_item'         => \__( 'Insert into task template', 'orbis-tasks' ),
					'uploaded_to_this_item'    => \__( 'Uploaded to this task template', 'orbis-tasks' ),
					'featured_image'           => \__( 'Featured image', 'orbis-tasks' ),
					'set_featured_image'       => \__( 'Set featured image', 'orbis-tasks' ),
					'remove_featured_image'    => \__( 'Remove featured image', 'orbis-tasks' ),
					'use_featured_image'       => \__( 'Use as featured image', 'orbis-tasks' ),
					'filter_items_list'        => \__( 'Filter task templates list', 'orbis-tasks' ),
					'filter_by_date'           => \__( 'Filter by date', 'orbis-tasks' ),
					'items_list_navigation'    => \__( 'Task templates list navigation', 'orbis-tasks' ),
					'items_list'               => \__( 'Task templates list', 'orbis-tasks' ),
					'item_published'           => \__( 'Task template published.', 'orbis-tasks' ),
					'item_published_privately' => \__( 'Task template published privately.', 'orbis-tasks' ),
					'item_reverted_to_draft'   => \__( 'Task template reverted to draft.', 'orbis-tasks' ),
					'item_trashed'             => \__( 'Task template trashed.', 'orbis-tasks' ),
					'item_scheduled'           => \__( 'Task template scheduled.', 'orbis-tasks' ),
					'item_updated'             => \__( 'Task template updated.', 'orbis-tasks' ),
					'item_link'                => \__( 'Task Template Link.', 'orbis-tasks' ),
					'item_link_description'    => \__( 'A link to a task template.', 'orbis-tasks' ),
					'menu_name'                => \__( 'Task Templates', 'orbis-tasks' ),
				],
				'public'               => true,
				'menu_position'        => 30,
				'menu_icon'            => 'dashicons-clipboard',
				'supports'             => [
					'title',
					'editor',
					'comments',
					'revisions',
					'author',
				],
				'register_meta_box_cb' => function () {
					\add_meta_box(
						'orbis_task_template_details',
						\__( 'Task template details', 'orbis-tasks' ),
						[ $this, 'meta_box_task_template_details' ],
						'orbis_task_template',
						'normal',
						'high'
					);
				},
				'has_archive'          => true,
				'rewrite'              => [
					'slug' => \_x( 'task-templates', 'slug', 'orbis-tasks' ),
				],
				'show_in_rest'         => true,
			]
		);
	}

	/**
	 * Posts 2 Posts init.
	 * 
	 * @link https://github.com/scribu/wp-posts-to-posts/wiki/Basic-usage
	 * @return void
	 */
	public function p2p_init() {
		\p2p_register_connection_type(
			[
				'name'         => 'orbis_tasks_to_users',
				'from'         => 'orbis_task',
				'to'           => 'user',
				'admin_column' => 'from',
				'title'        => [
					'from' => \__( 'Assignees', 'orbis-tasks' ),
					'to'   => \__( 'Tasks', 'orbis-tasks' ),
				],
				'from_labels'  => [
					'singular_name' => \__( 'Task', 'orbis-tasks' ),
					'search_items'  => \__( 'Search tasks', 'orbis-tasks' ),
					'not_found'     => \__( 'No tasks found.', 'orbis-tasks' ),
					'create'        => \__( 'Assign task', 'orbis-tasks' ),
				],
				'to_labels'    => [
					'singular_name' => \__( 'User', 'orbis-tasks' ),
					'search_items'  => \__( 'Search users', 'orbis-tasks' ),
					'not_found'     => \__( 'No users found.', 'orbis-tasks' ),
					'create'        => \__( 'Assign user', 'orbis-tasks' ),
				],
			]
		);

		if ( \post_type_exists( 'orbis_project' ) ) {
			\p2p_register_connection_type(
				[
					'name'         => 'orbis_tasks_to_projects',
					'from'         => 'orbis_task',
					'to'           => 'orbis_project',
					'admin_column' => 'from',
					'title'        => [
						'from' => \__( 'Projects', 'orbis-tasks' ),
						'to'   => \__( 'Tasks', 'orbis-tasks' ),
					],
					'from_labels'  => [
						'singular_name' => \__( 'Task', 'orbis-tasks' ),
						'search_items'  => \__( 'Search tasks', 'orbis-tasks' ),
						'not_found'     => \__( 'No tasks found.', 'orbis-tasks' ),
						'create'        => \__( 'Connect task', 'orbis-tasks' ),
					],
					'to_labels'    => [
						'singular_name' => \__( 'Project', 'orbis-tasks' ),
						'search_items'  => \__( 'Search projects', 'orbis-tasks' ),
						'not_found'     => \__( 'No projects found.', 'orbis-tasks' ),
						'create'        => \__( 'Connect project', 'orbis-tasks' ),
					],
				]
			);
		}
	}

	/**
	 * Install.
	 * 
	 * @return void
	 */
	public function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE $wpdb->orbis_tasks (
				id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) UNSIGNED DEFAULT NULL,
				project_id BIGINT(16) UNSIGNED DEFAULT NULL,
				assignee_id BIGINT(20) UNSIGNED DEFAULT NULL,
				task TEXT,
				due_at DATETIME DEFAULT NULL,
				completed BOOLEAN NOT NULL DEFAULT FALSE,
				PRIMARY KEY  (id)
			) $charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		\dbDelta( $sql );

		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_tasks );
	}

	/**
	 * Meta box task details.
	 * 
	 * @param WP_Post $post Post.
	 * @return void
	 */
	public function meta_box_task_details( $post ) {
		\wp_nonce_field( 'orbis_save_task_details', 'orbis_tasks_nonce' );

		$task = Task::from_post( $post );

		include __DIR__ . '/../admin/meta-box-task-details.php';
	}

	/**
	 * Meta box task template details.
	 * 
	 * @param WP_Post $post Post.
	 * @return void
	 */
	public function meta_box_task_template_details( $post ) {
		\wp_nonce_field( 'orbis_save_task_template_details', 'orbis_tasks_nonce' );

		$task_template = TaskTemplate::from_post( $post );

		include __DIR__ . '/../admin/meta-box-task-template-details.php';
	}

	/**
	 * Save Orbis task post.
	 * 
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_post_orbis_task( $post_id ) {
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! \array_key_exists( 'orbis_tasks_nonce', $_POST ) ) {
			return;
		}

		$nonce = \sanitize_key( $_POST['orbis_tasks_nonce'] );

		if ( ! \wp_verify_nonce( $nonce, 'orbis_save_task_details' ) ) {
			return;
		}

		$project_id  = \array_key_exists( '_orbis_task_project_id', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_project_id'] ) ) : '';
		$assignee_id = \array_key_exists( '_orbis_task_assignee_id', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_assignee_id'] ) ) : '';
		$due_date    = \array_key_exists( '_orbis_task_due_date', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_due_date'] ) ) : '';
		$start_date  = \array_key_exists( '_orbis_task_start_date', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_start_date'] ) ) : '';
		$end_date    = \array_key_exists( '_orbis_task_end_date', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_end_date'] ) ) : '';
		$seconds     = \array_key_exists( '_orbis_task_seconds_string', $_POST ) ? \orbis_parse_time( \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_seconds_string'] ) ) ) : '';
		$completed   = \array_key_exists( '_orbis_task_completed', $_POST ) ? '1' === \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_completed'] ) ) : false;

		$task = Task::from_post( \get_post( $post_id ) );

		$task->project_id  = ( '' === $project_id ) ? null : $project_id;
		$task->assignee_id = ( '' === $assignee_id ) ? null : $assignee_id;
		$task->due_date    = ( '' === $due_date ) ? null : DateTimeImmutable::createFromFormat( 'Y-m-d', $due_date, \wp_timezone() )->setTime( 0, 0 );
		$task->start_date  = ( '' === $start_date ) ? null : DateTimeImmutable::createFromFormat( 'Y-m-d', $start_date, \wp_timezone() )->setTime( 0, 0 );
		$task->end_date    = ( '' === $end_date ) ? null : DateTimeImmutable::createFromFormat( 'Y-m-d', $end_date, \wp_timezone() )->setTime( 0, 0 );
		$task->seconds     = ( '' === $seconds ) ? null : $seconds;
		$task->completed   = $completed;

		$this->save_task( $task );
	}

	/**
	 * Save Orbis task template post.
	 * 
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_post_orbis_task_template( $post_id ) {
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! \array_key_exists( 'orbis_tasks_nonce', $_POST ) ) {
			return;
		}

		$nonce = \sanitize_key( $_POST['orbis_tasks_nonce'] );

		if ( ! \wp_verify_nonce( $nonce, 'orbis_save_task_template_details' ) ) {
			return;
		}

		$assignee_id            = \array_key_exists( '_orbis_task_template_assignee_id', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_template_assignee_id'] ) ) : '';
		$creation_date          = \array_key_exists( '_orbis_task_template_creation_date', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_template_creation_date'] ) ) : '';
		$due_date_modifier      = \array_key_exists( '_orbis_task_template_due_date_modifier', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_template_due_date_modifier'] ) ) : '';
		$start_date_modifier    = \array_key_exists( '_orbis_task_template_start_date_modifier', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_template_start_date_modifier'] ) ) : '';
		$end_date_modifier      = \array_key_exists( '_orbis_task_template_end_date_modifier', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_template_end_date_modifier'] ) ) : '';
		$creation_date_modifier = \array_key_exists( '_orbis_task_template_creation_date_modifier', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_template_creation_date_modifier'] ) ) : '';
		$seconds                = \array_key_exists( '_orbis_task_template_time', $_POST ) ? \orbis_parse_time( \sanitize_text_field( \wp_unslash( $_POST['_orbis_task_template_time'] ) ) ) : '';

		$task_template = TaskTemplate::from_post( \get_post( $post_id ) );

		$task_template->assignee_id            = $assignee_id;
		$task_template->creation_date          = ( '' === $creation_date ) ? null : DateTimeImmutable::createFromFormat( 'Y-m-d', $creation_date, \wp_timezone() )->setTime( 0, 0 );
		$task_template->due_date_modifier      = $due_date_modifier;
		$task_template->start_date_modifier    = $start_date_modifier;
		$task_template->end_date_modifier      = $end_date_modifier;
		$task_template->creation_date_modifier = $creation_date_modifier;
		$task_template->seconds                = ( '' === $seconds ) ? null : $seconds;

		$this->save_task_template( $task_template );
	}

	/**
	 * Task posts columns.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/manage_screen-id_columns/
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public function task_posts_columns( $columns ) {
		$columns = [
			'cb'                   => '<input type="checkbox" />',
			'title'                => \__( 'Task', 'orbis-tasks' ),
			'orbis_task_project'   => \__( 'Project', 'orbis-tasks' ),
			'orbis_task_assignee'  => \__( 'Assignee', 'orbis-tasks' ),
			'orbis_task_due_at'    => \__( 'Due at', 'orbis-tasks' ),
			'orbis_task_time'      => \__( 'Time', 'orbis-tasks' ),
			'orbis_task_completed' => \__( 'Completed', 'orbis-tasks' ),
			'author'               => \__( 'Author', 'orbis-tasks' ),
			'comments'             => \__( 'Comments', 'orbis-tasks' ),
			'date'                 => \__( 'Date', 'orbis-tasks' ),
		];

		return $columns;
	}

	/**
	 * Task posts custom column.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/manage_post-post_type_posts_custom_column/
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 * @return void
	 */
	public function task_posts_custom_column( $column_name, $post_id ) {
		$task_post = get_post( $post_id );

		$task = Task::from_post( $task_post );

		switch ( $column_name ) {
			case 'orbis_task_project':
				$id = get_post_meta( $post_id, '_orbis_task_project_id', true );

				if ( isset( $task_post->project_post_id ) ) {
					$url   = get_permalink( $task_post->project_post_id );
					$title = get_the_title( $task_post->project_post_id );
	
					printf(
						'<a href="%s" target="_blank">%s</a>',
						esc_attr( $url ),
						esc_attr( $title )
					);
				} else {
					echo '—';
				}
	
				break;
			case 'orbis_task_assignee':
				if ( property_exists( $task_post, 'task_assignee_display_name' ) ) {
					echo esc_html( $task_post->task_assignee_display_name );
				}
	
				break;
			case 'orbis_task_due_at':
				if ( null === $task->due_date ) {
					echo '—';
				} else {
					$seconds = $task->due_date->getTimestamp();
	
					$delta = $seconds - time();
					$days  = round( $delta / ( 3600 * 24 ) );
	
					echo esc_html( $task->due_date->format( 'd-m-Y' ) ), '<br />';
	
					\printf(
						/* translators: %s: Number of days. */
						\esc_html__( '%d days', 'orbis-tasks' ),
						\esc_html( $days )
					);
				}
	
				break;
			case 'orbis_task_time':
				if ( empty( $task->seconds ) ) {
					echo '—';
				} else {
					echo esc_html( orbis_time( $task->seconds ) );
				}
	
				break;
			case 'orbis_task_completed':
				echo $task->completed ? \esc_html__( 'Yes', 'orbis-tasks' ) : \esc_html__( 'No', 'orbis-tasks' );
	
				break;
		}
	}

	/**
	 * Task sortable columns.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/manage_this-screen-id_sortable_columns/
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public function task_sortable_columns( $columns ) {
		$columns['orbis_task_due_at'] = 'orbis_task_due_at';
	
		return $columns;
	}

	/**
	 * Task template posts columns.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/manage_screen-id_columns/
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public function task_template_posts_columns( $columns ) {
		$columns = [
			'cb'                                => '<input type="checkbox" />',
			'title'                             => \__( 'Task Template', 'orbis-tasks' ),
			'orbis_task_template_assignee'      => \__( 'Assignee', 'orbis-tasks' ),
			'orbis_task_template_creation_date' => \__( 'Creation date', 'orbis-tasks' ),
			'orbis_task_template_time'          => \__( 'Time', 'orbis-tasks' ),
			'author'                            => \__( 'Author', 'orbis-tasks' ),
			'comments'                          => \__( 'Comments', 'orbis-tasks' ),
			'date'                              => \__( 'Date', 'orbis-tasks' ),
		];

		return $columns;
	}

	/**
	 * Task template posts custom column.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/manage_post-post_type_posts_custom_column/
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 * @return void
	 */
	public function task_template_posts_custom_column( $column_name, $post_id ) {
		$task_template_post = get_post( $post_id );

		$task_template = TaskTemplate::from_post( $task_template_post );

		switch ( $column_name ) {
			case 'orbis_task_template_assignee':
				echo \esc_html( null === $task_template->assignee_id ? '—' : \get_user_by( 'ID', $task_template->assignee_id )->display_name );

				break;
			case 'orbis_task_template_creation_date':
				echo \esc_html( null === $task_template->creation_date ? '—' : $task_template->creation_date->format( 'Y-m-d' ) );

				break;
			case 'orbis_task_template_time':
				echo \esc_html( null === $task_template->seconds ? '—' : orbis_time( $task_template->seconds ) );

				break;
		}
	}

	/**
	 * Save task.
	 * 
	 * @param Task $task Task.
	 * @return void
	 * @throws \Exception Throws an exception if the task cannot be saved.
	 */
	public function save_task( Task $task ) {
		if ( null === $task->post_id ) {
			$result = \wp_insert_post(
				[
					'post_title'   => $task->title,
					'post_content' => $task->body,
					'post_status'  => 'publish',
					'post_type'    => 'orbis_task',
				],
				true
			);

			if ( $result instanceof WP_Error ) {
				throw new \Exception( \esc_html( $result->get_error_message() ) );
			}

			$task->post_id = $result;
		}

		$this->save_task_in_custom_table( $task );

		$data = $task->jsonSerialize();

		unset( $data->title );
		unset( $data->body );

		\update_post_meta( $task->post_id, '_orbis_task_json', \wp_slash( \wp_json_encode( $data ) ) );
	}

	/**
	 * Save task template.
	 * 
	 * @param TaskTemplate $task_template Task template.
	 * @return void
	 * @throws \Exception Throws an exception if the task template cannot be saved.
	 */
	public function save_task_template( TaskTemplate $task_template ) {
		if ( null === $task_template->post_id ) {
			throw new \Exception( 'Cannot save task template because post ID is not defined.' );
		}

		$data = $task_template->jsonSerialize();

		unset( $data->title );
		unset( $data->body );

		\update_post_meta( $task_template->post_id, '_orbis_task_template_json', \wp_slash( \wp_json_encode( $data ) ) );

		if ( null === $task_template->creation_date ) {
			\delete_post_meta( $task_template->post_id, '_orbis_task_template_creation_date' );
		}

		if ( null !== $task_template->creation_date ) {
			\update_post_meta( $task_template->post_id, '_orbis_task_template_creation_date', $task_template->creation_date->format( 'Y-m-d' ) );
		}
	}

	/**
	 * Save task in custom table.
	 * 
	 * @param Task $task Task.
	 * @return void
	 * @throws \Exception Throws an exception if the task fails to save.
	 */
	private function save_task_in_custom_table( Task $task ) {
		global $wpdb;

		$orbis_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_tasks WHERE post_id = %d;", $task->post_id ) );

		$data = [];
		$form = [];

		$data['task'] = get_the_title( $task->post_id );
		$form['task'] = '%s';

		$data['completed'] = $task->completed;
		$form['completed'] = '%d';

		$data['project_id'] = $task->project_id;
		$form['project_id'] = '%d';

		$data['assignee_id'] = $task->assignee_id;
		$form['assignee_id'] = '%d';

		$data['due_at'] = ( null === $task->due_date ) ? null : $task->due_date->format( 'Y-m-d' );
		$form['due_at'] = '%s';

		if ( empty( $orbis_id ) ) {
			$data['post_id'] = $task->post_id;
			$form['post_id'] = '%d';

			$result = $wpdb->insert( $wpdb->orbis_tasks, $data, $form );

			if ( false === $result ) {
				throw new \Exception( 'Could not insert task into tasks table: ' . \esc_html( $wpdb->last_error ) );
			}

			$task->id = $wpdb->insert_id;
		} else {
			$result = $wpdb->update(
				$wpdb->orbis_tasks,
				$data,
				[ 'id' => $orbis_id ],
				$form,
				[ '%d' ]
			);

			if ( false === $result ) {
				throw new \Exception( 'Could not update task into tasks table: ' . \esc_html( $wpdb->last_error ) );
			}
		}
	}

	/**
	 * Query vars.
	 * 
	 * @param array<string> $query_vars Query vars.
	 * @return array<string>
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'orbis_task_completed';
		$query_vars[] = 'orbis_task_assignee';
		$query_vars[] = 'orbis_task_project';
	
		return $query_vars;
	}

	/**
	 * Pre get posts.
	 *
	 * @param WP_Query $query WordPress posts query.
	 * @return void
	 */
	public function pre_get_posts( $query ) {
		$post_type = $query->get( 'post_type' );

		if ( 'orbis_task' !== $post_type ) {
			return;
		}

		// phpcs:disable WordPressVIPMinimum.Hooks.PreGetPosts.PreGetPosts -- This function should modify all task queries, not just the main one.

		$orderby = $query->get( 'orderby' );
		$order   = $query->get( 'order' );

		if ( empty( $orderby ) ) {
			$query->set( 'orderby', 'orbis_task_due_at' );

			if ( empty( $order ) ) {
				if ( \is_admin() ) {
					$query->set( 'order', 'DESC' );
				} else {
					$query->set( 'order', 'ASC' );
				}
			}
		}

		if ( $query->is_post_type_archive( 'orbis_task' ) && ! \is_admin() ) {
			$completed = $query->get( 'orbis_task_completed' );

			if ( empty( $completed ) ) {
				$query->set( 'orbis_task_completed', 'no' );
			}
		}

		// phpcs:enable WordPressVIPMinimum.Hooks.PreGetPosts.PreGetPosts
	}

	/**
	 * Posts clauses.
	 *
	 * @link http://codex.wordpress.org/WordPress_Query_Vars
	 * @link http://codex.wordpress.org/Custom_Queries
	 * @param array    $pieces WordPress posts query pieces.
	 * @param WP_Query $query  WordPress posts query.
	 * @return array
	 */
	public function task_posts_clauses( $pieces, $query ) {
		global $wpdb;
	
		$post_type = $query->get( 'post_type' );
	
		if ( 'orbis_task' !== $post_type ) {
			return $pieces;
		}

		$fields = ',
			project.id AS project_id,
			project.post_id AS project_post_id,
			task.assignee_id AS task_assignee_id,
			assignee.display_name AS task_assignee_display_name
		';

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

		$where = '';

		$project = $query->get( 'orbis_task_project' );

		if ( ! empty( $project ) ) {
			$where .= $wpdb->prepare( ' AND project.post_id = %d', $project );
		}

		$assignee = $query->get( 'orbis_task_assignee' );

		if ( ! empty( $assignee ) ) {
			$where .= $wpdb->prepare( ' AND task.assignee_id = %d', $assignee );
		}

		$completed = $query->get( 'orbis_task_completed' );

		if ( ! empty( $completed ) ) {
			$completed = filter_var( $completed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

			if ( null !== $completed ) {
				$where .= sprintf( ' AND %s task.completed ', $completed ? '' : 'NOT' );
			}
		}

		$orderby = $pieces['orderby'];
		$order   = $query->get( 'order' );

		switch ( $query->get( 'orderby' ) ) {
			case 'orbis_task_due_at':
				$orderby = 'task.due_at ' . $order;

				break;
		}
	
		$pieces['join']   .= $join;
		$pieces['fields'] .= $fields;
		$pieces['where']  .= $where;
	
		$pieces['orderby'] = $orderby;
	
		return $pieces;
	}

	/**
	 * Template side content.
	 * 
	 * @return void
	 */
	public function template_side_content() {
		if ( \is_singular( 'orbis_task' ) ) {
			include __DIR__ . '/../templates/task-details.php';
		}

		if ( \is_singular( 'orbis_task_template' ) ) {
			include __DIR__ . '/../templates/task-template-details.php';
		}
	}

	/**
	 * Comment ID fields.
	 * 
	 * @link https://github.com/pronamic/wp-orbis-keychains/blob/0fbaeb7a90141cfafe0a3c0ae65413afd501b12f/includes/post.php#L217-L245
	 * @link https://github.com/WordPress/WordPress/blob/6.4/wp-includes/comment-template.php#L2642-L2651
	 * @param string $fields  Fields.
	 * @param int    $post_id Post ID.
	 * @return string
	 */
	public function comment_id_fields( $fields, $post_id ) {
		if ( 'orbis_task' !== \get_post_type( $post_id ) ) {
			return $fields;
		}

		$task = Task::from_post( \get_post( $post_id ) );

		$fields .= \sprintf(
			'<button name="orbis_tasks_update_task_state" type="submit" class="submit btn btn-secondary" value="%s">%s</button>',
			esc_attr( $task->completed ? 'open' : 'closed' ),
			esc_attr( $task->completed ? \__( 'Reopen task', 'orbis-tasks' ) : \__( 'Close task', 'orbis-tasks' ) )
		);

		return $fields;
	}

	/**
	 * Comment post.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/comment_post/
	 * @link https://github.com/WordPress/wordpress-develop/blob/6.4/src/wp-includes/comment.php#L2310-L2320
	 * @param string     $comment_id       Comment ID.
	 * @param int|string $comment_approved Comment approved.
	 * @return void
	 */
	public function comment_post( $comment_id, $comment_approved ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is handled by WordPress.
		if ( ! \array_key_exists( 'orbis_tasks_update_task_state', $_POST ) ) {
			return;
		}

		if ( 1 !== $comment_approved ) {
			return;
		}

		$comment = \get_comment( $comment_id );

		if ( ! $comment instanceof WP_Comment ) {
			return;
		}

		$comment_post = \get_post( $comment->comment_post_ID );

		if ( 'orbis_task' !== \get_post_type( $comment_post ) ) {
			return;
		}

		$task = Task::from_post( $comment_post );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is handled by WordPress.
		$state = \sanitize_text_field( \wp_unslash( $_POST['orbis_tasks_update_task_state'] ) );

		$task->completed = ( 'closed' === $state );

		$this->save_task( $task );

		\add_comment_meta( $comment_id, '_orbis_task_update_state', $state, true );
	}

	/**
	 * Comment text.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/comment_text/
	 * @link https://github.com/WordPress/wordpress-develop/blob/6.4/src/wp-includes/comment-template.php#L1071-L1082
	 * @param string          $comment_text Text of the comment.
	 * @param WP_Comment|null $comment      The comment object. Null if not found.
	 * @return string
	 */
	public function comment_text( $comment_text, $comment ) {
		if ( null === $comment ) {
			return $comment_text;
		}

		$state = \get_comment_meta( $comment->comment_ID, '_orbis_task_update_state', true );

		if ( '' === $state ) {
			return $comment_text;
		}

		switch ( $state ) {
			case 'open':
				$comment_text .= '<div class="alert alert-secondary" role="alert">' . \sprintf(
					/* translators: %s: Comment author. */
					\__( '%s reopened this task.', 'orbis-tasks' ),
					\esc_html( $comment->comment_author )
				) . '</div>';

				break;
			case 'closed':
				$comment_text .= '<div class="alert alert-secondary" role="alert">' . \sprintf(
					/* translators: %s: Comment author. */
					\__( '%s closed this task.', 'orbis-tasks' ),
					\esc_html( $comment->comment_author )
				) . '</div>';

				break;
		}

		return $comment_text;
	}
}
