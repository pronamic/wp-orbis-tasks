<?php
/**
 * Template controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

/**
 * Template controller class
 */
class TemplateController {
	/**
	 * Construct.
	 */
	public function __construct() {
		\add_filter( 'template_include', $this->template_include( ... ) );

		\add_action( 'get_template_part_templates/filter', $this->search_form_filter( ... ), 10, 2 );
	}

	/**
	 * Template include.
	 *
	 * Uses the single and archive task templates of this plugin, unless the theme has one.
	 *
	 * @param string $template Template.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( \is_singular( 'orbis_task' ) && '' === \locate_template( 'single-orbis_task.php' ) ) {
			return __DIR__ . '/../templates/single-orbis_task.php';
		}

		if ( \is_post_type_archive( 'orbis_task' ) && '' === \locate_template( 'archive-orbis_task.php' ) ) {
			return __DIR__ . '/../templates/archive-orbis_task.php';
		}

		return $template;
	}

	/**
	 * Search form filter.
	 *
	 * The search form of the Orbis theme requests the `templates/filter`
	 * template part with the post type as name, this adds the task assignee
	 * filter, unless the theme has its own.
	 *
	 * @param string      $slug Slug.
	 * @param string|null $name Name.
	 * @return void
	 */
	public function search_form_filter( $slug, $name ) {
		if ( 'orbis_task' !== $name ) {
			return;
		}

		if ( '' !== \locate_template( 'templates/filter-orbis_task.php' ) ) {
			return;
		}

		include __DIR__ . '/../templates/search-form-filter.php';
	}
}
