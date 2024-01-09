<?php
/**
 * Task
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

namespace Pronamic\Orbis\Tasks;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use WP_Post;

/**
 * Task class
 */
class Task implements JsonSerializable {
	/**
	 * ID.
	 * 
	 * @var int|null
	 */
	public $id;

	/**
	 * Post ID.
	 * 
	 * @var int|null
	 */
	public $post_id;

	/**
	 * Title.
	 * 
	 * @var string|null
	 */
	public $title;

	/**
	 * Body.
	 * 
	 * @var string|null
	 */
	public $body;

	/**
	 * Project ID.
	 * 
	 * @var int|null
	 */
	public $project_id;

	/**
	 * Assignee ID.
	 * 
	 * @var int|null
	 */
	public $assignee_id;

	/**
	 * Start date.
	 * 
	 * @var DateTimeInterface|null
	 */
	public $start_date;

	/**
	 * End date.
	 * 
	 * @var DateTimeInterface|null
	 */
	public $end_date;

	/**
	 * Seconds.
	 * 
	 * @var int|null
	 */
	public $seconds;

	/**
	 * Completed.
	 * 
	 * @var bool
	 */
	public $completed = false;

	/**
	 * JSON serialize.
	 * 
	 * @return mixed
	 */
	public function jsonSerialize() {
		return [
			'id'          => $this->id,
			'post_id'     => $this->post_id,
			'project_id'  => $this->project_id,
			'assignee_id' => $this->assignee_id,
			'start_date'  => null === $this->start_date ? null : $this->start_date->format( \DATE_ATOM ),
			'end_date'    => null === $this->end_date ? null : $this->end_date->format( \DATE_ATOM ),
			'seconds'     => $this->seconds,
			'completed'   => $this->completed,
		];
	}

	/**
	 * Create task from WordPress post.
	 * 
	 * @param WP_Post   $post WordPress post object.
	 * @param Task|null $task Task.
	 * @return self
	 */
	public static function from_post( WP_Post $post, $task = null ) {
		$task = ( null === $task ) ? new self() : $task;

		$task->post_id = $post->ID;

		$json = \get_post_meta( $post->ID, '_orbis_task_json', true );

		$object = \json_decode( $json );

		if ( \is_object( $object ) ) {
			$task = self::from_object( $object, $task );
		}

		if ( null === $task->project_id ) {
			$task->project_id = \get_post_meta( $post->ID, '_orbis_task_project_id', true );
		}

		if ( null === $task->assignee_id ) {
			$task->assignee_id = \get_post_meta( $post->ID, '_orbis_task_assignee_id', true );
		}

		$due_at_string = \get_post_meta( $post->ID, '_orbis_task_due_at_string', true );

		$due_at = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $due_at_string, \wp_timezone() );

		if ( null === $task->start_date && false !== $due_at ) {
			$task->start_date = $due_at;
		}

		if ( null === $task->end_date && false !== $due_at ) {
			$task->end_date = $due_at;
		}

		if ( null === $task->seconds ) {
			$task->seconds = \get_post_meta( $post->ID, '_orbis_task_seconds', true );
		}

		if ( null === $task->completed ) {
			$task->completed = \get_post_meta( $post->ID, '_orbis_task_completed', true );
		}

		return $task;
	}

	/**
	 * Create task from object.
	 * 
	 * @param object    $data Object.
	 * @param Task|null $task Task.
	 * @return self
	 */
	public static function from_object( $data, $task = null ) {
		$task = ( null === $task ) ? new self() : $task;

		if ( \property_exists( $data, 'project_id' ) ) {
			$task->project_id = $data->project_id;
		}

		if ( \property_exists( $data, 'assignee_id' ) ) {
			$task->assignee_id = $data->assignee_id;
		}

		if ( \property_exists( $data, 'start_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( \DATE_ATOM, $data->start_date );

			$task->start_date = ( false === $value ) ? null : $value;
		}

		if ( \property_exists( $data, 'end_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( \DATE_ATOM, $data->end_date );

			$task->end_date = ( false === $value ) ? null : $value;
		}

		if ( \property_exists( $data, 'seconds' ) ) {
			$task->seconds = $data->seconds;
		}

		if ( \property_exists( $data, 'completed' ) ) {
			$task->completed = $data->completed;
		}

		return $task;
	}
}
