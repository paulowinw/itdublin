<?php

namespace PrestoPlayer\Models;

class Setting {

	const PREFIX = 'presto_player';

	public static function getGroupName( $group ) {
		return self::PREFIX . "_{$group}";
	}

	/**
	 * Get the option group
	 *
	 * @param string $group
	 * @return mixed
	 */
	public static function getGroup( $group ) {
		return \get_option( self::getGroupName( $group ) );
	}

	/**
	 * Get an individual option from the group
	 *
	 * @param string $group Group name.
	 * @param string $name Field name.
	 * @param string $default Default value if nothing is found.
	 *
	 * @return mixed
	 */
	public static function get( $group, $name = '', $default = null ) {
		$option = self::getGroup( $group );

		if ( ! $name ) {
			return $option;
		}
		return isset( $option[ $name ] ) ? $option[ $name ] : $default;
	}

	/**
	 * Set an individual option
	 *
	 * @param string $group Group name
	 * @param string $name Field name
	 * @param mixed  $value Field value
	 *
	 * @return boolean Whether the option updated
	 */
	public static function set( $group, $name, $value ) {
		// get stored group
		$stored          = (array) self::getGroup( $group );
		$stored          = array_filter(
			$stored,
			function ( $key ) {
				return is_string( $key );
			},
			ARRAY_FILTER_USE_KEY
		);
		$stored[ $name ] = $value;
		return \update_option( self::getGroupName( $group ), $stored );
	}

	public static function update( $group, $name, $value ) {
		// get stored group
		$stored          = (array) self::getGroup( $group );
		$stored          = array_filter(
			$stored,
			function ( $key ) {
				return is_string( $key );
			},
			ARRAY_FILTER_USE_KEY
		);
		$stored[ $name ] = $value;
		return \update_option( self::getGroupName( $group ), $stored );
	}

	/**
	 * Delete an option
	 *
	 * @param string $group
	 * @param string $name
	 * @return boolean
	 */
	public static function delete( $group, $name ) {
		$stored = (array) self::getGroup( $group );
		unset( $stored[ $name ] );
		return \update_option( self::getGroupName( $group ), $stored );
	}

	public static function deleteAll( $group ) {
		delete_option( self::getGroupName( $group ) );
	}

	public static function getDefaultColor() {
		return apply_filters( 'presto_player_default_color', '#00b3ff' );
	}
}
