<?php
/**
 * SetUserReputation.
 * php version 5.6
 *
 * @category SetUserReputation
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\wpForo\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * SetUserReputation
 *
 * @category SetUserReputation
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SetUserReputation extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'wpForo';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wp_foro_set_user_reputation';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Set User Reputation', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields template fields.
	 * @param array $selected_options saved template data.
	 * @throws Exception Exception.
	 *
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
	
		$reputation_id = $selected_options['reputation_id'];

		$user_email = $selected_options['wp_user_email'];

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );

			if ( $user ) {
				$user_id = $user->ID;
				if ( function_exists( 'WPF' ) ) {
					$points = WPF()->member->rating( $reputation_id, 'points' );

					$args = [ 'custom_points' => $points ];
				
					WPF()->member->update_profile_fields( $user_id, $args, false );
					WPF()->member->reset( $user_id );
					$user = WPF()->member->get_member( $user_id );
					return $user;
				} else {
					throw new Exception( 'Something went wrong.' );
				}
			} else {
				throw new Exception( 'User not found.' );
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}
	}

}

SetUserReputation::get_instance();
