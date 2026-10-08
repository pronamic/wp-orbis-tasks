<?php
/**
 * Archive tasks
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

use DateTimeImmutable;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

$today = new DateTimeImmutable( 'now', \wp_timezone() );

\get_header();

?>
<div class="card">
	<?php \get_template_part( 'templates/search_form' ); ?>

	<?php if ( \have_posts() ) : ?>

		<div class="table-responsive">
			<table class="table table-striped table-condense table-hover">
				<thead>
					<tr>
						<th><?php \esc_html_e( 'Task', 'orbis-tasks' ); ?></th>
						<th><?php \esc_html_e( 'Assignee', 'orbis-tasks' ); ?></th>
						<th><?php \esc_html_e( 'Due At', 'orbis-tasks' ); ?></th>
						<th><span class="visually-hidden"><?php \esc_html_e( 'Actions', 'orbis-tasks' ); ?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php

					while ( \have_posts() ) :
						\the_post();

						$task_post = \get_post();

						$task = Task::from_post( $task_post );

						?>

						<tr id="post-<?php \the_ID(); ?>" <?php \post_class(); ?>>
							<td>
								<a class="title" href="<?php \the_permalink(); ?>"><?php \the_title(); ?></a> <br />

								<div class="entry-meta">
									<i class="fa fa-file" aria-hidden="true"></i>
									<?php

									if ( isset( $task_post->project_post_id ) ) {
										\printf(
											'<a href="%s">%s</a>',
											\esc_url( (string) \get_permalink( $task_post->project_post_id ) ),
											\esc_html( \get_the_title( $task_post->project_post_id ) )
										);
									}

									?>

									<i class="fa fa-user" aria-hidden="true"></i>
									<?php

									if ( isset( $task_post->task_assignee_display_name ) ) {
										echo \esc_html( $task_post->task_assignee_display_name );
									}

									?>

									<i class="fa fa-clock-o" aria-hidden="true"></i>
									<?php

									echo \esc_html( \orbis_time( \get_post_meta( $task_post->ID, '_orbis_task_seconds', true ) ) );

									?>
								</div>
							</td>
							<td>
								<?php echo \get_avatar( \get_post_meta( $task_post->ID, '_orbis_task_assignee_id', true ), 40 ); ?>
							</td>
							<td>
								<?php

								if ( null === $task->due_date ) {
									echo '—';
								}

								if ( null !== $task->due_date ) {
									echo \esc_html( \wp_date( 'D j M Y', $task->due_date->getTimestamp() ) );

									if ( $task->due_date < $today ) {
										$diff = $task->due_date->diff( $today );

										\printf(
											' <span class="badge text-bg-danger">%s</span>',
											\esc_html(
												\sprintf(
													/* translators: %s: Number of days. */
													\_n( '%s day', '%s days', $diff->days, 'orbis-tasks' ),
													\number_format_i18n( $diff->days, 0 )
												)
											)
										);
									}
								}

								?>
							</td>
							<td>
								<?php \get_template_part( 'templates/table-cell-actions' ); ?>
							</td>
						</tr>

					<?php endwhile; ?>
				</tbody>
			</table>
		</div>

	<?php else : ?>

		<div class="card-body">
			<?php \get_template_part( 'templates/content-none' ); ?>
		</div>

	<?php endif; ?>
</div>

<?php

if ( \function_exists( 'orbis_content_nav' ) ) {
	\orbis_content_nav();
} else {
	\the_posts_pagination();
}

\get_footer();
