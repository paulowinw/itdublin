<?php
/**
 * GetCoordinatesByAddress.
 * php version 5.6
 *
 * @category GetCoordinatesByAddress
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use SureTriggers\Integrations\Voxel\Voxel;

/**
 * GetCoordinatesByAddress
 *
 * @category GetCoordinatesByAddress
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class GetCoordinatesByAddress extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Voxel';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'voxel_get_cooordinates_by_address';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Get Coordinates By Address', 'suretriggers' ),
			'action'   => 'voxel_get_cooordinates_by_address',
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * 
	 * @throws Exception Exception.
	 * 
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$address = $selected_options['address'];
		if ( ! function_exists( 'Voxel\get' ) ) {
			return false;
		}
		if ( is_array( $address ) ) {
			$latitude  = isset( $address['latitude'] ) ? $address['latitude'] : null;
			$longitude = isset( $address['longitude'] ) ? $address['longitude'] : null;

			if ( ! is_numeric( $latitude ) || ! is_numeric( $longitude ) ) {
				return [ 'response' => 'Invalid coordinates provided.' ];
			}
		} elseif ( ! is_string( $address ) || empty( trim( $address ) ) ) {
			return [ 'response' => 'Invalid address provided.' ];
		}

		if ( \Voxel\get( 'settings.maps.provider' ) === 'mapbox' ) {
			$url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/%s.json?%s';

			$params = [
				'access_token' => \Voxel\get( 'settings.maps.mapbox.api_key' ),
				'language'     => 'en',
				'limit'        => 1,
			];

			if ( is_array( $address ) ) {
				$location = join( ',', array_reverse( array_map( 'floatval', $address ) ) );
			} else {
				$location = rawurlencode( $address );
			}

			$request = wp_remote_get(
				sprintf( $url, $location, http_build_query( $params ) ),
				[
					'httpversion' => '1.1',
					'sslverify'   => false,
				]
			);

			if ( is_wp_error( $request ) ) {
				throw new Exception( 'Could not perform geocoding request.' );
			}

			$response = json_decode( wp_remote_retrieve_body( $request ), false );
			if ( is_object( $response ) && property_exists( $response, 'features' ) ) {
				if ( empty( $response->features ) && property_exists( $response, 'message' ) ) {
					return [ 'response' => isset( $response->message ) ? $response->message : 'Geocoding request failed.' ];
				}
				$result = $response->features[0];

				return [
					'latitude'  => $result->geometry->coordinates[1],
					'longitude' => $result->geometry->coordinates[0],
					'address'   => $result->place_name,
				];
			} else {
				return [ 'response' => 'Geocoding request failed.' ];
			}
		} else {
			$params = [
				'key'      => \Voxel\get( 'settings.maps.google_maps.api_key' ),
				'language' => 'en',
			];
	
			if ( is_array( $address ) ) {
				$params['latlng'] = join( ',', array_map( 'floatval', $address ) );
			} else {
				$params['address'] = (string) $address;
			}
	
			$request = wp_remote_get(
				sprintf( 'https://maps.googleapis.com/maps/api/geocode/json?%s', http_build_query( $params ) ),
				[
					'httpversion' => '1.1',
					'sslverify'   => false,
				]
			);
	
			if ( is_wp_error( $request ) ) {
				throw new Exception( 'Could not perform geocoding request.' );
			}
	
			$response = json_decode( wp_remote_retrieve_body( $request ), false );
			if ( is_object( $response ) && property_exists( $response, 'results' ) ) {
				if ( ( property_exists( $response, 'status' ) && property_exists( $response, 'error_message' ) && 'OK' !== $response->status ) || 
				empty( $response->results ) ) {
					return [
						'status'        => isset( $response->status ) ? $response->status : 'REQUEST_FAILED',
						'error_message' => isset( $response->error_message ) ? $response->error_message : 'Geocoding request failed.',
					];
				}
				$result = $response->results[0];
				return [
					'latitude'  => $result->geometry->location->lat,
					'longitude' => $result->geometry->location->lng,
					'address'   => $result->formatted_address,
				];
			} else {
				return [ 'response' => 'Request failed.' ];
			}
		}
	}

}

GetCoordinatesByAddress::get_instance();
