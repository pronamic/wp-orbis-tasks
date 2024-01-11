<?php
/**
 * Task details
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

$task = Task::from_post( \get_post() );

?>
<div class="card mb-3">
	<div class="card-header"><?php \esc_html_e( 'Task details', 'orbis-tasks' ); ?></div>

	<div class="card-body">
		<div class="content">
			<dl>
				<dt><?php \esc_html_e( 'Posted on', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( get_the_date() ); ?></dd>

				<dt><?php \esc_html_e( 'Posted by', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( \get_the_author() ); ?></dd>

				<dt><?php \esc_html_e( 'Assignee', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( null === $task->assignee_id ? '—' : \get_user_by( 'ID', $task->assignee_id )->display_name ); ?></dd>

				<dt><?php \esc_html_e( 'Due at', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( null === $task->due_date ? '—' : $task->due_date->format( 'd-m-Y' ) ); ?></dd>

				<dt><?php \esc_html_e( 'Time', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( null === $task->seconds ? '—' : \orbis_time( $task->seconds ) ); ?></dd>

				<dt><?php \esc_html_e( 'Status', 'orbis-tasks' ); ?></dt>
				<dd><?php echo \esc_html( $task->completed ? \__( 'Closed', 'orbis-tasks' ) : \__( 'Open', 'orbis-tasks' ) ); ?></dd>
			</dl>
		</div>
	</div>
</div>
