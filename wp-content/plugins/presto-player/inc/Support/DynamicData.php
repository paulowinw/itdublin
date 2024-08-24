<?php

namespace PrestoPlayer\Support;

class DynamicData {

	/**
	 * Get values to replace.
	 *
	 * @return array
	 */
	public static function getValues() {
		$current_user = wp_get_current_user();

		return apply_filters(
			'presto-player/dynamic-data',
			array(
				'{user.user_login}'      => $current_user->user_login ?? '',
				'{user.user_nicename}'   => $current_user->user_nicename ?? '',
				'{user.user_email}'      => $current_user->user_email ?? '',
				'{user.user_url}'        => $current_user->user_url ?? '',
				'{user.user_registered}' => $current_user->user_registered ?? '',
				'{user.display_name}'    => $current_user->display_name ?? '',
				'{site.url}'             => get_home_url(),
				'{site.name}'            => get_bloginfo(),
				'{ip_address}'           => self::getIP(),
			)
		);
	}

	/**
	 * Replace dynamic data with actual data.
	 *
	 * @param  array $items Array of items with ['text'].
	 * @return array
	 */
	public static function replaceItems( $items, $key ) {
		foreach ( $items as $k => $item ) {
			$items[ $k ][ $key ] = self::replaceText( $item[ $key ] );
		}

		return $items;
	}

	/**
	 * Replace value in string with dynamic data.
	 *
	 * @param string $text String with dynamic data.
	 * @return string
	 */
	public static function replaceText( $text ) {
		return wp_kses_post( strtr( $text, self::getValues() ) );
	}

	/**
	 * Get the person's IP.
	 *
	 * @return string
	 */
	public static function getIP() {
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					$ip = trim( $ip ); // just to be safe

					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
	}
}
