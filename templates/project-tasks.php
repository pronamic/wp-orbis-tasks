<?php
/**
 * Project tasks
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

use WP_Query;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query(
	[
		'post_type'          => 'orbis_task',
		'posts_per_page'     => 25,
		'orbis_task_project' => \get_the_ID(),
	]
);

if ( $query->have_posts() ) : ?>

	<div class="table-responsive">
		<table class="table table-striped mb-0">
			<thead>
				<tr>
					<th class="border-top-0"><?php \esc_html_e( 'Description', 'orbis-tasks' ); ?></th>
					<th class="border-top-0"><?php \esc_html_e( 'Comments', 'orbis-tasks' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php

				while ( $query->have_posts() ) :
					$query->the_post();

					?>

					<tr id="post-<?php \the_ID(); ?>" <?php \post_class(); ?>>
						<td>
							<a href="<?php \the_permalink(); ?>"><?php \the_title(); ?></a>
						</td>
						<td>
							<span class="badge text-bg-secondary"><?php \comments_number( '0', '1', '%' ); ?></span>
						</td>
					</tr>

				<?php endwhile; ?>
			</tbody>
		</table>
	</div>

	<?php \wp_reset_postdata(); ?>

<?php else : ?>

	<div class="card-body">
		<p class="text-muted m-0">
			<?php \esc_html_e( 'No tasks found.', 'orbis-tasks' ); ?>
		</p>
	</div>

<?php endif; ?>
