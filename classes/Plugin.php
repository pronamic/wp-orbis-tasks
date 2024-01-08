<?php

namespace Pronamic\Orbis\Tasks;

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
	 * Construct.
	 */
	public function __construct() {
		include __DIR__ . '/../includes/functions.php';
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );
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

		\register_post_type(
			'orbis_task',
			[
				'label'         => \__( 'Tasks', 'orbis-tasks' ),
				'labels'        => [
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
				'public'        => true,
				'menu_position' => 30,
				'menu_icon'     => 'dashicons-list-view',
				'supports'      => [
					'title',
					'editor',
					'author',
					'comments',
				],
				'has_archive'   => true,
				'rewrite'       => [
					'slug' => \_x( 'tasks', 'slug', 'orbis-tasks' ),
				],
			]
		);
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
}
