<?php

class Orbis_Tasks_Plugin extends Orbis_Plugin {
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->set_name( 'orbis_tasks' );
		$this->set_db_version( '0.1.0' );

		$this->plugin_include( 'includes/functions.php' );
		$this->plugin_include( 'includes/shortcodes.php' );

		orbis_register_table( 'orbis_tasks', false, '' );
	}

	public function loaded() {
		$this->load_textdomain( 'orbis_tasks', '/languages/' );
	}

	public function install() {


		parent::install();
	}
}
