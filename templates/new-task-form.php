<?php 

$task_description = filter_input( INPUT_POST, 'orbis_task_description', FILTER_SANITIZE_STRING );
$task_project_id  = filter_input( INPUT_POST, 'orbis_task_project_id', FILTER_SANITIZE_STRING );
$task_assignee_id = filter_input( INPUT_POST, 'orbis_task_assignee_id', FILTER_SANITIZE_STRING );
$task_due_at      = filter_input( INPUT_POST, 'orbis_task_due_at', FILTER_SANITIZE_STRING );

if ( filter_has_var( INPUT_POST,  'orbis_task_add' ) ) {
	$nonce = filter_input( INPUT_POST, 'orbis_tasks_new_task_nonce', FILTER_SANITIZE_STRING );
	
	if ( wp_verify_nonce( $nonce, 'orbis_tasks_add_new_task' ) ) {

		$result = wp_insert_post( array(
			'post_type'             => 'orbis_task',
			'post_status'           => 'publish',
			'post_title'            => $task_description,
		), true );

		if ( is_wp_error( $result ) ) {
			var_dump( $result );
		} else {
			$post_id = $result;
			
			$data = array(
				'_orbis_task_project_id'  => $task_project_id,
				'_orbis_task_assignee_id' => $task_assignee_id,
				'_orbis_task_due_at'      => $task_due_at,
			);
			
			foreach ( $data as $key => $value ) {
				if ( empty( $value ) ) {
					delete_post_meta( $post_id, $key );
				} else {
					update_post_meta( $post_id, $key, $value );
				}
			}
		}
	}
}

?>
<div class="panel">
	<div class="content">
		<form action="" method="post">
			<?php wp_nonce_field( 'orbis_tasks_add_new_task', 'orbis_tasks_new_task_nonce' ); ?>

			<legend>Add task</legend>
			
			<div class="form-line clearfix">
			
				<div class="col" style="float: left; margin-right: 20px;">
					<label>Description</label>
					<input placeholder="Task description" class="input-xxlarge" name="orbis_task_description" value="<?php echo esc_attr( $task_description ); ?>" style="font-size: 18px; padding: 12px;" type="text">
					
					</div>
					
					<div class="col" style="width: 40%; float: left;">
						<label>Time</label>
						<input placeholder="00:00" class="input-mini" style="font-size: 18px; padding: 12px;" type="text">
				</div>
			</div>
		
			<label>Project</label>
			<input placeholder="Select project" type="text" name="orbis_task_project_id" value="<?php echo esc_attr( $task_project_id ); ?>" />

			<label>Person</label>
			<input placeholder="Select person" type="text" name="orbis_task_assignee_id" value="<?php echo esc_attr( $task_description ); ?>" />

			<label>Date</label>
			<input placeholder="dd-mm-yyyy" type="text" name="orbis_task_due_at" value="<?php echo esc_attr( $task_due_at ); ?>" />
			
			<div class="form-actions">
				<button type="submit" class="btn btn-primary" name="orbis_task_add">Save task</button>
				<button type="button" class="btn" data-toggle="collapse" data-target="#demo">Cancel</button>
			</div>
		</form>
	</div>
</div>