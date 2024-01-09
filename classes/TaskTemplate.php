<?php
/**
 * Task template
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
 * Task template class
 */
class TaskTemplate implements JsonSerializable {
	/**
	 * Post ID.
	 * 
	 * @var int|null
	 */
	public $post_id;

	/**
	 * Interval
	 * 
	 * @var string|null
	 */
	public $interval;

	/**
	 * Creation date.
	 * 
	 * @var DateTimeInterface|null
	 */
	public $creation_date;

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
	 * Next creation date.
	 * 
	 * @var DateTimeInterface|null
	 */
	public $next_creation_date;

	/**
	 * JSON serialize.
	 * 
	 * @return mixed
	 */
	public function jsonSerialize() {
		return [
			'post_id'            => $this->post_id,
			'interval'           => $this->interval,
			'creation_date'      => null === $this->creation_date ? null : $this->creation_date->format( \DATE_ATOM ),
			'start_date'         => null === $this->start_date ? null : $this->start_date->format( \DATE_ATOM ),
			'end_date'           => null === $this->end_date ? null : $this->end_date->format( \DATE_ATOM ),
			'next_creation_date' => null === $this->next_creation_date ? null : $this->next_creation_date->format( \DATE_ATOM ),
		];
	}

	/**
	 * Create task template from WordPress post.
	 * 
	 * @param WP_Post   $post          WordPress post object.
	 * @param Task|null $task_template Task template.
	 * @return self
	 */
	public static function from_post( WP_Post $post, $task_template = null ) {
		$task_template = ( null === $task_template ) ? new self() : $task_template;

		$task_template->post_id = $post->ID;

		$json = \get_post_meta( $post->ID, '_orbis_task_template_json', true );

		$object = \json_decode( $json );

		if ( \is_object( $object ) ) {
			$task_template = self::from_object( $object, $task_template );
		}

		return $task_template;
	}

	/**
	 * Create task template from object.
	 * 
	 * @param object    $data          Object.
	 * @param Task|null $task_template Task template.
	 * @return self
	 */
	public static function from_object( $data, $task_template = null ) {
		$task_template = ( null === $task_template ) ? new self() : $task_template;

		if ( \property_exists( $data, 'interval' ) ) {
			$task_template->interval = $data->interval;
		}

		if ( \property_exists( $data, 'creation_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( \DATE_ATOM, $data->creation_date );

			$task_template->creation_date = ( false === $value ) ? null : $value;
		}

		if ( \property_exists( $data, 'start_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( \DATE_ATOM, $data->start_date );

			$task_template->start_date = ( false === $value ) ? null : $value;
		}

		if ( \property_exists( $data, 'end_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( \DATE_ATOM, $data->end_date );

			$task_template->end_date = ( false === $value ) ? null : $value;
		}

		if ( \property_exists( $data, 'next_creation_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( \DATE_ATOM, $data->next_creation_date );

			$task_template->next_creation_date = ( false === $value ) ? null : $value;
		}

		return $task_template;
	}
}
