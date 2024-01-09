<?php

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
			<label for="orbis_task_template_interval"><?php esc_html_e( 'Interval', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<select id="orbis_task_template_interval" name="_orbis_task_template_interval">
				<?php

				foreach ( $intervals as $value => $label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $value ),
						selected( $value, $task_template->interval, false ),
						esc_html( $label )
					);
				}

				?>
			</select>
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
			<label for="orbis_task_template_start_date"><?php esc_html_e( 'Start date', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_template_start_date" name="_orbis_task_template_start_date" value="<?php echo esc_attr( null === $task_template->start_date ? '' : $task_template->start_date->format( 'Y-m-d' ) ); ?>" type="date" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_template_end_date"><?php esc_html_e( 'End date', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_template_end_date" name="_orbis_task_template_end_date" value="<?php echo esc_attr( null === $task_template->end_date ? '' : $task_template->end_date->format( 'Y-m-d' ) ); ?>" type="date" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_task_template_next_creation_date"><?php esc_html_e( 'Next creation date', 'orbis-tasks' ); ?></label>
		</th>
		<td>
			<input id="orbis_task_template_next_creation_date" name="_orbis_task_template_next_creation_date" value="<?php echo esc_attr( null === $task_template->next_creation_date ? '' : $task_template->next_creation_date->format( 'Y-m-d' ) ); ?>" type="date" class="regular-text" />
		</td>
	</tr>
</table>
