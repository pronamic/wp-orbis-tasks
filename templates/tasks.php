<?php

global $orbis_tasks;

?>
<div class="panel">
	<table class="table table-striped table-bordered table-condense">
		<thead>
			<tr>
				<th><?php _e( 'Added By', 'orbis_tasks' ); ?></th>
				<th><?php _e( 'Project', 'orbis_tasks' ); ?></th>
				<th><?php _e( 'Executor', 'orbis_tasks' ); ?></th>
				<th><?php _e( 'Description', 'orbis_tasks' ); ?></th>
				<th><?php _e( 'End date', 'orbis_tasks' ); ?></th>
				<th><?php _e( 'Hours', 'orbis_tasks' ); ?></th>
				<th><?php _e( 'Days', 'orbis_tasks' ); ?></th>
				<th><?php _e( 'Actions', 'orbis_tasks' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $orbis_tasks as $task ) : ?>

				<?php

				$time_diff = strtotime( $task->planned_end_date ) - time();

				$classes = array( 'task' );

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
						<?php echo mysql2date( 'd-m-Y', $task->planned_end_date ); ?>
					</td>
					<td>
						<?php echo orbis_time( $task->planned_duration ); ?>
					</td>
					<td class="number-days-column">
						<?php echo round( $time_diff / ( 3600 * 24 ) ); ?>
					</td>
					<td>

					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>
</div>