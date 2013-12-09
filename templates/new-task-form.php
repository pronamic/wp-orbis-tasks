<?php 

$task_description   = filter_input( INPUT_POST, '_orbis_task_description', FILTER_SANITIZE_STRING );
$task_project_id    = filter_input( INPUT_POST, '_orbis_task_project_id', FILTER_SANITIZE_STRING );
$task_assignee_id   = filter_input( INPUT_POST, '_orbis_task_assignee_id', FILTER_SANITIZE_STRING );
$task_due_at_string = filter_input( INPUT_POST, '_orbis_task_due_at_string', FILTER_SANITIZE_STRING );

?>
<div class="panel">
	<div class="content">
		<form action="" method="post">
			<?php wp_nonce_field( 'orbis_tasks_add_new_task', 'orbis_tasks_new_task_nonce' ); ?>

			<legend>Add task</legend>
			
			<div class="form-line clearfix">
				<div class="col" style="float: left; margin-right: 20px;">
					<label>Description</label>
					<input placeholder="Task description" class="input-xxlarge" name="_orbis_task_description" value="<?php echo esc_attr( $task_description ); ?>" style="font-size: 18px; padding: 12px;" type="text">
				</div>
					
				<div class="col" style="width: 40%; float: left;">
					<label>Time</label>
					<input placeholder="00:00" class="input-mini" style="font-size: 18px; padding: 12px;" type="text" name="_orbis_task_seconds_string" />
				</div>
			</div>
		
			<label>Project</label>
			<input placeholder="Select project" type="text" name="_orbis_task_project_id" value="<?php echo esc_attr( $task_project_id ); ?>" />

			<label>Person</label>
			<?php

			wp_dropdown_users( array(
				'id'               => 'orbis_task_assignee_id',
				'name'             => '_orbis_task_assignee_id',
				'selected'         => $assignee_id,
				'show_option_none' => __( '&mdash; Select Assignee &mdash;', 'orbis_tasks' ),
			) );

			?>

			<label>Date</label>
			<input placeholder="dd-mm-yyyy" type="text" name="_orbis_task_due_at_string" value="<?php echo esc_attr( $task_due_at_string ); ?>" class="orbis-datepicker" />
			
			<div class="form-actions">
				<button type="submit" class="btn btn-primary" name="orbis_task_add">Save task</button>
				<button type="button" class="btn" data-toggle="collapse" data-target="#demo">Cancel</button>
			</div>
		</form>
	</div>
</div>