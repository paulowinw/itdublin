<?php
/**
 * RegisterANewUser.
 * php version 5.6
 *
 * @category RegisterANewUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentForm\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * RegisterANewUser
 *
 * @category RegisterANewUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RegisterANewUser extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'FluentForm';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'register_a_new_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Register New User', 'suretriggers' ),
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
	 * @param array $selected_options sele.
	 *
	 * @return array|void
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $selected_options['user_email'] ) || ! is_email( $selected_options['user_email'] ) ) {
			throw new Exception( 'Invalid email.' );
		}

		$email = sanitize_email( $selected_options['user_email'] );

		if ( ! email_exists( $email ) ) {
			/**
			 * No user belongs to this E-Mail
			 * Creating one now
			 */

			$user_pass = ( isset( $selected_options['password'] ) ) ? $selected_options['password'] : wp_generate_password();
			$userdata  = [
				'user_login' => $selected_options['user_name'],
				'user_email' => $email,
				'first_name' => $selected_options['first_name'],
				'last_name'  => $selected_options['last_name'],
				'user_pass'  => $user_pass,
				'role'       => $selected_options['role'],
			];

			$user_id = wp_insert_user( wp_slash( $userdata ) );

			if ( is_wp_error( $user_id ) ) {
				throw new Exception( $user_id->get_error_message() );
			}

			if ( isset( $selected_options['user_meta'] ) && is_array( $selected_options['user_meta'] ) && count( $selected_options['user_meta'] ) ) {
				foreach ( $selected_options['user_meta'] as $meta ) {
					update_user_meta( $user_id, $meta['metaKey'], $meta['metaValue'] );
				}
			}
			$user    = get_userdata( $user_id );
			$context = [];
			if ( ! $user ) {
				throw new Exception( 'Invalid user.' );
			}
			$context['wp_user_id']     = $user->ID;
			$context['user_login']     = $user->user_login;
			$context['display_name']   = $user->display_name;
			$context['user_firstname'] = $user->user_firstname;
			$context['user_lastname']  = $user->user_lastname;
			$context['user_email']     = $user->user_email;
			return $context;
		}

		throw new Exception( 'User already Registered.' );

	}
}

RegisterANewUser::get_instance();
