<?php

global $orbis_tasks;

?>
<div class="panel">
	<table class="table table-striped table-bordered table-condense">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Added By', 'orbis-tasks' ); ?></th>
				<th><?php esc_html_e( 'Project', 'orbis-tasks' ); ?></th>
				<th><?php esc_html_e( 'Executor', 'orbis-tasks' ); ?></th>
				<th><?php esc_html_e( 'Description', 'orbis-tasks' ); ?></th>
				<th><?php esc_html_e( 'End date', 'orbis-tasks' ); ?></th>
				<th><?php esc_html_e( 'Hours', 'orbis-tasks' ); ?></th>
				<th><?php esc_html_e( 'Days', 'orbis-tasks' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'orbis-tasks' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $orbis_tasks as $task ) : ?>

				<?php

				$time_diff = strtotime( $task->plannedesc_html_end_date ) - time();

				$classes = [ 'task' ];

				if ( $time_diff < 0 ) {
					$classes[] = 'failed';
				}


				?>
				<tr class="<?php echo implode( ' ', $classes ); ?>">
					<td>
						<?php echo $task->added_by_name; ?>
					</td>
					<td>
						<a href="<?php echo get_permalink( $task->project_post_id ); ?>">
							<?php echo $task->company_name; ?> - <?php echo $task->project_name; ?>
						</a>
					</td>
					<td>
						<?php echo $task->assigned_to_name; ?>
					</td>
					<td>
						<?php echo apply_filters( 'orbis_task_text', $task->task ); ?>
					</td>
					<td>
						<?php echo mysql2date( 'd-m-Y', $task->plannedesc_html_end_date ); ?>
					</td>
					<td>
						<?php echo orbis_time( $task->planned_duration ); ?>
					</td>
					<td class="number-days-column">
						<?php echo round( $time_diff / ( 3600 * 24 ) ); ?>
					</td>
					<td>
						<a href="<?php echo get_finish_orbis_task_link( $task->id ); ?>"><span class="glyphicon glyphicon-ok"></span> <span style="display: none"><?php esc_html_e( 'Finish', 'orbis-tasks' ); ?></span></a>
						<a href="<?php echo get_delete_orbis_task_link( $task->id ); ?>"><span class="glyphicon glyphicon-remove"></span> <span style="display: none"><?php esc_html_e( 'Remove', 'orbis-tasks' ); ?></span></a>
						<a href="<?php echo getesc_html_edit_orbis_task_link( $task->id ); ?>"><span class="glyphicon glyphicon-pencil"></span> <span style="display: none"><?php esc_html_e( 'Edit', 'orbis-tasks' ); ?></span></a>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>
</div>
