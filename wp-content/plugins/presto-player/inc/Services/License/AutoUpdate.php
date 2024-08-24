<?php

namespace PrestoPlayer\Services\License;

use PrestoPlayer\Models\LicensedProduct;
use PrestoPlayer\Plugin;

class AutoUpdate {

	private $slug        = 'presto-player';
	public $plugin       = 'presto-player/presto-player.php';
	private $API_VERSION = 1.1;

	public function register() {
		// check for updates
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'checkForUpdate' ) );
		// Take over the Plugin info screen and load info from prestomade.com
		add_filter( 'plugins_api', array( $this, 'pluginsApiCall' ), 10, 3 );
	}

	/**
	 * Check for plugin update
	 *
	 * @param mixed $checked_data
	 * @return mixed
	 */
	public function checkForUpdate( $checked_data ) {
		global $wp_version;

		if ( ! is_object( $checked_data ) || ! isset( $checked_data->response ) ) {
			return $checked_data;
		}

		$request_data = $this->prepareRequest( 'plugin_update_free' );
		if ( $request_data === false ) {
			return $checked_data;
		}

		// Start checking for an update
		$request_uri = add_query_arg( $request_data, LicensedProduct::apiUrl() );

		// check if cached
		$data = false; // get_site_transient('presto_player_check_for_plugin_update_' . md5($request_uri));
		if ( $data === false ) {
			$data = wp_remote_get(
				$request_uri,
				array(
					'timeout'    => 20,
					'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
				)
			);

			if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
				return $checked_data;
			}

			// cache for 4 hours
			set_site_transient( 'presto_player_check_for_plugin_update_' . md5( $request_uri ), $data, 4 * HOUR_IN_SECONDS );
		}

		// get response
		$response_block = json_decode( $data['body'] );

		$response = isset( $response_block->message ) ? $response_block->message : '';

		// Feed the update data into WP updater
		if ( is_object( $response ) && ! empty( $response ) ) {
			$response                                = $this->postprocessResponse( $response );
			$checked_data->response[ $this->plugin ] = $response;
		}

		return $checked_data;
	}

	/**
	 * Fetches plugin data from prestomade.com
	 *
	 * @param false|object|array $def
	 * @param string             $action
	 * @param object             $args
	 *
	 * @return false|object|array
	 */
	public function pluginsApiCall( $def, $action, $args ) {
		// only for our plugin
		if ( ! is_object( $args ) || ! isset( $args->slug ) || $args->slug != $this->slug ) {
			return $def;
		}

		$action = $action === 'plugin_information' ? 'plugin_information_free' : $action;

		// prepare request
		$request_data = $this->prepareRequest( $action, $args );
		if ( $request_data === false ) {
			return new \WP_Error( 'plugins_api_failed', __( 'An error occour when try to identify the pluguin.', 'presto-player' ) . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' . __( 'Try again', 'presto-player' ) . '&lt;/a>' );
		}

		global $wp_version;

		// make call to server
		$request_uri = add_query_arg( $request_data, LicensedProduct::apiUrl() );
		$data        = wp_remote_get(
			$request_uri,
			array(
				'timeout'    => 20,
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
			)
		);

		if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
			return new \WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.', 'presto-player' ) . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' . __( 'Try again', 'presto-player' ) . '&lt;/a>', $data->get_error_message() );
		}

		// retrieve the last message within the $response_block
		$response_block = json_decode( $data['body'] );
		$response       = $response_block->message;

		if ( is_object( $response ) && ! empty( $response ) ) {
			$response = $this->postProcessResponse( $response );
			return $response;
		}

		return $def;
	}

	/**
	 * Prepare request arguments
	 *
	 * @param string $action Type of action
	 * @param array  $args Query arguments for request
	 * @return array
	 */
	public function prepareRequest( $action, $args = array() ) {
		global $wp_version;

		return array(
			'woo_sl_action' => $action,
			'license_key'   => LicensedProduct::getKey(),
			'version'       => Plugin::version(),
			'wp-version'    => $wp_version,
			'api_version'   => $this->API_VERSION,
		);
	}

	/**
	 * Postprocess update resonse
	 *
	 * @param object $response
	 * @return void
	 */
	public function postProcessResponse( $response ) {
		// include slug and plugin data
		$response->slug   = $this->slug;
		$response->plugin = $this->plugin;

		// if sections are being set, force array
		if ( isset( $response->sections ) ) {
			$response->sections = (array) $response->sections;
		}
		// if banners are being set, force array
		if ( isset( $response->banners ) ) {
			$response->banners = (array) $response->banners;
		}
		// if icons being set, force array
		if ( isset( $response->icons ) ) {
			$response->icons = (array) $response->icons;
		}

		return $response;
	}
}
