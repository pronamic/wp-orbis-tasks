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
	 * Due date.
	 * 
	 * @var DateTimeInterface|null
	 */
	public $due_date;

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
		return (object) [
			'id'          => $this->id,
			'title'       => $this->title,
			'body'        => $this->body,
			'post_id'     => $this->post_id,
			'project_id'  => $this->project_id,
			'assignee_id' => $this->assignee_id,
			'due_date'    => null === $this->due_date ? null : $this->due_date->format( 'Y-m-d' ),
			'start_date'  => null === $this->start_date ? null : $this->start_date->format( 'Y-m-d' ),
			'end_date'    => null === $this->end_date ? null : $this->end_date->format( 'Y-m-d' ),
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

		$task->post_id = \get_post_field( 'ID', $post );
		$task->title   = \get_post_field( 'post_title', $post );
		$task->body    = \get_post_field( 'post_content', $post );

		$json = \get_post_meta( $post->ID, '_orbis_task_json', true );

		$object = \json_decode( $json );

		if ( \is_object( $object ) ) {
			$task = self::from_object( $object, $task );
		}

		if ( null === $task->project_id ) {
			$meta_value = \get_post_meta( $post->ID, '_orbis_task_project_id', true );

			$task->project_id = ( '' === $meta_value ) ? null : $meta_value;
		}

		if ( null === $task->assignee_id ) {
			$meta_value = \get_post_meta( $post->ID, '_orbis_task_assignee_id', true );

			$task->assignee_id = ( '' === $meta_value ) ? null : $meta_value;
		}

		$due_at_string = \get_post_meta( $post->ID, '_orbis_task_due_at', true );

		$due_at = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $due_at_string, \wp_timezone() );

		if ( null === $task->due_date && false !== $due_at ) {
			$task->due_date = $due_at;
		}

		if ( null === $task->seconds ) {
			$meta_value = \get_post_meta( $post->ID, '_orbis_task_seconds', true );

			$task->seconds = ( '' === $meta_value ) ? null : $meta_value;
		}

		if ( '1' === \get_post_meta( $post->ID, '_orbis_task_completed', true ) ) {
			$task->completed = true;
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

		if ( \property_exists( $data, 'id' ) ) {
			$task->id = $data->id;
		}

		if ( \property_exists( $data, 'project_id' ) ) {
			$task->project_id = $data->project_id;
		}

		if ( \property_exists( $data, 'assignee_id' ) ) {
			$task->assignee_id = $data->assignee_id;
		}

		if ( \property_exists( $data, 'due_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $data->due_date );

			$task->due_date = ( false === $value ) ? null : $value->setTime( 0, 0 );
		}

		if ( \property_exists( $data, 'start_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $data->start_date );

			$task->start_date = ( false === $value ) ? null : $value->setTime( 0, 0 );
		}

		if ( \property_exists( $data, 'end_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $data->end_date );

			$task->end_date = ( false === $value ) ? null : $value->setTime( 0, 0 );
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
