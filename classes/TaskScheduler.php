<?php
/**
 * Task scheduler
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

use WP_Post;
use WP_Query;

/**
 * Task scheduler class
 */
class TaskScheduler {
	/**
	 * Plugin.
	 * 
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct task scheduler.
	 * 
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		\add_action( 'orbis_tasks_schedule_create_tasks', [ $this, 'schedule_all' ] );

		\add_action( 'orbis_tasks_schedule_paged_create_tasks', [ $this, 'schedule_paged' ] );

		\add_action( 'orbis_tasks_create_task_from_template', [ $this, 'create_task_from_template' ] );
	}

	/**
	 * Initialize.
	 * 
	 * @return void
	 */
	public function init() {
		/**
		 * Schedule.
		 * 
		 * @link https://actionscheduler.org/usage/
		 */
		if ( false === \as_has_scheduled_action( 'orbis_tasks_schedule_create_tasks' ) ) {
			\as_schedule_recurring_action(
				\strtotime( 'tomorrow' ),
				\DAY_IN_SECONDS,
				'orbis_tasks_schedule_create_tasks',
				[],
				'orbis-tasks',
				true
			);
		}
	}

	/**
	 * Schedule all.
	 *
	 * @return void
	 */
	public function schedule_all() {
		$query = new WP_Query(
			[
				'fields'         => 'ids',
				'post_type'      => 'orbis_task_template',
				'posts_per_page' => 100,
			]
		);

		if ( 0 === $query->max_num_pages ) {
			return;
		}

		$pages = \range( $query->max_num_pages, 1 );

		foreach ( $pages as $page ) {
			$this->schedule_page( $page );
		}
	}

	/**
	 * Schedule page.
	 *
	 * @param int $page Page.
	 * @return int
	 */
	private function schedule_page( $page ) {
		return \as_enqueue_async_action(
			'orbis_tasks_schedule_paged_create_tasks',
			[
				'page' => $page,
			],
			'orbis-tasks'
		);
	}

	/**
	 * Schedule paged.
	 *
	 * @param int $page Page.
	 * @return void
	 */
	public function schedule_paged( $page ) {
		$query = new WP_Query(
			[
				'post_type'      => 'orbis_task_template',
				'posts_per_page' => 100,
				'paged'          => $page,
			]
		);

		$posts = \array_filter(
			$query->posts,
			function ( $post ) {
				return ( $post instanceof WP_Post );
			}
		);

		foreach ( $posts as $post ) {
			$task_template = TaskTemplate::from_post( $post );

			\as_enqueue_async_action(
				'orbis_tasks_create_task_from_template',
				[ 
					'task_template_post_id' => $task_template->post_id,
				],
				'orbis-tasks'
			);
		}
	}

	/**
	 * Create task from template.
	 * 
	 * @param int $task_template_post_id Task template post ID.
	 * @return void
	 * @throws \Exception Throws exception if task template cannot be found.
	 */
	public function create_task_from_template( $task_template_post_id ) {
		$task_template_post = \get_post( $task_template_post_id );

		if ( null === $task_template_post ) {
			throw new \Exception( 'Cannot find task template post with ID: ' . \esc_html( $task_template_post_id ) );
		}

		$task_template = TaskTemplate::from_post( $task_template_post );

		if ( empty( $task_template->interval ) ) {
			return;
		}

		$task = new Task();

		$task->start_date = $task_template->next_creation_date;
		$task->end_date   = $task_template->next_creation_date;

		$this->plugin->save_task( $task );
	}
}
