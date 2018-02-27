<?php

$task_description   = filter_input( INPUT_POST, '_orbis_task_description', FILTER_SANITIZE_STRING );
$task_project_id    = filter_input( INPUT_POST, '_orbis_task_project_id', FILTER_SANITIZE_STRING );
$task_assignee_id   = filter_input( INPUT_POST, '_orbis_task_assignee_id', FILTER_SANITIZE_STRING );
$task_due_at_string = filter_input( INPUT_POST, '_orbis_task_due_at_string', FILTER_SANITIZE_STRING );

?>
<div class="card">
	<div class="card-body">
		<form action="" method="post">
			<?php wp_nonce_field( 'orbis_tasks_add_new_task', 'orbis_tasks_new_task_nonce' ); ?>

			<legend class="card-title"><?php esc_html_e( 'Add task', 'orbis_tasks' ); ?></legend>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php esc_html_e( 'Description', 'orbis_tasks' ); ?></label>
						<input placeholder="Task description" class="form-control input-lg" name="_orbis_task_description" value="<?php echo esc_attr( $task_description ); ?>" type="text">
					</div>
				</div>
				
				<div class="col-md-6">
					<div class="form-group">
						<label><?php esc_html_e( 'Time', 'orbis_tasks' ); ?></label>
						<input placeholder="00:00" class="form-control input-sm" type="text" name="_orbis_task_seconds_string" />
					</div>
				</div>
			</div>

			<div class="form-group">
				<label><?php esc_html_e( 'Project', 'orbis_tasks' ); ?></label>
				<input placeholder="Select project" class="form-control" type="text" name="_orbis_task_project_id" value="<?php echo esc_attr( $task_project_id ); ?>" />
			</div>
			
			<div class="form-group">
				<label><?php esc_html_e( 'Person', 'orbis_tasks' ); ?></label>
				<?php

				wp_dropdown_users( array(
					'id'               => 'orbis_task_assignee_id',
					'name'             => '_orbis_task_assignee_id',
					'selected'         => $assignee_id,
					'show_option_none' => __( '&mdash; Select Assignee &mdash;', 'orbis_tasks' ),
				) );

				?>
			</div>
			
			<div class="form-group">
				<label><?php esc_html_e( 'Date', 'orbis_tasks' ); ?></label>
				<input placeholder="dd-mm-yyyy" type="text" name="_orbis_task_due_at_string" value="<?php echo esc_attr( $task_due_at_string ); ?>" class="orbis-datepicker" />
			</div>
			
			<div class="form-actions">
				<button type="submit" class="btn btn-primary" name="orbis_task_add"><?php esc_html_e( 'Save task', 'orbis_tasks' ); ?></button>
				<button type="button" class="btn btn-default" data-toggle="collapse" data-target="#demo"><?php esc_html_e( 'Cancel', 'orbis_tasks' ); ?></button>
			</div>
		</form>
	</div>
</div>
