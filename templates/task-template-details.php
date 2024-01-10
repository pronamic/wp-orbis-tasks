<?php
/**
 * Task template details
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

$task_template = TaskTemplate::from_post( \get_post() );

?>
<div class="card mb-3">
	<div class="card-header"><?php \esc_html_e( 'Task template details', 'orbis-tasks' ); ?></div>

	<div class="card-body">
		<div class="content">
			<dl>
				<dt><?php \esc_html_e( 'Posted on', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( get_the_date() ); ?></dd>

				<dt><?php \esc_html_e( 'Posted by', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( \get_the_author() ); ?></dd>

				<dt><?php \esc_html_e( 'Assignee', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( null === $task_template->assignee_id ? '—' : \get_user_by( 'ID', $task_template->assignee_id )->display_name ); ?></dd>

				<dt><?php \esc_html_e( 'Time', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( null === $task_template->seconds ? '—' : \orbis_time( $task_template->seconds ) ); ?></dd>
			</dl>
		</div>
	</div>
</div>
