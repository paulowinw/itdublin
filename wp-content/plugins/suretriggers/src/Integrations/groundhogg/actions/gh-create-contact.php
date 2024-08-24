<?php
/**
 * GhCreateContact.
 * php version 5.6
 *
 * @category GhCreateContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Groundhogg\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * GhCreateContact
 *
 * @category GhCreateContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class GhCreateContact extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Groundhogg';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'gh_create_contact';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create/Update Contact', 'suretriggers' ),
			'action'   => $this->action,
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
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$email         = sanitize_email( $selected_options['email'] );
		$api_key       = $selected_options['token'];
		$public_key    = $selected_options['public_key'];
		$first_name    = $selected_options['first_name'];
		$last_name     = $selected_options['last_name'];
		$optin_status  = $selected_options['optin_status'];
		$custom_fields = $selected_options['custom_fields'];
		
		if ( is_email( $email ) ) {
			// Make a single response array.
			$response_array = [];

			// Build http request param.
			$request_args = [
				'data' => [
					'email'        => $email,
					'first_name'   => $first_name,
					'last_name'    => $last_name,
					'optin_status' => $optin_status,
				],
			];

			if ( ! empty( $custom_fields ) ) {
				$fields = [];
				foreach ( $custom_fields as $key => $value ) {
					$fields[ $value['custom_field_key'] ] = $value['custom_field_value'];
				}
				$request_args['meta'] = $fields;
			}

			$args = [
				'headers'     => [
					'Content-Type'  => 'application/json',
					'Gh-Token'      => $api_key,
					'Gh-Public-Key' => $public_key,
				],
				'sslverify'   => false,
				'data_format' => 'body',
				'body'        => wp_json_encode( $request_args ),
			];

			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$request       = wp_remote_post( get_rest_url() . 'gh/v4/contacts/', $args );
			$response_code = wp_remote_retrieve_response_code( $request );
			$response_body = wp_remote_retrieve_body( $request );
			$response      = $response_body;

			if ( 200 !== $response_code ) {
				$response = json_decode( $response_body );
				if ( is_object( $response ) ) {
					if ( property_exists( $response, 'code' ) && property_exists( $response, 'message' ) ) {
						$error_code     = $response->code;
						$error_message  = $response->message;
						$response_array = [
							'status'  => 'error',
							'code'    => $error_code,
							'message' => $error_message,
						];
					}
				}
			} else {
				$response       = json_decode( $response, true );
				$response_array = (array) $response;
			}

			return $response_array;
		} else {
			throw new Exception( 'Enter valid email' );
		}
	}

}

GhCreateContact::get_instance();
