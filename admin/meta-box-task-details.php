<?php

global $wpdb, $post;

wp_nonce_field( 'orbis_save_task_details', 'orbis_task_details_meta_box_nonce' );

$orbis_id    = get_post_meta( $post->ID, '_orbis_task_id', true );

$project_id    = get_post_meta( $post->ID, '_orbis_task_project_id', true );
$assignee_id   = get_post_meta( $post->ID, '_orbis_task_assignee_id', true );
$due_at_string = get_post_meta( $post->ID, '_orbis_task_due_at_string', true );
$completed     = get_post_meta( $post->ID, '_orbis_task_completed', true );
$seconds       = get_post_meta( $post->ID, '_orbis_task_seconds', true );

if ( true ) {
	$task = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->orbis_tasks WHERE post_id = %d;", $post->ID ) );

	if ( $task ) {
		$project_id	 = $task->project_id;
		$assignee_id = $task->assignee_id;
		$due_at      = $task->due_at;
		$completed   = $task->completed;
	}
}

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_id"><?php _e( 'Orbis ID', 'orbis_tasks' ); ?></label>
		</th>
		<td>
			<input type="text" id="orbis_task_id" name="_orbis_task_id" value="<?php echo esc_attr( $orbis_id ); ?>" class="regular-text" readonly="readonly" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_project"><?php _e( 'Project', 'orbis_tasks' ); ?></label>
		</th>
		<td>
			<input type="text" id="orbis_task_project" name="_orbis_task_project_id" value="<?php echo esc_attr( $project_id ); ?>" class="orbis-id-control orbis-project-id-control regular-text" data-text="<?php echo esc_attr( $project_id ); ?>" placeholder="<?php _e( 'Select Project', 'orbis' ); ?>" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_assignee_id"><?php _e( 'Assignee', 'orbis_tasks' ); ?></label>
		</th>
		<td>
			<?php
			
			wp_dropdown_users( array(
				'id'               => 'orbis_task_assignee_id',
				'name'             => '_orbis_task_assignee_id',
				'selected'         => $assignee_id,
				'show_option_none' => __( '&mdash; Select Assignee &mdash;', 'orbis_tasks' ),
			) );

			?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_due_at"><?php _e( 'Due At', 'orbis_tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_due_at" name="_orbis_task_due_at_string" value="<?php echo esc_attr( $due_at_string ); ?>" type="text" class="regular-text orbis-datepicker" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="_orbis_task_seconds_string"><?php _e( 'Time', 'orbis_tasks' ); ?></label>
		</th>
		<td>
			<input size="5" id="_orbis_task_seconds_string" name="_orbis_task_seconds_string" value="<?php echo esc_attr( orbis_time( $seconds ) ); ?>" type="text" class="small-text" />

			<p class="description">
				<?php _e( 'You can enter time as 1.5 or 1:30 (they both mean 1 hour and 30 minutes).', 'orbis' ); ?>
			</p>
		</td>
	</tr>
</table>