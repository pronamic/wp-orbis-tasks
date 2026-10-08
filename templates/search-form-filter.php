<?php
/**
 * Search form filter
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="d-flex align-items-center gap-2">
	<?php

	\wp_dropdown_users(
		[
			'name'             => 'orbis_task_assignee',
			'selected'         => \filter_input( \INPUT_GET, 'orbis_task_assignee', \FILTER_VALIDATE_INT, \FILTER_NULL_ON_FAILURE ),
			'show_option_none' => \__( '— Select Assignee —', 'orbis-tasks' ),
			'class'            => 'form-control',
		]
	);

	?>

	<button class="btn btn-secondary" type="submit"><?php \esc_html_e( 'Filter', 'orbis-tasks' ); ?></button>
</div>
