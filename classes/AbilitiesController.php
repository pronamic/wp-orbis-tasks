<?php
/**
 * Abilities controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

use DateTimeImmutable;
use WP_Error;
use WP_User;

/**
 * Abilities controller class
 *
 * @link https://developer.wordpress.org/news/2025/11/introducing-the-wordpress-abilities-api/
 */
class AbilitiesController {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct.
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
		\add_action( 'wp_abilities_api_categories_init', [ $this, 'register_ability_categories' ] );
		\add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );

		\add_filter( 'orbis_mcp_server_tools', [ $this, 'mcp_server_tools' ] );
	}

	/**
	 * Add abilities to the Orbis MCP server tools.
	 *
	 * @link https://github.com/pronamic/orbis-mcp-server
	 * @param string[] $tools Ability names.
	 * @return string[]
	 */
	public function mcp_server_tools( $tools ) {
		$tools[] = 'orbis-tasks/search';
		$tools[] = 'orbis-tasks/create';
		$tools[] = 'orbis-tasks/comment';

		return $tools;
	}

	/**
	 * Register ability categories.
	 *
	 * @return void
	 */
	public function register_ability_categories() {
		\wp_register_ability_category(
			'orbis-tasks',
			[
				'label'       => \__( 'Orbis Tasks', 'orbis-tasks' ),
				'description' => \__( 'Abilities for working with Orbis tasks.', 'orbis-tasks' ),
			]
		);
	}

	/**
	 * Register abilities.
	 *
	 * @return void
	 */
	public function register_abilities() {
		$nullable_string = [
			'type' => [ 'string', 'null' ],
		];

		$nullable_integer = [
			'type' => [ 'integer', 'null' ],
		];

		$date_description = \__( 'Date in Y-m-d format or a relative date in English, such as "today", "yesterday", "monday this week", "sunday this week", "monday last week" or "first day of next month". Relative dates are resolved in the timezone of the site.', 'orbis-tasks' );

		$assignee_description = \__( 'User to assign the task to: "me" for the current user, or a user ID, login, email address or display name.', 'orbis-tasks' );

		$task_schema = [
			'type'       => 'object',
			'properties' => [
				'id'            => [ 'type' => 'integer' ],
				'post_id'       => [ 'type' => 'integer' ],
				'title'         => [ 'type' => 'string' ],
				'url'           => [ 'type' => 'string' ],
				'content'       => [ 'type' => 'string' ],
				'status'        => [
					'type' => 'string',
					'enum' => [ 'open', 'completed' ],
				],
				'due_date'      => $nullable_string,
				'start_date'    => $nullable_string,
				'end_date'      => $nullable_string,
				'completed_at'  => $nullable_string,
				'seconds'       => $nullable_integer,
				'assignee'      => [
					'type'       => [ 'object', 'null' ],
					'properties' => [
						'id'   => [ 'type' => 'integer' ],
						'name' => [ 'type' => 'string' ],
					],
				],
				'author'        => [
					'type'       => [ 'object', 'null' ],
					'properties' => [
						'id'   => [ 'type' => 'integer' ],
						'name' => [ 'type' => 'string' ],
					],
				],
				'project'       => [
					'type'       => [ 'object', 'null' ],
					'properties' => [
						'id'      => [ 'type' => 'integer' ],
						'post_id' => $nullable_integer,
						'name'    => [ 'type' => 'string' ],
						'url'     => $nullable_string,
					],
				],
				'created'       => [ 'type' => 'string' ],
				'comment_count' => [ 'type' => 'integer' ],
			],
		];

		\wp_register_ability(
			'orbis-tasks/search',
			[
				'label'               => \__( 'Search tasks', 'orbis-tasks' ),
				'description'         => \__( 'Searches Orbis tasks and returns the matching tasks with their status, due date, period, completion date, assignee, project and comment count. Examples: my open tasks are status "open" with assignee "me"; tasks to do this week are status "open" with due_before "sunday this week"; overdue tasks are status "open" with due_before "yesterday"; tasks completed last week are status "completed" with completed_after "monday last week" and completed_before "sunday last week". The output contains the current date of the site. Use orbis/search-comments for the comments of a task.', 'orbis-tasks' ),
				'category'            => 'orbis-tasks',
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'search'           => [
							'type'        => 'string',
							'description' => \__( 'Search term, matched against the task title, the task description and the project name.', 'orbis-tasks' ),
						],
						'status'           => [
							'type'        => 'string',
							'description' => \__( 'Limit the results to open tasks, completed tasks or any task.', 'orbis-tasks' ),
							'enum'        => [ 'open', 'completed', 'any' ],
							'default'     => 'open',
						],
						'assignee'         => [
							'type'        => 'string',
							'description' => \__( 'Limit the results to tasks assigned to this user: "me" for the current user, "none" for unassigned tasks, or a user ID, login, email address or display name.', 'orbis-tasks' ),
						],
						'project_id'       => [
							'type'        => 'integer',
							'description' => \__( 'Limit the results to tasks of this Orbis project ID.', 'orbis-tasks' ),
							'minimum'     => 1,
						],
						'due_after'        => [
							'type'        => 'string',
							'description' => \__( 'Only return tasks due on or after this date.', 'orbis-tasks' ) . ' ' . $date_description,
						],
						'due_before'       => [
							'type'        => 'string',
							'description' => \__( 'Only return tasks due on or before this date.', 'orbis-tasks' ) . ' ' . $date_description,
						],
						'completed_after'  => [
							'type'        => 'string',
							'description' => \__( 'Only return tasks completed on or after this date.', 'orbis-tasks' ) . ' ' . $date_description,
						],
						'completed_before' => [
							'type'        => 'string',
							'description' => \__( 'Only return tasks completed on or before this date.', 'orbis-tasks' ) . ' ' . $date_description,
						],
						'orderby'          => [
							'type'        => 'string',
							'description' => \__( 'Sort the tasks by due date, completion date or creation date. Defaults to the completion date for completed tasks and the due date otherwise.', 'orbis-tasks' ),
							'enum'        => [ 'due_date', 'completed_at', 'created' ],
						],
						'order'            => [
							'type'        => 'string',
							'description' => \__( 'Sort order. Defaults to descending for the completion and creation date and ascending for the due date.', 'orbis-tasks' ),
							'enum'        => [ 'asc', 'desc' ],
						],
						'per_page'         => [
							'type'        => 'integer',
							'description' => \__( 'Maximum number of tasks to return.', 'orbis-tasks' ),
							'minimum'     => 1,
							'maximum'     => 100,
							'default'     => 50,
						],
						'page'             => [
							'type'        => 'integer',
							'description' => \__( 'Page of results to return.', 'orbis-tasks' ),
							'minimum'     => 1,
							'default'     => 1,
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'today'    => [ 'type' => 'string' ],
						'total'    => [ 'type' => 'integer' ],
						'page'     => [ 'type' => 'integer' ],
						'per_page' => [ 'type' => 'integer' ],
						'tasks'    => [
							'type'  => 'array',
							'items' => $task_schema,
						],
					],
				],
				'execute_callback'    => [ $this, 'search_tasks' ],
				'permission_callback' => fn() => \current_user_can( 'edit_posts' ),
				'meta'                => [
					'show_in_rest' => true,
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
				],
			]
		);

		\wp_register_ability(
			'orbis-tasks/create',
			[
				'label'               => \__( 'Create tasks', 'orbis-tasks' ),
				'description'         => \__( 'Creates one or more Orbis tasks, for example from a list in the conversation. Use one item per task with a short title; put any extra details in the content. The current user becomes the author of the tasks. Only set an assignee, project or dates when the user asked for it. All tasks are validated before any task is created, so either all tasks are created or none.', 'orbis-tasks' ),
				'category'            => 'orbis-tasks',
				'input_schema'        => [
					'type'                 => 'object',
					'required'             => [ 'tasks' ],
					'properties'           => [
						'tasks' => [
							'type'        => 'array',
							'description' => \__( 'Tasks to create.', 'orbis-tasks' ),
							'minItems'    => 1,
							'maxItems'    => 50,
							'items'       => [
								'type'                 => 'object',
								'required'             => [ 'title' ],
								'properties'           => [
									'title'      => [
										'type'        => 'string',
										'description' => \__( 'Short title of the task.', 'orbis-tasks' ),
										'minLength'   => 1,
									],
									'content'    => [
										'type'        => 'string',
										'description' => \__( 'Description of the task.', 'orbis-tasks' ),
									],
									'assignee'   => [
										'type'        => 'string',
										'description' => $assignee_description,
									],
									'project_id' => [
										'type'        => 'integer',
										'description' => \__( 'Orbis project ID to connect the task to.', 'orbis-tasks' ),
										'minimum'     => 1,
									],
									'due_date'   => [
										'type'        => 'string',
										'description' => \__( 'Due date of the task.', 'orbis-tasks' ) . ' ' . $date_description,
									],
									'start_date' => [
										'type'        => 'string',
										'description' => \__( 'Start date of the period in which the task is planned.', 'orbis-tasks' ) . ' ' . $date_description,
									],
									'end_date'   => [
										'type'        => 'string',
										'description' => \__( 'End date (inclusive) of the period in which the task is planned.', 'orbis-tasks' ) . ' ' . $date_description,
									],
									'time'       => [
										'type'        => 'string',
										'description' => \__( 'Estimated time in hours, for example "1.5" or "1:30" for 1 hour and 30 minutes.', 'orbis-tasks' ),
									],
								],
								'additionalProperties' => false,
							],
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'tasks' => [
							'type'  => 'array',
							'items' => $task_schema,
						],
					],
				],
				'execute_callback'    => [ $this, 'create_tasks' ],
				'permission_callback' => fn() => \current_user_can( 'edit_posts' ) && \current_user_can( 'publish_posts' ),
				'meta'                => [
					'show_in_rest' => true,
					'annotations'  => [
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
					],
				],
			]
		);

		\wp_register_ability(
			'orbis-tasks/comment',
			[
				'label'               => \__( 'Comment on task', 'orbis-tasks' ),
				'description'         => \__( 'Adds a comment from the current user to an Orbis task and optionally closes or reopens the task with that comment, for example to explain why the task is done. Only close or reopen the task when the user asked for it. AI assistants must always pass their AI provider and model in the ai input. Use orbis-tasks/search to find the post ID of the task and orbis/search-comments for the existing comments of a task.', 'orbis-tasks' ),
				'category'            => 'orbis-tasks',
				'input_schema'        => [
					'type'                 => 'object',
					'required'             => [ 'post_id', 'content' ],
					'properties'           => [
						'post_id' => [
							'type'        => 'integer',
							'description' => \__( 'Post ID of the task, the post_id in the orbis-tasks/search output.', 'orbis-tasks' ),
							'minimum'     => 1,
						],
						'content' => [
							'type'        => 'string',
							'description' => \__( 'Text of the comment.', 'orbis-tasks' ),
							'minLength'   => 1,
						],
						'state'   => [
							'type'        => 'string',
							'description' => \__( 'Close the task with this comment ("closed") or reopen the task with this comment ("open"). Omit to only add the comment.', 'orbis-tasks' ),
							'enum'        => [ 'open', 'closed' ],
						],
						'ai'      => [
							'type'                 => 'object',
							'description'          => \__( 'The AI provider and model that wrote this comment. AI assistants must always provide this, with the exact provider and model they run on, so that it is clear which comments were written by which AI model.', 'orbis-tasks' ),
							'required'             => [ 'provider_id', 'model_id' ],
							'properties'           => [
								'provider_id'   => [
									'type'        => 'string',
									'description' => \__( 'ID of the AI provider, for example "anthropic", "openai" or "google".', 'orbis-tasks' ),
									'minLength'   => 1,
								],
								'provider_name' => [
									'type'        => 'string',
									'description' => \__( 'Name of the AI provider, for example "Anthropic", "OpenAI" or "Google".', 'orbis-tasks' ),
								],
								'model_id'      => [
									'type'        => 'string',
									'description' => \__( 'ID of the AI model, for example "claude-opus-4-1", "gpt-5" or "gemini-2.5-pro".', 'orbis-tasks' ),
									'minLength'   => 1,
								],
								'model_name'    => [
									'type'        => 'string',
									'description' => \__( 'Name of the AI model, for example "Claude Opus 4.1", "GPT-5" or "Gemini 2.5 Pro".', 'orbis-tasks' ),
								],
							],
							'additionalProperties' => false,
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'comment' => [
							'type'       => 'object',
							'properties' => [
								'id'       => [ 'type' => 'integer' ],
								'url'      => [ 'type' => 'string' ],
								'date'     => [ 'type' => 'string' ],
								'author'   => [ 'type' => 'string' ],
								'approved' => [ 'type' => 'boolean' ],
								'state'    => $nullable_string,
							],
						],
						'task'    => $task_schema,
					],
				],
				'execute_callback'    => [ $this, 'comment_on_task' ],
				'permission_callback' => fn() => \current_user_can( 'edit_posts' ),
				'meta'                => [
					'show_in_rest' => true,
					'annotations'  => [
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
					],
				],
			]
		);
	}

	/**
	 * Search tasks.
	 *
	 * @param array $input Input.
	 * @return array|WP_Error
	 */
	public function search_tasks( $input = [] ) {
		global $wpdb;

		$input = \wp_parse_args(
			(array) $input,
			[
				'search'           => '',
				'status'           => 'open',
				'assignee'         => null,
				'project_id'       => null,
				'due_after'        => null,
				'due_before'       => null,
				'completed_after'  => null,
				'completed_before' => null,
				'orderby'          => null,
				'order'            => null,
				'per_page'         => 50,
				'page'             => 1,
			]
		);

		$conditions = [];

		$search = \trim( (string) $input['search'] );

		if ( '' !== $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';

			$search_conditions = [
				$wpdb->prepare( 'post.post_title LIKE %s', $like ),
				$wpdb->prepare( 'post.post_content LIKE %s', $like ),
			];

			if ( $this->has_projects() ) {
				$search_conditions[] = $wpdb->prepare( 'project.name LIKE %s', $like );
			}

			$conditions[] = '( ' . \implode( ' OR ', $search_conditions ) . ' )';
		}

		switch ( $input['status'] ) {
			case 'open':
				$conditions[] = 'NOT task.completed';

				break;
			case 'completed':
				$conditions[] = 'task.completed';

				break;
		}

		if ( null !== $input['assignee'] && '' !== $input['assignee'] ) {
			if ( 'none' === $input['assignee'] ) {
				$conditions[] = 'task.assignee_id IS NULL';
			} else {
				$user = $this->get_user( $input['assignee'] );

				if ( $user instanceof WP_Error ) {
					return $user;
				}

				$conditions[] = $wpdb->prepare( 'task.assignee_id = %d', $user->ID );
			}
		}

		if ( null !== $input['project_id'] ) {
			$conditions[] = $wpdb->prepare( 'task.project_id = %d', $input['project_id'] );
		}

		$date_conditions = [
			'due_after'        => 'task.due_at >= %s',
			'due_before'       => 'task.due_at < %s',
			'completed_after'  => 'task.completed_at >= %s',
			'completed_before' => 'task.completed_at < %s',
		];

		foreach ( $date_conditions as $key => $condition ) {
			$date = $this->parse_date( $input[ $key ], $key );

			if ( $date instanceof WP_Error ) {
				return $date;
			}

			if ( null === $date ) {
				continue;
			}

			// The before dates are inclusive, so compare with the start of the next day.
			if ( \str_ends_with( $key, '_before' ) ) {
				$date = $date->modify( '+1 day' );
			}

			$conditions[] = $wpdb->prepare( $condition, $date->format( 'Y-m-d H:i:s' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The condition is a fixed string.
		}

		$orderby = $input['orderby'];

		if ( null === $orderby ) {
			$orderby = ( 'completed' === $input['status'] ) ? 'completed_at' : 'due_date';
		}

		$order = $input['order'];

		if ( null === $order ) {
			$order = ( 'due_date' === $orderby ) ? 'asc' : 'desc';
		}

		$order = ( 'asc' === $order ) ? 'ASC' : 'DESC';

		switch ( $orderby ) {
			case 'completed_at':
				$order_sql = "task.completed_at IS NULL, task.completed_at $order, task.id $order";

				break;
			case 'created':
				$order_sql = "post.post_date $order, task.id $order";

				break;
			default:
				$order_sql = "task.due_at IS NULL, task.due_at $order, task.id $order";

				break;
		}

		$per_page = \max( 1, \min( 100, (int) $input['per_page'] ) );
		$page     = \max( 1, (int) $input['page'] );

		$result = $this->query_tasks( $conditions, $order_sql, $per_page, ( $page - 1 ) * $per_page );

		return [
			'today'    => \wp_date( 'Y-m-d' ),
			'total'    => $result['total'],
			'page'     => $page,
			'per_page' => $per_page,
			'tasks'    => $result['tasks'],
		];
	}

	/**
	 * Create tasks.
	 *
	 * @param array $input Input.
	 * @return array|WP_Error
	 */
	public function create_tasks( $input = [] ) {
		global $wpdb;

		$input = (array) $input;

		$items = \array_values( (array) ( $input['tasks'] ?? [] ) );

		if ( 0 === \count( $items ) ) {
			return new WP_Error( 'orbis_tasks_no_tasks', \__( 'No tasks to create.', 'orbis-tasks' ) );
		}

		// Validate all tasks before creating any task.
		$tasks = [];

		foreach ( $items as $index => $item ) {
			$item = (array) $item;

			$title = \trim( \sanitize_text_field( (string) ( $item['title'] ?? '' ) ) );

			if ( '' === $title ) {
				return $this->get_task_error( $index, new WP_Error( 'orbis_tasks_missing_title', \__( 'The title is required.', 'orbis-tasks' ) ) );
			}

			$task = new Task();

			$task->title = $title;
			$task->body  = \wp_kses_post( (string) ( $item['content'] ?? '' ) );

			if ( isset( $item['assignee'] ) && '' !== $item['assignee'] ) {
				$user = $this->get_user( $item['assignee'] );

				if ( $user instanceof WP_Error ) {
					return $this->get_task_error( $index, $user );
				}

				$task->assignee_id = $user->ID;
			}

			if ( isset( $item['project_id'] ) ) {
				$project_id = (int) $item['project_id'];

				if ( $this->has_projects() && null === $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_projects WHERE id = %d;", $project_id ) ) ) {
					return $this->get_task_error(
						$index,
						new WP_Error(
							'orbis_tasks_project_not_found',
							\sprintf(
								/* translators: %d: Orbis project ID. */
								\__( 'Orbis project %d not found.', 'orbis-tasks' ),
								$project_id
							)
						)
					);
				}

				$task->project_id = $project_id;
			}

			foreach ( [ 'due_date', 'start_date', 'end_date' ] as $key ) {
				$date = $this->parse_date( $item[ $key ] ?? null, $key );

				if ( $date instanceof WP_Error ) {
					return $this->get_task_error( $index, $date );
				}

				$task->$key = $date;
			}

			if ( isset( $item['time'] ) && '' !== \trim( (string) $item['time'] ) ) {
				$task->seconds = (int) \round( $this->parse_time( (string) $item['time'] ) );
			}

			$tasks[] = $task;
		}

		$post_ids = [];

		foreach ( $tasks as $index => $task ) {
			try {
				$this->plugin->save_task( $task );
			} catch ( \Exception $e ) {
				return new WP_Error(
					'orbis_tasks_create_failed',
					\sprintf(
						/* translators: 1: task number, 2: error message, 3: number of created tasks. */
						\__( 'Task %1$d could not be created: %2$s. %3$d tasks were created before this error.', 'orbis-tasks' ),
						$index + 1,
						$e->getMessage(),
						\count( $post_ids )
					),
					[
						'post_ids' => $post_ids,
					]
				);
			}

			$post_ids[] = (int) $task->post_id;
		}

		$placeholders = \implode( ', ', \array_fill( 0, \count( $post_ids ), '%d' ) );

		$result = $this->query_tasks(
			[
				$wpdb->prepare( "task.post_id IN ( $placeholders )", ...$post_ids ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Placeholders only.
			],
			'task.id ASC',
			\count( $post_ids ),
			0
		);

		return [
			'tasks' => $result['tasks'],
		];
	}

	/**
	 * Comment on task.
	 *
	 * @param array $input Input.
	 * @return array|WP_Error
	 */
	public function comment_on_task( $input = [] ) {
		global $wpdb;

		$input = (array) $input;

		$post_id = (int) ( $input['post_id'] ?? 0 );

		$post = \get_post( $post_id );

		if ( null === $post || 'orbis_task' !== $post->post_type || 'publish' !== $post->post_status ) {
			return new WP_Error(
				'orbis_tasks_task_not_found',
				\sprintf(
					/* translators: %d: post ID. */
					\__( 'Task with post ID %d not found.', 'orbis-tasks' ),
					$post_id
				)
			);
		}

		if ( ! \comments_open( $post ) ) {
			return new WP_Error( 'orbis_tasks_comments_closed', \__( 'Comments are closed for this task.', 'orbis-tasks' ) );
		}

		$content = \trim( (string) ( $input['content'] ?? '' ) );

		if ( '' === $content ) {
			return new WP_Error( 'orbis_tasks_missing_content', \__( 'The comment text is required.', 'orbis-tasks' ) );
		}

		$state = $input['state'] ?? null;

		if ( null !== $state && ! \in_array( $state, [ 'open', 'closed' ], true ) ) {
			return new WP_Error( 'orbis_tasks_invalid_state', \__( 'The state must be "open" or "closed".', 'orbis-tasks' ) );
		}

		$user = \wp_get_current_user();

		$comment_meta = [
			'_orbis_task_ability_name' => 'orbis-tasks/comment',
		];

		$ai = (array) ( $input['ai'] ?? [] );

		$ai_meta_keys = [
			'provider_id'   => '_orbis_task_ai_provider_id',
			'provider_name' => '_orbis_task_ai_provider_name',
			'model_id'      => '_orbis_task_ai_model_id',
			'model_name'    => '_orbis_task_ai_model_name',
		];

		foreach ( $ai_meta_keys as $key => $meta_key ) {
			$value = \sanitize_text_field( (string) ( $ai[ $key ] ?? '' ) );

			if ( '' !== $value ) {
				$comment_meta[ $meta_key ] = $value;
			}
		}

		/**
		 * Use `wp_new_comment()` instead of `wp_insert_comment()`, so that
		 * comments added via this ability are handled like comments from
		 * the comment form, including filters and notifications.
		 *
		 * The comment meta is passed to `wp_insert_comment()`, so that it is
		 * available before the `comment_post` action is triggered.
		 */
		$comment_id = \wp_new_comment(
			\wp_slash(
				[
					'comment_post_ID'      => $post->ID,
					'comment_content'      => $content,
					'comment_type'         => 'comment',
					'comment_parent'       => 0,
					'user_id'              => $user->ID,
					'comment_author'       => $user->display_name,
					'comment_author_email' => $user->user_email,
					'comment_author_url'   => $user->user_url,
					'comment_meta'         => $comment_meta,
				]
			),
			true
		);

		if ( $comment_id instanceof WP_Error ) {
			return $comment_id;
		}

		if ( false === $comment_id ) {
			return new WP_Error( 'orbis_tasks_comment_failed', \__( 'The comment could not be added.', 'orbis-tasks' ) );
		}

		$comment = \get_comment( $comment_id );

		$approved = ( '1' === (string) $comment->comment_approved );

		// Like the comment form, only update the task state with an approved comment.
		if ( null !== $state && $approved ) {
			try {
				$this->plugin->update_task_state_by_comment( $comment_id, $state );
			} catch ( \Exception $e ) {
				return new WP_Error(
					'orbis_tasks_update_state_failed',
					\sprintf(
						/* translators: %s: error message. */
						\__( 'The comment was added, but the task state could not be updated: %s', 'orbis-tasks' ),
						$e->getMessage()
					)
				);
			}
		}

		$comment_state = \get_comment_meta( $comment->comment_ID, '_orbis_task_update_state', true );

		$result = $this->query_tasks(
			[
				$wpdb->prepare( 'task.post_id = %d', $post->ID ),
			],
			'task.id ASC',
			1,
			0
		);

		return [
			'comment' => [
				'id'       => (int) $comment->comment_ID,
				'url'      => (string) \get_comment_link( $comment ),
				'date'     => $comment->comment_date,
				'author'   => $comment->comment_author,
				'approved' => $approved,
				'state'    => ( '' === $comment_state ) ? null : $comment_state,
			],
			'task'    => $result['tasks'][0] ?? null,
		];
	}

	/**
	 * Query tasks.
	 *
	 * @param string[] $conditions Prepared SQL conditions.
	 * @param string   $order_sql  SQL order by clause.
	 * @param int      $limit      Limit.
	 * @param int      $offset     Offset.
	 * @return array{total: int, tasks: array}
	 */
	private function query_tasks( $conditions, $order_sql, $limit, $offset ) {
		global $wpdb;

		$fields = '
			task.id,
			task.post_id,
			task.project_id,
			task.assignee_id,
			task.due_at,
			task.completed,
			task.completed_at,
			post.post_title,
			post.post_content,
			post.post_author,
			post.post_date,
			post.comment_count,
			assignee.display_name AS assignee_name,
			author.display_name AS author_name
		';

		$join = "
			$wpdb->orbis_tasks AS task
				INNER JOIN
			$wpdb->posts AS post
					ON task.post_id = post.ID
				LEFT JOIN
			$wpdb->users AS assignee
					ON task.assignee_id = assignee.ID
				LEFT JOIN
			$wpdb->users AS author
					ON post.post_author = author.ID
		";

		if ( $this->has_projects() ) {
			$fields .= ',
				project.post_id AS project_post_id,
				project.name AS project_name
			';

			$join .= "
				LEFT JOIN
			$wpdb->orbis_projects AS project
					ON task.project_id = project.id
			";
		}

		$conditions = \array_merge(
			[
				"post.post_type = 'orbis_task'",
				"post.post_status = 'publish'",
			],
			$conditions
		);

		$where = \implode( ' AND ', $conditions );

		$total = (int) $wpdb->get_var( "SELECT COUNT( task.id ) FROM $join WHERE $where;" );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT $fields FROM $join WHERE $where ORDER BY $order_sql LIMIT %d OFFSET %d;",
				$limit,
				$offset
			)
		);

		$post_ids = \array_map( fn( $row ) => (int) $row->post_id, $results );

		\_prime_post_caches( $post_ids, false, true );

		return [
			'total' => $total,
			'tasks' => \array_map( [ $this, 'format_task' ], $results ),
		];
	}

	/**
	 * Format task row for ability output.
	 *
	 * @param object $row Database row.
	 * @return array
	 */
	private function format_task( $row ) {
		$post_id = (int) $row->post_id;

		$task = Task::from_post( \get_post( $post_id ) );

		$assignee = null;

		if ( null !== $row->assignee_id && null !== $row->assignee_name ) {
			$assignee = [
				'id'   => (int) $row->assignee_id,
				'name' => $row->assignee_name,
			];
		}

		$author = null;

		if ( null !== $row->author_name ) {
			$author = [
				'id'   => (int) $row->post_author,
				'name' => $row->author_name,
			];
		}

		$project = null;

		if ( null !== $row->project_id && isset( $row->project_name ) ) {
			$project_post_id = null === $row->project_post_id ? null : (int) $row->project_post_id;

			$project = [
				'id'      => (int) $row->project_id,
				'post_id' => $project_post_id,
				'name'    => $row->project_name,
				'url'     => null === $project_post_id ? null : (string) \get_permalink( $project_post_id ),
			];
		}

		return [
			'id'            => (int) $row->id,
			'post_id'       => $post_id,
			'title'         => $row->post_title,
			'url'           => (string) \get_permalink( $post_id ),
			'content'       => \wp_html_excerpt( \wp_strip_all_tags( $row->post_content ), 500, '…' ),
			'status'        => $row->completed ? 'completed' : 'open',
			'due_date'      => null === $row->due_at ? null : \substr( $row->due_at, 0, 10 ),
			'start_date'    => null === $task->start_date ? null : $task->start_date->format( 'Y-m-d' ),
			'end_date'      => null === $task->end_date ? null : $task->end_date->format( 'Y-m-d' ),
			'completed_at'  => $row->completed_at,
			'seconds'       => null === $task->seconds ? null : (int) $task->seconds,
			'assignee'      => $assignee,
			'author'        => $author,
			'project'       => $project,
			'created'       => $row->post_date,
			'comment_count' => (int) $row->comment_count,
		];
	}

	/**
	 * Check if the Orbis projects table is available.
	 *
	 * @return bool
	 */
	private function has_projects() {
		global $wpdb;

		return ! empty( $wpdb->orbis_projects );
	}

	/**
	 * Get user by "me", ID, login, email address or display name.
	 *
	 * @param mixed $value Value.
	 * @return WP_User|WP_Error
	 */
	private function get_user( $value ) {
		$value = \trim( (string) $value );

		$user = false;

		if ( 'me' === \strtolower( $value ) ) {
			$user = \wp_get_current_user();
		} elseif ( \ctype_digit( $value ) ) {
			$user = \get_user_by( 'id', (int) $value );
		} else {
			$user = \get_user_by( 'login', $value );

			if ( false === $user && \is_email( $value ) ) {
				$user = \get_user_by( 'email', $value );
			}

			if ( false === $user ) {
				$users = \get_users(
					[
						'search'         => $value,
						'search_columns' => [ 'display_name' ],
						'number'         => 2,
					]
				);

				if ( 1 === \count( $users ) ) {
					$user = \reset( $users );
				}
			}
		}

		if ( ! $user instanceof WP_User || 0 === $user->ID ) {
			return new WP_Error(
				'orbis_tasks_user_not_found',
				\sprintf(
					/* translators: %s: user search value. */
					\__( 'No unique user found for "%s". Use a user ID, login or email address.', 'orbis-tasks' ),
					$value
				)
			);
		}

		return $user;
	}

	/**
	 * Parse a date or relative date in the site timezone.
	 *
	 * @param mixed  $value Value.
	 * @param string $name  Input name.
	 * @return DateTimeImmutable|WP_Error|null
	 */
	private function parse_date( $value, $name ) {
		if ( null === $value || '' === \trim( (string) $value ) ) {
			return null;
		}

		try {
			$date = new DateTimeImmutable( (string) $value, \wp_timezone() );
		} catch ( \Exception $e ) {
			return new WP_Error(
				'orbis_tasks_invalid_date',
				\sprintf(
					/* translators: 1: input name, 2: input value. */
					\__( 'Invalid date for %1$s: "%2$s".', 'orbis-tasks' ),
					$name,
					$value
				)
			);
		}

		return $date->setTime( 0, 0 );
	}

	/**
	 * Parse time in hours, for example "1.5" or "1:30", to seconds.
	 *
	 * @param string $value Value.
	 * @return float|int
	 */
	private function parse_time( $value ) {
		if ( \function_exists( 'orbis_parse_time' ) ) {
			return \orbis_parse_time( $value );
		}

		return (float) \str_replace( ',', '.', $value ) * 3600;
	}

	/**
	 * Get error for a task in the list of tasks to create.
	 *
	 * @param int      $index Index.
	 * @param WP_Error $error Error.
	 * @return WP_Error
	 */
	private function get_task_error( $index, WP_Error $error ) {
		return new WP_Error(
			$error->get_error_code(),
			\sprintf(
				/* translators: 1: task number, 2: error message. */
				\__( 'Task %1$d: %2$s No tasks were created.', 'orbis-tasks' ),
				$index + 1,
				$error->get_error_message()
			)
		);
	}
}
