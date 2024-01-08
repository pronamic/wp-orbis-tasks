<?php

class Orbis_Tasks_Plugin {
	/**
	 * Construct.
	 */
	public function __construct() {
		include __DIR__ . '/../includes/functions.php';
		include __DIR__ . '/../includes/post.php';
		include __DIR__ . '/../includes/shortcodes.php';
		include __DIR__ . '/../includes/template.php';

		add_action( 'init', [ $this, 'init' ] );
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
