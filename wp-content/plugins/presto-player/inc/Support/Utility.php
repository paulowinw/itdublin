<?php

namespace PrestoPlayer\Support;

class Utility {

	public static function sanitizeCSS( $css ) {
		return preg_match( '#</?\w+#', $css ) ? '' : $css;
	}

	public static function insertAfterString( $str, $search, $insert ) {
		$index = strpos( $str, $search );
		if ( $index === false ) {
			return $str;
		}
		return substr_replace( $str, $search . $insert, $index, strlen( $search ) );
	}

	public static function snakeToCamel( $input ) {
		return lcfirst( str_replace( ' ', '', ucwords( str_replace( '_', ' ', $input ) ) ) );
	}

	/**
	 * Convert a duration to human readable format.
	 *
	 * @since 5.1.0
	 *
	 * @param string $duration Duration will be in string format (HH:ii:ss) OR (ii:ss),
	 *                         with a possible prepended negative sign (-).
	 * @return string|false A human readable duration string, false on failure.
	 */
	public static function human_readable_duration( $duration = '' ) {
		if ( ( empty( $duration ) || ! is_string( $duration ) ) ) {
			return __( '0 seconds', 'presto-player' );
		}

		$duration = trim( $duration );

		// Remove prepended negative sign.
		if ( '-' === substr( $duration, 0, 1 ) ) {
			$duration = substr( $duration, 1 );
		}

		// Extract duration parts.
		$duration_parts = array_reverse( explode( ':', $duration ) );
		$duration_count = count( $duration_parts );

		$hour   = null;
		$minute = null;
		$second = null;

		if ( 3 === $duration_count ) {
			// Validate HH:ii:ss duration format.
			if ( ! ( (bool) preg_match( '/^([0-9]+):([0-5]?[0-9]):([0-5]?[0-9])$/', $duration ) ) ) {
				return false;
			}
			// Three parts: hours, minutes & seconds.
			list($second, $minute, $hour) = $duration_parts;
		} elseif ( 2 === $duration_count ) {
			// Validate ii:ss duration format.
			if ( ! ( (bool) preg_match( '/^([0-5]?[0-9]):([0-5]?[0-9])$/', $duration ) ) ) {
				return false;
			}
			// Two parts: minutes & seconds.
			list($second, $minute) = $duration_parts;
		} else {
			return false;
		}

		$human_readable_duration = array();

		// Add the hour part to the string.
		if ( is_numeric( $hour ) && $hour > 0 ) {
			/* translators: %s: Time duration in hour or hours. */
			$human_readable_duration[] = sprintf( _n( '%s hour', '%s hours', $hour ), (int) $hour );
		}

		// Add the minute part to the string.
		if ( is_numeric( $minute ) && $minute > 0 ) {
			/* translators: %s: Time duration in minute or minutes. */
			$human_readable_duration[] = sprintf( _n( '%s minute', '%s minutes', $minute ), (int) $minute );
		}

		// Add the second part to the string.
		if ( is_numeric( $second ) && $second > 0 ) {
			/* translators: %s: Time duration in second or seconds. */
			$human_readable_duration[] = sprintf( _n( '%s second', '%s seconds', $second ), (int) $second );
		}

		return implode( ', ', $human_readable_duration );
	}

	public static function getIPAddress( $ip_address = '' ) {
		$ip = $ip_address ? $ip_address : ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' );

		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		} else {
			return '';
		}
	}

	/**
	 * Insert an array into another array before/after a certain key
	 *
	 * @param array  $array The initial array
	 * @param array  $pairs The array to insert
	 * @param string $key The certain key
	 * @param string $position Wether to insert the array before or after the key
	 * @return array
	 */
	public static function arrayInsert( $array, $pairs, $key, $position = 'after' ) {
		$key_pos = array_search( $key, array_keys( $array ) );

		if ( 'after' == $position ) {
			++$key_pos;
		}

		if ( false !== $key_pos ) {
			$result = array_slice( $array, 0, $key_pos );
			$result = array_merge( $result, $pairs );
			$result = array_merge( $result, array_slice( $array, $key_pos ) );
		} else {
			$result = array_merge( $array, $pairs );
		}

		return $result;
	}

	/*
	* Inserts a new key/value before the key in the array.
	*
	* @param $key The key to insert before.
	* @param $array An array to insert in to.
	* @param $new_key The key to insert.
	* @param $new_value An value to insert.
	*
	* @return The new array if the key exists, FALSE otherwise.
	*
	*/
	public static function arrayInsertBefore( $key, array &$array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
				$new[ $k ] = $value;
			}
			return $new;
		}
		return false;
	}

	/*
	* Inserts a new key/value after the key in the array.
	*
	* @param $key The key to insert after.
	* @param $array An array to insert in to.
	* @param $new_key The key to insert.
	* @param $new_value An value to insert.
	*
	* @return The new array if the key exists, FALSE otherwise.
	*/
	public static function arrayInsertAfter( $key, array &$array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				$new[ $k ] = $value;
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
			}
			return $new;
		}
		return false;
	}

	public static function hex2rgba( $color, $opacity = false ) {

		$defaultColor = 'rgb(0,0,0)';

		// Return default color if no color provided
		if ( empty( $color ) ) {
			return $defaultColor;
		}

		// Ignore "#" if provided
		if ( $color[0] == '#' ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters, get values
		if ( strlen( $color ) == 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		// Convert hex values to rgb values
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb)
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		// Return rgb(a) color string
		return $output;
	}
}
