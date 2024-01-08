<?php

global $wpdb, $post;

wp_nonce_field( 'orbis_save_task_details', 'orbis_task_details_meta_box_nonce' );

$orbis_id      = get_post_meta( $post->ID, '_orbis_task_id', true );
$project_id    = get_post_meta( $post->ID, '_orbis_task_project_id', true );
$assignee_id   = get_post_meta( $post->ID, '_orbis_task_assignee_id', true );
$due_at_string = get_post_meta( $post->ID, '_orbis_task_due_at_string', true );
$seconds       = get_post_meta( $post->ID, '_orbis_task_seconds', true );
$completed     = get_post_meta( $post->ID, '_orbis_task_completed', true );

$query = "
	SELECT
		project.id AS project_id,
		principal.name AS principal_name,
		project.name AS project_name,
		project.number_seconds AS project_time
		$extra_select
	FROM
		$wpdb->orbis_projects AS project
			LEFT JOIN
		$wpdb->orbis_companies AS principal
				ON project.principal_id = principal.id
	WHERE
		project.finished = 0
			AND
		project.id = $project_id
	GROUP BY
		project.id
	ORDER BY
		project.id
";

$project = $wpdb->get_results( $query )[0];

$project_text = sprintf(
	'%s. %s - %s ( %s )',
	$project->project_id,
	$project->principal_name,
	$project->project_name,
	orbis_time( $project->project_time )
);

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_id"><?php esc_html_e( 'Orbis ID', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input type="text" id="orbis_task_id" name="_orbis_task_id" value="<?php echo esc_attr( $orbis_id ); ?>" class="regular-text" readonly="readonly" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_project"><?php esc_html_e( 'Project', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<select id="orbis_task_project" name="_orbis_task_project_id" class="orbis-id-control orbis-project-id-control regular-text">
				<option id="orbis_select2_default" value="<?php echo esc_attr( $project_id ); ?>">
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
					'selected'         => $assignee_id,
					'show_option_none' => __( '&mdash; Select Assignee &mdash;', 'orbis-tasks' ),
				] 
			);

			?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_due_at"><?php esc_html_e( 'Due At', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_due_at" name="_orbis_task_due_at_string" value="<?php echo esc_attr( $due_at_string ); ?>" type="text" class="regular-text orbis-datepicker" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="_orbis_task_seconds_string"><?php esc_html_e( 'Time', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input size="5" id="_orbis_task_seconds_string" name="_orbis_task_seconds_string" value="<?php echo esc_attr( orbis_time( $seconds ) ); ?>" type="text" class="small-text" />

			<p class="description">
				<?php esc_html_e( 'You can enter time as 1.5 or 1:30 (they both mean 1 hour and 30 minutes).', 'orbis' ); ?>
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="orbis_task_completed"><?php esc_html_e( 'Completed', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<label for="orbis_task_completed">
				<input id="orbis_task_completed" name="_orbis_task_completed" value="1" type="checkbox" <?php checked( $completed ); ?> />
				<?php esc_html_e( 'Task is completed', 'orbis-tasks' ); ?>
			</label>
		</td>
	</tr>
</table>
