<?php
/**
 * Meta box task template details
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$intervals = [
	''         => \__( '— Select interval —', 'orbis-tasks' ),
	'1 week'   => \__( 'Weekly', 'orbis-tasks' ),
	'1 month'  => \__( 'Monthly', 'orbis-tasks' ),
	'3 months' => \__( 'Quarterly', 'orbis-tasks' ),
	'1 year'   => \__( 'Annual', 'orbis-tasks' ),
]

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_assignee_id"><?php esc_html_e( 'Assignee', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<?php

			wp_dropdown_users(
				[
					'id'               => 'orbis_task_template_assignee_id',
					'name'             => '_orbis_task_template_assignee_id',
					'selected'         => $task_template->assignee_id,
					'show_option_none' => __( '— Select assignee —', 'orbis-tasks' ),
				] 
			);

			?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_template_creation_date"><?php esc_html_e( 'Creation date', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_template_creation_date" name="_orbis_task_template_creation_date" value="<?php echo esc_attr( null === $task_template->creation_date ? '' : $task_template->creation_date->format( 'Y-m-d' ) ); ?>" type="date" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_template_due_date_modifier"><?php esc_html_e( 'Due date modifier', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_template_due_date_modifier" name="_orbis_task_template_due_date_modifier" value="<?php echo esc_attr( $task_template->due_date_modifier ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_template_start_date_modifier"><?php esc_html_e( 'Start date modifier', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_template_start_date_modifier" name="_orbis_task_template_start_date_modifier" value="<?php echo esc_attr( $task_template->start_date_modifier ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_template_end_date_modifier"><?php esc_html_e( 'End date modifier', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_template_end_date_modifier" name="_orbis_task_template_end_date_modifier" value="<?php echo esc_attr( $task_template->end_date_modifier ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_template_creation_date_modifier"><?php esc_html_e( 'Creation date modifier', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_template_creation_date_modifier" name="_orbis_task_template_creation_date_modifier" value="<?php echo esc_attr( $task_template->creation_date_modifier ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_template_time"><?php esc_html_e( 'Time', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input size="5" id="orbis_task_template_time" name="_orbis_task_template_time" value="<?php echo esc_attr( orbis_time( $task_template->seconds ) ); ?>" type="text" />

			<p class="description">
				<?php esc_html_e( 'You can enter time as 1.5 or 1:30 (they both mean 1 hour and 30 minutes).', 'orbis-tasks' ); ?>
			</p>
		</td>
	</tr>
</table>
