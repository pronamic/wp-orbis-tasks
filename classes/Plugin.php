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

		/**
		 * Post types.
		 * 
		 * @link https://github.com/WordPress/WordPress/blob/6.4/wp-includes/class-wp-post-type.php#L950-L1005
		 */
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

		\register_post_type(
			'orbis_task_template',
			[
				'label'         => \__( 'Task Templates', 'orbis-tasks' ),
				'labels'        => [
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
				'public'        => true,
				'menu_position' => 30,
				'menu_icon'     => 'dashicons-clipboard',
				'supports'      => [
					'title',
					'editor',
					'comments',
					'revisions',
					'author',
				],
				'has_archive'   => true,
				'rewrite'       => [
					'slug' => \_x( 'task-templates', 'slug', 'orbis-tasks' ),
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
