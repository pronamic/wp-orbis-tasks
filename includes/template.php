<?php

function orbis_task_time() {
	$post_id = get_the_ID();

	$seconds = get_post_meta( $post_id, '_orbis_task_seconds', true );
	
	echo orbis_time( $seconds );
}

function orbis_task_due_at() {
	$post_id = get_the_ID();

	$date = get_post_meta( $post_id, '_orbis_task_due_at', true );

	echo date_i18n( 'D j M Y', strtotime( $date ) );
}

function orbis_task_project() {
	global $post;
	
	if ( isset( $post->project_post_id ) ) {
		printf( 
			'<a href="%s">%s</a>',
			esc_attr( get_permalink( $post->project_post_id ) ),
			esc_html( get_the_title( $post->project_post_id ) )	
		);
	}
}

function orbis_finish_task_link() {
	printf(
		'<a href="%s" class="orbis-confirm"><i class="icon-ok"></i> <span style="display: none">%s</span></a>',
		esc_attr( get_finish_orbis_task_link( get_the_ID() ) ),
		esc_html( __( 'Finish', 'orbis_tasks' ) )
	);
}
