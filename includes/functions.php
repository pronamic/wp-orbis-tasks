<?php
/**
 * Functions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Tasks
 */

if ( ! function_exists( 'orbis_time' ) ) {
	/**
	 * Format time
	 *
	 * @param int    $seconds Seconds.
	 * @param string $format  Format.
	 * @return mixed
	 */
	function orbis_time( $seconds, $format = 'HH:MM' ) {
		// @see http://stackoverflow.com/a/3856312
		if ( ! is_numeric( $seconds ) ) {
			return false;
		}

		$hours   = floor( $seconds / 3600 );
		$minutes = floor( ( $seconds - ( $hours * 3600 ) ) / 60 );
		$seconds = floor( $seconds % 60 );

		$search = [
			'HH',
			'H',
			'MM',
			'M',
			'SS',
			'S',
		];

		$replace = [
			sprintf( '%02d', $hours ),
			$hours,
			sprintf( '%02d', $minutes ),
			$minutes,
			sprintf( '%02d', $seconds ),
			$seconds,
		];

		return str_replace( $search, $replace, $format );
	}
}
