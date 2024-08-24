<?php

namespace PrestoPlayer\Models;

use PrestoPlayer\Models\Setting;

class LicensedProduct {

	/**
	 * The api url
	 *
	 * @return string
	 */
	public static function apiUrl() {
		return 'http://my.prestomade.com/index.php';
	}

	/**
	 * The product id
	 *
	 * @return string
	 */
	public static function id() {
		return 'presto-player-pro';
	}

	/**
	 * This domain
	 *
	 * @return string
	 */
	public static function domain() {
		$protocol = is_ssl() ? 'https://' : 'http://';
		return str_replace( $protocol, '', get_bloginfo( 'wpurl' ) );
	}

	/**
	 * Save the key
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function saveKey( $key ) {
		return Setting::update( 'license', 'key', $key );
	}

	/**
	 * Get the key
	 *
	 * @return string
	 */
	public static function getKey() {
		return Setting::get( 'license', 'key' );
	}

	/**
	 * Activate a license
	 *
	 * @param string $key
	 * @return string|\WP_Error
	 */
	public static function activate( $key ) {
		$request_uri = add_query_arg(
			array(
				'woo_sl_action'     => 'activate',
				'licence_key'       => wp_kses_post( $key ),
				'product_unique_id' => self::id(),
				'domain'            => self::domain(),
			),
			self::apiUrl()
		);

		$data = wp_remote_get( $request_uri );

		// error
		if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
			return new \WP_Error( 'connection_error', 'There was a problem establishing a connection to the licensing server. Please try again in a few minutes.' );
		}

		// check body
		$data_body = json_decode( $data['body'] );
		$data_body = $data_body[0] ?? $data_body;

		if ( isset( $data_body->status ) ) {
			if ( $data_body->status == 'success' && ( $data_body->status_code == 's100' || $data_body->status_code == 's101' ) ) {
				self::saveKey( $key );
				if ( $data_body->message ) {
					return $data_body->message;
				}
				return __( 'Activated', 'presto-player' );
			} else {
				if ( $data_body->message ) {
					$code = $data_body->status_code ?? 'error';
					return new \WP_Error( $code, $data_body->message );
				}
				return new \WP_Error( 'activation_error', 'There was a problem activating the license. Please check your license expiration.' );
			}
		}

		return new \WP_Error( 'connection_error', 'There was a problem establishing a connection to the licensing server. Please try again in a few minutes.' );
	}

	/**
	 * Deactivate license on this site
	 *
	 * @return string|\WP_Error
	 */
	public static function deactivate() {
		$request_uri = add_query_arg(
			array(
				'woo_sl_action'     => 'deactivate',
				'licence_key'       => wp_kses_post( self::getKey() ),
				'product_unique_id' => self::id(),
				'domain'            => self::domain(),
			),
			self::apiUrl()
		);

		$data = wp_remote_get( $request_uri );

		// error
		if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
			return new \WP_Error( 'connection_error', 'There was a problem establishing a connection to the licensing server. Please try again in a few minutes.' );
		}

		$data_body = json_decode( $data['body'] );
		$data_body = $data_body[0] ?? $data_body;

		if ( isset( $data_body->status ) ) {
			if ( $data_body->status == 'success' && $data_body->status_code == 's201' ) {
				self::saveKey( '' );
				Setting::update( 'license_data', 'last_check', time() );
				return $data_body->message;
			} elseif ( $data_body->status_code == 'e002' || $data_body->status_code == 'e104' || $data_body->status_code == 'e211' ) { // if message code is e104  force de-activation
					self::saveKey( '' );
					Setting::update( 'license_data', 'last_check', time() );
					return __( 'Deactivated', 'presto-player' );
			} else {
				self::saveKey( '' );
				if ( $data_body->message ) {
					$code = $data_body->status_code ?? 'error';
					return new \WP_Error( $code, $data_body->message );
				}
				return new \WP_Error( 'error', 'There was a problem deactivating the licence' );
			}
		} else {
			return new \WP_Error( 'error', 'There was a problem with the connecting to the licensing server.' );
		}
	}
}
