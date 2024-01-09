<?php
/**
 * Meta box task details
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$project_text = $task->project_id;

if ( \property_exists( $wpdb, 'orbis_projects' ) && \property_exists( $wpdb, 'orbis_companies' ) ) {
	$query = $wpdb->prepare(
		"
		SELECT
			project.id AS project_id,
			principal.name AS principal_name,
			project.name AS project_name,
			project.number_seconds AS project_time
		FROM
			$wpdb->orbis_projects AS project
				LEFT JOIN
			$wpdb->orbis_companies AS principal
					ON project.principal_id = principal.id
		WHERE
			project.finished = 0
				AND
			project.id = %d
		GROUP BY
			project.id
		ORDER BY
			project.id
		;
		",
		$task->project_id
	);

	$project = $wpdb->get_row( $query );

	if ( $project ) {
		$project_text = sprintf(
			'%s. %s - %s ( %s )',
			$project->project_id,
			$project->principal_name,
			$project->project_name,
			orbis_time( $project->project_time )
		);
	}
}

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_id"><?php esc_html_e( 'Orbis ID', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input type="text" id="orbis_task_id" name="_orbis_task_id" value="<?php echo esc_attr( $task->id ); ?>" class="regular-text" readonly="readonly" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_project"><?php esc_html_e( 'Project', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<select id="orbis_task_project" name="_orbis_task_project_id" class="orbis-id-control orbis-project-id-control regular-text">
				<option id="orbis_select2_default" value="<?php echo esc_attr( $task->project_id ); ?>">
					<?php echo esc_attr( $project_text ); ?>
				</option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_assignee_id"><?php esc_html_e( 'Assignee', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<?php

			wp_dropdown_users(
				[
					'id'               => 'orbis_task_assignee_id',
					'name'             => '_orbis_task_assignee_id',
					'selected'         => $task->assignee_id,
					'show_option_none' => __( '— Select Assignee —', 'orbis-tasks' ),
				] 
			);

			?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_start_date"><?php esc_html_e( 'Start date', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_start_date" name="_orbis_task_start_date" value="<?php echo esc_attr( null === $task->start_date ? '' : $task->start_date->format( 'Y-m-d' ) ); ?>" type="date" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_end_date"><?php esc_html_e( 'End date', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_end_date" name="_orbis_task_end_date" value="<?php echo esc_attr( null === $task->end_date ? '' : $task->end_date->format( 'Y-m-d' ) ); ?>" type="date" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="_orbis_task_seconds_string"><?php esc_html_e( 'Time', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input size="5" id="_orbis_task_seconds_string" name="_orbis_task_seconds_string" value="<?php echo esc_attr( orbis_time( $task->seconds ) ); ?>" type="text" class="small-text" />

			<p class="description">
				<?php esc_html_e( 'You can enter time as 1.5 or 1:30 (they both mean 1 hour and 30 minutes).', 'orbis-tasks' ); ?>
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="orbis_task_completed"><?php esc_html_e( 'Completed', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<label for="orbis_task_completed">
				<input id="orbis_task_completed" name="_orbis_task_completed" value="1" type="checkbox" <?php checked( $task->completed ); ?> />
				<?php esc_html_e( 'Task is completed', 'orbis-tasks' ); ?>
			</label>
		</td>
	</tr>
</table>
