<?php

class Orbis_Tasks_Plugin extends Orbis_Plugin {
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->set_name( 'orbis_tasks' );
		$this->set_db_version( '1.1.0' );

		$this->plugin_include( 'includes/functions.php' );
		$this->plugin_include( 'includes/post.php' );
		$this->plugin_include( 'includes/shortcodes.php' );
		$this->plugin_include( 'includes/template.php' );
		$this->plugin_include( 'includes/angular.php' );

		orbis_register_table( 'orbis_tasks' );

		add_action( 'widgets_init', array( $this, 'widgets_init' ) );

		// AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->ajax = new Orbis_Tasks_AJAX( $this );
		}
	}

	public function loaded() {
		$this->load_textdomain( 'orbis_tasks', '/languages/' );
	}

	public function install() {
		// Tables
		orbis_install_table( 'orbis_tasks', '
			id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED DEFAULT NULL,
			project_id BIGINT(16) UNSIGNED DEFAULT NULL,
			assignee_id BIGINT(20) UNSIGNED DEFAULT NULL,
			task TEXT,
			due_at DATETIME DEFAULT NULL,
			completed BOOLEAN NOT NULL DEFAULT FALSE,
			PRIMARY KEY  (id)
		' );

		parent::install();
	}

	function widgets_init() {
		$this->plugin_include( 'includes/widgets/class-orbis-widget-tasks.php' );

		register_widget( 'Orbis_Widget_Tasks' );
	}
}
