<?php
/**
 * SetUserStatus.
 * php version 5.6
 *
 * @category SetUserStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * SetUserStatus
 *
 * @category SetUserStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SetUserStatus extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyBoss';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bb_set_user_status';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Set User Status', 'suretriggers' ),
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
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$user_email = $selected_options['wp_user_email'];
		$status     = $selected_options['status'];

		if ( ! function_exists( 'bp_is_active' ) || ! function_exists( 'bp_moderation_is_user_suspended' ) ) {
			return;
		}

		if ( ! class_exists( 'BP_Suspend_Member' ) ) {
			return;
		}

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id = $user->ID;
				if ( bp_is_active( 'moderation' ) ) {
					if ( 'suspend' === $status ) {
						\BP_Suspend_Member::suspend_user( $user_id );
					} elseif ( bp_moderation_is_user_suspended( $user_id ) ) {
						\BP_Suspend_Member::unsuspend_user( $user_id );
					}
					$bp_current_user = new \WP_User( $user_id );
					return $bp_current_user;
				} else {
					throw new Exception(
						'To change members status in your network, 
                    please activate the Moderation component.' 
					);
				}
			} else {
				throw new Exception( 'User Not found.' );
			}
		} else {
			throw new Exception( 'Please enter valid email.' );
		}
	}
}

SetUserStatus::get_instance();
