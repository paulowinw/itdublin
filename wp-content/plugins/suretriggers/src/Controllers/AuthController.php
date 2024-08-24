<?php
/**
 * AuthController.
 * php version 5.6
 *
 * @category AuthController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use SureCart\Models\ApiToken;
use SureTriggers\Traits\SingletonLoader;

/**
 * AuthController- Connect and revoke user access_token.
 *
 * @category AuthController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 *
 * @psalm-suppress UndefinedTrait
 */
class AuthController {


	use SingletonLoader;

	/**
	 * Access token for authentication.
	 *
	 * @var string $secret_key
	 */
	private $access_token;

	/**
	 * Connection id for authentication.
	 *
	 * @var string $secret_key
	 */
	private $connection_id;

	/**
	 * Secret Key for authentication.
	 *
	 * @var string $secret_key
	 */
	private $secret_key;

	/**
	 * List of conected integrations/plugins.
	 *
	 * @var array $connected_integrations
	 */
	private $connected_integrations;

	/**
	 * Initialise data.
	 */
	public function __construct() {
		$this->access_token           = OptionController::get_option( 'access_token' );
		$this->connection_id          = OptionController::get_option( 'connection_id' );
		$this->connected_integrations = OptionController::get_option( 'connected_integrations', [] );
		$this->secret_key             = OptionController::get_option( 'secret_key' );
		add_action( 'admin_init', [ $this, 'save_connection' ] );
		add_action( 'updated_option', [ $this, 'updated_sc_api_key' ], 10, 3 );
	}

	/**
	 * Remove the respective integration triggers after deleting the connection
	 *
	 * @param string $integration Integration Name.
	 */
	public static function remove_integration_triggers( $integration ) {
		$saved_triggers = OptionController::get_option( 'triggers', [] );

		foreach ( $saved_triggers as $index => $trigger ) {
			if ( ! empty( $trigger['integration'] ) && $integration === $trigger['integration'] ) {
				unset( $saved_triggers[ $index ] );
			}
		}

		$saved_triggers = OptionController::set_option( 'triggers', $saved_triggers );

		// Remove the respective integration triggers field data after deleting the connection.
		$saved_triggers_data = OptionController::get_option( 'trigger_data', [] );
		foreach ( $saved_triggers_data as $index => $trigger ) {
			if ( is_array( $saved_triggers_data ) && is_array( $trigger ) && ! empty( $trigger ) && $integration === $index ) {
				unset( $saved_triggers_data[ $index ] );
			}
		}
		$saved_triggers_data = OptionController::set_option( 'trigger_data', $saved_triggers_data );
	}

	/**
	 * Add or revoke access token from Sass.
	 *
	 * @param object $request Request.
	 */
	public function revoke_connection( $request ) {
		$secret_key       = $request->get_header( 'st_authorization' );
		list($secret_key) = sscanf( $secret_key, 'Bearer %s' );

		if ( $this->secret_key !== $secret_key ) {
			return RestController::error_message( 'Invalid secret key.' );
		}

		// delete the suretrigger_options from wp_options table once the connection is deleted on SAAS.
		OptionController::set_option( 'secret_key', null );

		return RestController::success_message();

	}

	/**
	 * Save sure triggers connection.
	 *
	 * @return void
	 */
	public function save_connection() {
		if ( ! isset( $_GET['sure-trigger-connect-nonce'] ) ) {
			return;
		}

		if ( ! isset( $_GET['connection-status'] ) ) {
			return;
		}

		$nonce             = sanitize_text_field( wp_unslash( $_GET['sure-trigger-connect-nonce'] ) );
		$connection_status = (bool) sanitize_text_field( wp_unslash( $_GET['connection-status'] ) );

		if ( false === wp_verify_nonce( $nonce, 'sure-trigger-connect' ) ) {
			return;
		}

		if ( false === current_user_can( 'administrator' ) ) {
			return;
		}

		$access_key = isset( $_GET['sure-triggers-access-key'] ) ? sanitize_text_field( wp_unslash( $_GET['sure-triggers-access-key'] ) ) : false;

		if ( false === $connection_status ) {
			$access_key = 'connection-denied';
		}

		$connected_email_id = isset( $_GET['connected_email'] ) ? sanitize_email( wp_unslash( $_GET['connected_email'] ) ) : '';

		OptionController::set_option( 'secret_key', $access_key );
		OptionController::set_option( 'connected_email_key', $connected_email_id );

		/**
		 * If there any SureCart
		 */
		$this->post_authorize_create_sc_connection();
	}

	/**
	 * Create SureCart connection at saas end.
	 *
	 * @return void
	 */
	public function post_authorize_create_sc_connection() {
		if ( ! is_plugin_active( 'surecart/surecart.php' ) || ! class_exists( ApiToken::class ) ) {
			return;
		}

		$this->create_sc_connection();
	}

	/**
	 * Send a request to the SAAS to create SureCart connection for authorized user
	 *
	 * @return string
	 */
	public function create_sc_connection() {
		$sc_api_key = ApiToken::get();

		if ( empty( $sc_api_key ) ) {
			return;
		}

		$secret_key      = OptionController::get_option( 'secret_key' );
		$connected_email = OptionController::get_option( 'connected_email_key' );

		wp_remote_post(
			trailingslashit( API_SERVER_URL ) . 'connection/create-sc',
			[
				'sslverify' => false,
				'headers'   => [
					'Authorization' => 'Bearer ' . $secret_key,
					'scapikey'      => $sc_api_key,
				],
				'body'      => [
					'email' => $connected_email,
					'title' => 'SureCart | ' . get_bloginfo( 'name' ),
				],
			]
		);
	}

	/**
	 * Update Sure Cart connection whenever update the API key
	 *
	 * @param string $option Option.
	 * @param mixed  $old_value Old value.
	 * @param mixed  $value Value.
	 * @return void
	 */
	public function updated_sc_api_key( $option, $old_value, $value ) {
		if ( 'sc_api_token' !== $option ) {
			return;
		}

		if ( $value ) {
			$this->create_sc_connection();
		}
	}

}

AuthController::get_instance();
