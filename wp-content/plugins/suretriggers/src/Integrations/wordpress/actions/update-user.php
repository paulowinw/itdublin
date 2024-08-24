<?php
/**
 * UpdateUser.
 * php version 5.6
 *
 * @category UpdateUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WordPress\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * UpdateUser
 *
 * @category UpdateUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UpdateUser extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'update_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'User: Update User Details', 'suretriggers' ),
			'action'   => 'update_user',
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
		global $wpdb;

		$meta_array = [];

		if ( empty( $selected_options['user_details'] ) || ! $user_id ) {
			return [];
		}

		$meta_array['ID'] = $user_id;

		foreach ( $selected_options['user_details'] as $meta ) {
			$meta_key                = $meta['user_key'];
			$meta_value              = $meta['user_value'];
			$meta_array[ $meta_key ] = $meta_value;

			// User login and email.
			if ( 'user_login' === $meta_key ) {
				if ( ! empty( $meta_value ) ) {
					if ( ! validate_username( $meta_value ) ) {
						wp_send_json_error( __( 'Invalid username: %1$s.', 'suretriggers' ), $meta_value );
					} else {
						$user_id_has_this_id = username_exists( $meta_value );

						if ( $user_id_has_this_id && $user_id_has_this_id !== $user_id ) {
							wp_send_json_error( __( 'Username "%1$s" already exists.', 'suretriggers' ), $meta_value );
						} else {
							$wpdb->update( $wpdb->users, [ 'user_login' => $meta_value ], [ 'ID' => $user_id ] ); //phpcs:ignore
						}
					}
				}
			} elseif ( 'user_email' === $meta_key ) {
				if ( ! empty( $meta_value ) ) {
					if ( ! is_email( $meta_value ) ) {
						wp_send_json_error( __( 'Invalid email address: %1$s.', 'suretriggers' ), $meta_value );
					} else {

						$user_id_has_email = email_exists( $meta_value );

						if ( $user_id_has_email && $user_id_has_email !== $user_id ) {
							wp_send_json_error( __( 'Email address "%1$s" already exists.', 'suretriggers' ), $meta_value );
						} else {
							$wpdb->update( $wpdb->users, [ 'user_email' => $meta_value ], [ 'ID' => $user_id ] ); //phpcs:ignore
						}
					}
				}
			}
		}

		wp_update_user( $meta_array );

		unset( $meta_array['ID'] );
		$meta_array['updated_user_ID'] = $user_id;

		return $meta_array;
	}
}

UpdateUser::get_instance();
