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

use DateTimeInterface;
use JsonSerializable;
use Pronamic\WordPress\DateTime\DateTimeImmutable;
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
	 * Assignee ID.
	 * 
	 * @var int|null
	 */
	public $assignee_id;

	/**
	 * Creation date.
	 * 
	 * @var DateTimeInterface|null
	 */
	public $creation_date;

	/**
	 * Due date modifier.
	 * 
	 * @var string
	 */
	public $due_date_modifier = '';

	/**
	 * Start date modifier.
	 * 
	 * @var string
	 */
	public $start_date_modifier = '';

	/**
	 * End date modifier.
	 * 
	 * @var string
	 */
	public $end_date_modifier = '';

	/**
	 * Create date modifier.
	 * 
	 * @var string
	 */
	public $creation_date_modifier = '';

	/**
	 * Seconds.
	 * 
	 * @var int|null
	 */
	public $seconds;

	/**
	 * New task.
	 * 
	 * @param DateTimeInterface|null $creation_date Creation date.
	 * @return Task
	 * @throws \Exception Throws an exception if creating a new task fails.
	 */
	public function new_task( $creation_date ) {
		if ( null === $creation_date ) {
			$creation_date = $this->creation_date;
		}

		if ( null === $creation_date ) {
			throw new \Exception( 'Task template creation date is not defined.' );
		}

		$task = new Task();

		$task->assignee_id = $this->assignee_id;

		$date = DateTimeImmutable::create_from_interface( $this->creation_date );

		$task->due_date   = $date->modify( $this->due_date_modifier );
		$task->start_date = $date->modify( $this->start_date_modifier );
		$task->end_date   = $date->modify( $this->end_date_modifier );

		$task->seconds = $this->seconds;

		$task->title = $this->replace_merge_tags( $this->title, $task );
		$task->body  = $this->replace_merge_tags( $this->body, $task );

		return $task;
	}

	/**
	 * Replace merge tags.
	 * 
	 * @param string $text Text.
	 * @param Task   $task Task.
	 * @return string
	 */
	private function replace_merge_tags( $text, $task ) {
		$start_date_month   = '';
		$start_date_year    = '';
		$start_date_quarter = '';
		$start_date_week    = '';

		if ( null !== $task->start_date ) {
			$start_date_month   = \wp_date( 'F', $task->start_date->getTimestamp() );
			$start_date_year    = \wp_date( 'Y', $task->start_date->getTimestamp() );
			$start_date_quarter = \ceil( $task->start_date->format( 'n' ) / 3 );
			$start_date_week    = \ltrim( $task->start_date->format( 'W' ), '0' );
		}

		$end_date_month   = '';
		$end_date_year    = '';
		$end_date_quarter = '';
		$end_date_week    = '';

		if ( null !== $task->end_date ) {
			$end_date_month   = \wp_date( 'F', $task->end_date->getTimestamp() );
			$end_date_year    = \wp_date( 'Y', $task->end_date->getTimestamp() );
			$end_date_quarter = \ceil( $task->end_date->format( 'n' ) / 3 );
			$end_date_week    = \ltrim( $task->end_date->format( 'W' ), '0' );
		}

		$replace_pairs = [
			'{start_date_month}'   => $start_date_month,
			'{start_date_year}'    => $start_date_year,
			'{start_date_quarter}' => $start_date_quarter,
			'{start_date_week}'    => $start_date_week,
			'{end_date_month}'     => $end_date_month,
			'{end_date_year}'      => $end_date_year,
			'{end_date_quarter}'   => $end_date_quarter,
			'{end_date_week}'      => $end_date_week,
		];

		$text = \strtr( $text, $replace_pairs );

		return $text;
	}

	/**
	 * Modify creation date.
	 * 
	 * @return void
	 */
	public function modify_creation_date() {
		if ( null === $this->creation_date ) {
			return;
		}

		$date = DateTimeImmutable::create_from_interface( $this->creation_date );

		$this->creation_date = $date->modify( $this->creation_date_modifier );
	}

	/**
	 * JSON serialize.
	 * 
	 * @return mixed
	 */
	public function jsonSerialize() {
		return (object) [
			'post_id'                => $this->post_id,
			'title'                  => $this->title,
			'body'                   => $this->body,
			'assignee_id'            => $this->assignee_id,
			'creation_date'          => null === $this->creation_date ? null : $this->creation_date->format( 'Y-m-d' ),
			'due_date_modifier'      => $this->due_date_modifier,
			'start_date_modifier'    => $this->start_date_modifier,
			'end_date_modifier'      => $this->end_date_modifier,
			'creation_date_modifier' => $this->creation_date_modifier,
			'seconds'                => $this->seconds,
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

		$task_template->post_id = \get_post_field( 'ID', $post );
		$task_template->title   = \get_post_field( 'post_title', $post );
		$task_template->body    = \get_post_field( 'post_content', $post );

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

		if ( \property_exists( $data, 'assignee_id' ) ) {
			$task_template->assignee_id = $data->assignee_id;
		}

		if ( \property_exists( $data, 'creation_date' ) ) {
			$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $data->creation_date );

			$task_template->creation_date = ( false === $value ) ? null : $value->setTime( 0, 0 );
		}

		if ( \property_exists( $data, 'due_date_modifier' ) ) {
			$task_template->due_date_modifier = $data->due_date_modifier;
		}

		if ( \property_exists( $data, 'start_date_modifier' ) ) {
			$task_template->start_date_modifier = $data->start_date_modifier;
		}

		if ( \property_exists( $data, 'end_date_modifier' ) ) {
			$task_template->end_date_modifier = $data->end_date_modifier;
		}

		if ( \property_exists( $data, 'creation_date_modifier' ) ) {
			$task_template->creation_date_modifier = $data->creation_date_modifier;
		}

		if ( \property_exists( $data, 'seconds' ) ) {
			$task_template->seconds = $data->seconds;
		}

		return $task_template;
	}
}
