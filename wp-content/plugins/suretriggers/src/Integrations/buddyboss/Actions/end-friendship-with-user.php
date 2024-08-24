<?php
/**
 * EndFriendshipWithUser.
 * php version 5.6
 *
 * @category EndFriendshipWithUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * EndFriendshipWithUser
 *
 * @category EndFriendshipWithUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class EndFriendshipWithUser extends AutomateAction {

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
	public $action = 'bb_end_friendship_with_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'End friendship with user', 'suretriggers' ),
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
	 * @throws Exception Exception.
	 *
	 * @return bool|array 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$initiator_friend = $selected_options['wp_initiator_user_email'];
		$remove_friend    = $selected_options['wp_user_email'];
		if ( is_email( $remove_friend ) && is_email( $initiator_friend ) ) {
			$initiator_friend_user = get_user_by( 'email', $initiator_friend );
			$user                  = get_user_by( 'email', $remove_friend );
			if ( $initiator_friend_user ) {
				if ( $user ) {
					$user_id = $user->ID;
					if ( function_exists( 'friends_remove_friend' ) ) {
						friends_remove_friend( $initiator_friend_user->ID, $user_id );
					}
					$context['initiator'] = WordPress::get_user_context( $initiator_friend_user->ID );
					$context['user']      = WordPress::get_user_context( $user_id );
					return $context;
				} else {
					// If there's no user found, return default message.
					throw new Exception( 'User with the email provided not found.' );
				}
			} else {
				// If there's no user found, return default message.
				throw new Exception( 'Inititator User with the email provided not found.' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

EndFriendshipWithUser::get_instance();
